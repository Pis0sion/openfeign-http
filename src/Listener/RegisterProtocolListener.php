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
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Rpc\ProtocolManager;
use Pis0sion\Openfeign\Constant;
use Pis0sion\Openfeign\DataFormatter;
use Pis0sion\Openfeign\OpenFeignTransporter;
use Pis0sion\Openfeign\Packer\JsonSerializePacker;
use Pis0sion\Openfeign\PathGenerator;

/**
 * \Pis0sion\Openfeign\Listener\RegisterProtocolListener.
 */
class RegisterProtocolListener implements ListenerInterface
{
    /**
     * @param \Hyperf\Rpc\ProtocolManager $protocolManager
     */
    public function __construct(private ProtocolManager $protocolManager)
    {
    }

    /**
     * listen.
     * @return string[]
     */
    public function listen(): array
    {
        return [BootApplication::class];
    }

    /**
     * process.
     */
    public function process(object $event)
    {
        $this->protocolManager->register(Constant::PROTOCOL_DEFAULT, [
            'packer' => JsonSerializePacker::class,
            'transporter' => OpenFeignTransporter::class,
            'path-generator' => PathGenerator::class,
            'data-formatter' => DataFormatter::class,
        ]);
    }
}
