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

use Closure;
use FastRoute\Dispatcher;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Rpc\Protocol;
use Hyperf\RpcServer\CoreMiddleware as RpcCoreMiddleware;
use Pis0sion\Openfeign\Dispatcher\DispatcherFactory;
use Pis0sion\Openfeign\Exception\NotFoundHttpException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * \Pis0sion\Openfeign\CoreMiddleware.
 */
class CoreMiddleware extends RpcCoreMiddleware
{
    /**
     * @var \Pis0sion\Openfeign\ResponseBuilder
     */
    protected $responseBuilder;

    /**
     * @param \Pis0sion\Openfeign\ResponseBuilder $builder
     */
    public function __construct(
        ContainerInterface $container,
        Protocol           $protocol,
        ResponseBuilder    $builder,
        string             $serverName
    )
    {
        parent::__construct($container, $protocol, $serverName);
        $this->responseBuilder = $builder;
    }

    /**
     * createDispatcher.
     */
    protected function createDispatcher(string $serverName): Dispatcher
    {
        $factory = make(DispatcherFactory::class, [
            'pathGenerator' => $this->protocol->getPathGenerator(),
        ]);
        return $factory->getDispatcher($serverName);
    }

    /**
     * handleFound.
     * @return null|array|\Hyperf\Utils\Contracts\Arrayable|mixed|\Psr\Http\Message\ResponseInterface|string
     */
    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request)
    {
        if ($dispatched->handler->callback instanceof Closure) {
            $parameters = $this->parseClosureParameters($dispatched->handler->callback, $dispatched->params);
            $response = call($dispatched->handler->callback, $parameters);
        } else {
            [$controller, $action] = $this->prepareHandler($dispatched->handler->callback);
            $controllerInstance = $this->container->get($controller);
            if (!method_exists($controller, $action)) {
                return $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::INTERNAL_ERROR);
            }

            try {
                $parameters = $this->parseMethodParameters($controller, $action, $this->getMethodParameters($request));
            } catch (\Throwable $throwable) {
                return $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::INVALID_PARAMS, $throwable);
            }

            try {
                $response = $controllerInstance->{$action}(...$parameters);
            } catch (\Throwable $exception) {
                $response = $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::SERVER_ERROR, $exception);
                $this->responseBuilder->persistToContext($response);
                throw $exception;
            }
        }

        return $response;
    }

    /**
     * getMethodParameters.
     */
    protected function getMethodParameters(ServerRequestInterface $request): array
    {
        if ($request->getMethod() === Constant::HTTP_GET_METHOD) {
            return $request->getQueryParams() ?? [];
        }

        return $request->getParsedBody() ?? [];
    }

    /**
     * handleNotFound.
     * @return array|\Hyperf\Utils\Contracts\Arrayable|mixed|\Psr\Http\Message\ResponseInterface|string|void
     */
    protected function handleNotFound(ServerRequestInterface $request)
    {
        throw new NotFoundHttpException(Constant::NOT_FOUND_HANDLER);
    }

    /**
     * handleMethodNotAllowed.
     * @return array|\Hyperf\Utils\Contracts\Arrayable|mixed|\Psr\Http\Message\ResponseInterface|string|void
     */
    protected function handleMethodNotAllowed(array $routes, ServerRequestInterface $request)
    {
        return $this->handleNotFound($request);
    }

    /**
     * transferToResponse.
     * @param null|array|\Hyperf\Utils\Contracts\Arrayable|\Hyperf\Utils\Contracts\Jsonable|string $response
     */
    protected function transferToResponse($response, ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseBuilder->buildResponse($response);
    }
}
