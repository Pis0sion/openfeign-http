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

use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpServer\ResponseEmitter;
use Hyperf\HttpServer\Server;
use Hyperf\Rpc\Protocol;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcServer\RequestDispatcher;
use Pis0sion\Openfeign\Exception\Handler\DefaultExceptionHandler;
use Psr\Container\ContainerInterface;

/**
 * \Pis0sion\Openfeign\OpenFeignServer.
 */
class OpenFeignServer extends Server
{
    /**
     * @var Protocol
     */
    protected $protocol;

    /**
     * @var \Hyperf\Contract\PackerInterface
     */
    protected $packer;

    /**
     * @var mixed|\Pis0sion\Openfeign\ResponseBuilder
     */
    protected $responseBuilder;

    /**
     * @param \Psr\Container\ContainerInterface $container
     * @param \Hyperf\RpcServer\RequestDispatcher $dispatcher
     * @param \Hyperf\ExceptionHandler\ExceptionHandlerDispatcher $exceptionHandlerDispatcher
     * @param \Hyperf\HttpServer\ResponseEmitter $responseEmitter
     * @param \Hyperf\Rpc\ProtocolManager $protocolManager
     */
    public function __construct(
        ContainerInterface         $container,
        RequestDispatcher          $dispatcher,
        ExceptionHandlerDispatcher $exceptionHandlerDispatcher,
        ResponseEmitter            $responseEmitter,
        ProtocolManager            $protocolManager
    )
    {
        parent::__construct($container, $dispatcher, $exceptionHandlerDispatcher, $responseEmitter);
        $this->protocol = new Protocol($container, $protocolManager, Constant::PROTOCOL_DEFAULT);
        $this->packer = $this->protocol->getPacker();
        $this->responseBuilder = make(ResponseBuilder::class, [
            'dataFormatter' => $this->protocol->getDataFormatter(),
            'packer' => $this->packer,
        ]);
    }

    /**
     * getDefaultExceptionHandler.
     * @return string[]
     */
    protected function getDefaultExceptionHandler(): array
    {
        return [DefaultExceptionHandler::class];
    }

    /**
     * createCoreMiddleware.
     * @return \Pis0sion\Openfeign\CoreMiddleware
     */
    protected function createCoreMiddleware(): CoreMiddleware
    {
        return new CoreMiddleware(
            $this->container,
            $this->protocol,
            $this->responseBuilder,
            $this->serverName
        );
    }
}
