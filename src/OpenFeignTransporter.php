<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Pis0sion\Openfeign;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\LoadBalancer\LoadBalancerInterface;
use Hyperf\LoadBalancer\Node;
use Hyperf\Rpc\Contract\TransporterInterface;
use Pis0sion\Openfeign\Exception\NotFoundHttpException;
use Pis0sion\Openfeign\Packer\JsonSerializePacker;

/**
 * \Pis0sion\Openfeign\OpenFeignTransporter.
 */
class OpenFeignTransporter implements TransporterInterface
{

    /**
     * @var array
     */
    protected array $nodes = [];

    /**
     * @var null|LoadBalancerInterface
     */
    private $loadBalancer;

    /**
     * @var float
     */
    private $connectTimeout = 5;

    /**
     * @var float
     */
    private $recvTimeout = 5;

    /**
     * @var array
     */
    private $clientOptions;

    /**
     * @var \Pis0sion\Openfeign\Packer\JsonSerializePacker
     */
    private $packer;

    /**
     * @param \Hyperf\Guzzle\ClientFactory $clientFactory
     * @param \Pis0sion\Openfeign\Packer\JsonSerializePacker $packer
     * @param array $config
     */
    public function __construct(ClientFactory $clientFactory, JsonSerializePacker $packer, array $config = [])
    {
        $this->clientFactory = $clientFactory;
        $this->packer = $packer;
        if (!isset($config['recv_timeout'])) {
            $config['recv_timeout'] = $this->recvTimeout;
        }
        if (!isset($config['connect_timeout'])) {
            $config['connect_timeout'] = $this->connectTimeout;
        }
        $this->clientOptions = $config;
    }

    /**
     * send.
     * @param string $data
     * @return string
     */
    public function send(string $reqData)
    {
        $node = $this->getNode();
        $uri = $node->host . ':' . $node->port . $node->pathPrefix;
        $reqDatum = $this->unpackDatum($reqData);
        $url = $this->assertRouteUrl($this->getSchemaUri($node) . $uri, $reqDatum['routePath']);

        $response = $this->getClient()->{$reqDatum['httpMethod']}(
            $url,
            $this->assertRequestParameters($reqDatum['httpMethod'], $reqDatum['parameters'])
        );

        if ($response->getStatusCode() != 200) {
            $this->loadBalancer->removeNode($node);
        }

        return $response->getBody()->getContents();
    }

    /**
     * getClient.
     */
    public function getClient(): Client
    {
        $clientOptions = $this->clientOptions;
        // Swoole HTTP Client cannot set recv_timeout and connect_timeout options, use timeout.
        $clientOptions['timeout'] = $clientOptions['recv_timeout'] + $clientOptions['connect_timeout'];
        unset($clientOptions['recv_timeout'], $clientOptions['connect_timeout']);
        return $this->clientFactory->create($clientOptions);
    }

    /**
     * recv.
     */
    public function recv()
    {
        throw new NotFoundHttpException(__CLASS__ . ' does not support recv method.');
    }

    /**
     * getLoadBalancer.
     */
    public function getLoadBalancer(): ?LoadBalancerInterface
    {
        return $this->loadBalancer;
    }

    /**
     * setLoadBalancer.
     */
    public function setLoadBalancer(LoadBalancerInterface $loadBalancer): TransporterInterface
    {
        $this->loadBalancer = $loadBalancer;
        return $this;
    }

    /**
     * getSchemaUri.
     * @return mixed
     */
    protected function getSchemaUri(Node $node)
    {
        return value(function () use ($node) {
            $schema = 'http';
            if (property_exists($node, 'schema')) {
                $schema = $node->schema;
            }
            if (!in_array($schema, ['http', 'https'])) {
                $schema = 'http';
            }
            $schema .= '://';
            return $schema;
        });
    }

    /**
     * unpackDatum.
     * @return mixed
     */
    protected function unpackDatum(string $datum)
    {
        return $this->packer->unpack($datum);
    }

    /**
     * assertRouteUrl.
     */
    protected function assertRouteUrl(string $baseUri, string $routePath): string
    {
        $baseUri = trim($baseUri, '/');
        $routePath = trim($routePath, '/');
        return $baseUri . '/' . $routePath;
    }

    /**
     * assertRequestParameters.
     */
    protected function assertRequestParameters(string $httpMethod, array $parameters): array
    {
        $reqBaseOptions = [
            RequestOptions::CONNECT_TIMEOUT => $this->clientOptions['connect_timeout'] ?? 0,
            RequestOptions::TIMEOUT => $this->clientOptions['recv_timeout'] ?? 0,
            RequestOptions::HTTP_ERRORS => false,
        ];

        $reqOpt = [];

        if ($httpMethod == Constant::HTTP_GET_METHOD) {
            $reqOpt[RequestOptions::QUERY] = $parameters;
        } else {
            $reqOpt[RequestOptions::JSON] = $parameters;
        }

        return array_merge(array_filter($reqOpt), $reqBaseOptions);
    }

    /**
     * getNode.
     */
    private function getNode(): Node
    {
        if ($this->loadBalancer instanceof LoadBalancerInterface) {
            return $this->loadBalancer->select();
        }
        return $this->nodes[array_rand($this->nodes)];
    }
}
