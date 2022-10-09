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

use Pis0sion\Openfeign\Listener\RegisterProtocolListener;
use Pis0sion\Openfeign\Listener\RegisterServiceListener;

/**
 * \Pis0sion\Openfeign\ConfigProvider.
 */
class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'listeners' => [
                RegisterProtocolListener::class,
                RegisterServiceListener::class,
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
