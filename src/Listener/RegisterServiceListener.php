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

namespace Pis0sion\Openfeign\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\RpcServer\Event\AfterPathRegister;
use Hyperf\ServiceGovernance\ServiceManager;
use Pis0sion\Openfeign\Constant;

/**
 * \Pis0sion\Openfeign\Listener\RegisterServiceListener.
 */
class RegisterServiceListener implements ListenerInterface
{
    /**
     * @param \Hyperf\ServiceGovernance\ServiceManager $serviceManager
     */
    public function __construct(private ServiceManager $serviceManager)
    {
    }

    /**
     * listen.
     * @return string[]
     */
    public function listen(): array
    {
        return [AfterPathRegister::class];
    }

    /**
     * process.
     */
    public function process(object $event)
    {
        if (!in_array($event->annotation->protocol, $this->getProtocols(), true)) {
            return;
        }
        $metadata = $event->toArray();
        $annotationArray = $metadata['annotation'];
        unset($metadata['path'], $metadata['annotation'], $annotationArray['name']);
        $this->serviceManager->register($event->annotation->name, $event->path, array_merge($metadata, $annotationArray));
    }

    /**
     * getProtocols.
     * @return string[]
     */
    protected function getProtocols(): array
    {
        return [Constant::PROTOCOL_DEFAULT];
    }
}
