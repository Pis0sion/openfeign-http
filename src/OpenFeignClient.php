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

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\RpcClient\AbstractServiceClient;
use Pis0sion\Openfeign\Annotation\OpenFeignRouter;
use Pis0sion\Openfeign\Exception\InvalidArgumentException;
use Pis0sion\Openfeign\Exception\InvalidResponseException;

/**
 * \Pis0sion\Openfeign\OpenFeignClient.
 */
class OpenFeignClient extends AbstractServiceClient
{
    /**
     * @var string
     */
    protected $protocol = Constant::PROTOCOL_DEFAULT;

    /**
     * __request.
     * @return mixed
     */
    protected function __request(string $method, array $params, ?string $id = null)
    {
        $response = $this->client->send($this->__generateData($method, $params, $id));

        if (is_array($response)) {
            if (array_key_exists('code', $response) && $response['code'] == 0) {
                throw new InvalidResponseException($response['msg'] ?? '');
            }
            if (array_key_exists('data', $response)) {
                return $response['data'];
            }
        }

        throw new InvalidResponseException('Invalid response.');
    }

    /**
     * __generateData.
     * @return array
     */
    protected function __generateData(string $methodName, array $params, ?string $id)
    {
        if (!$this->serviceName) {
            throw new InvalidArgumentException('Parameter $serviceName missing.');
        }

        [$httpMethod, $methodRouteName, $routePath] = $this->__generateOpenFeignPath($methodName);

        if ($methodName === $methodRouteName) {
            $routePath = $this->__generateRpcPath($methodRouteName);
        }

        return $this->dataFormatter->formatRequest([$httpMethod, $routePath, $params, $id]);
    }

    /**
     * __generateOpenFeignPath.
     */
    protected function __generateOpenFeignPath(string $methodName): array
    {
        // default method route name
        $methodRouteName = $routePath = $methodName;
        // default method type
        $httpMethod = Constant::DEFAULT_METHOD;
        // get annotation class method
        $methodAnnotations = AnnotationCollector::getClassMethodAnnotation(get_class($this), $methodName)[OpenFeignRouter::class] ?? null;
        // get annotation object
        if ($methodAnnotations instanceof OpenFeignRouter) {
            if ($methodAnnotations?->method) {
                $httpMethod = $methodAnnotations->method;
            }
            if ($methodAnnotations?->path) {
                $methodRouteName = $routePath = '/' . trim($methodAnnotations->path, '/');
            }
        }

        return [$httpMethod, $methodRouteName, $routePath];
    }

    /**
     * __generateRpcPath.
     */
    protected function __generateRpcPath(string $methodName): string
    {
        return $this->pathGenerator->generate($this->serviceName, $methodName);
    }
}
