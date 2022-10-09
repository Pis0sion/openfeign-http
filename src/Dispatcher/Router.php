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

namespace Pis0sion\Openfeign\Dispatcher;

use Pis0sion\Openfeign\Constant;

/**
 * \Pis0sion\Openfeign\Dispatcher\Router.
 */
class Router
{
    /**
     * @var string
     */
    protected static $serverName = Constant::PROTOCOL_DEFAULT;

    /**
     * @var DispatcherFactory
     */
    protected static $factory;

    /**
     * __callStatic.
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $router = static::$factory->getRouter(static::$serverName);
        return $router->{$name}(...$arguments);
    }

    /**
     * addServer.
     */
    public static function addServer(string $serverName, callable $callback)
    {
        $temp = $serverName;
        static::$serverName = $serverName;
        call($callback);
        static::$serverName = $temp;
        unset($temp);
    }

    /**
     * init.
     * @param \Pis0sion\Openfeign\Dispatcher\DispatcherFactory $factory
     */
    public static function init(DispatcherFactory $factory)
    {
        static::$factory = $factory;
    }

    /**
     * add.
     * @param $route
     * @param $handler
     */
    public static function add(string $httpMethod, $route, $handler, array $options = []): void
    {
        static::addRoute($httpMethod, $route, $handler, $options);
    }
}
