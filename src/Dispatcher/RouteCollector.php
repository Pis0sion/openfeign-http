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

use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use Hyperf\HttpServer\Router\Handler;

/**
 * \Pis0sion\Openfeign\Dispatcher\RouteCollector.
 */
class RouteCollector
{
    /**
     * @var RouteParser
     */
    protected $routeParser;

    /**
     * @var DataGenerator
     */
    protected $dataGenerator;

    /**
     * @var string
     */
    protected $currentGroupPrefix;

    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator)
    {
        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
        $this->currentGroupPrefix = '';
    }

    /**
     * addRoute.
     * @param $route
     * @param $handler
     */
    public function addRoute(string $httpMethod, $route, $handler)
    {
        $route = $this->currentGroupPrefix . $route;
        $routeDatas = $this->routeParser->parse($route);
        foreach ($routeDatas as $routeData) {
            $this->dataGenerator->addRoute($httpMethod, $routeData, new Handler($handler, $route));
        }
    }

    /**
     * addGroup.
     * @param $prefix
     */
    public function addGroup($prefix, callable $callback, array $options = [])
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;
    }

    /**
     * getData.
     * @return mixed
     */
    public function getData()
    {
        return $this->dataGenerator->getData();
    }
}
