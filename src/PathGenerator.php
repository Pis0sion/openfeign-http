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

use Hyperf\Rpc\Contract\PathGeneratorInterface;
use Hyperf\Utils\Str;

/**
 * \Pis0sion\Openfeign\PathGenerator.
 */
class PathGenerator implements PathGeneratorInterface
{
    /**
     * generate
     * @param string $service
     * @param string $method
     * @return string
     */
    public function generate(string $service, string $method): string
    {
        $handledNamespace = explode('\\', $service);
        $handledNamespace = Str::replaceArray('\\', ['/'], end($handledNamespace));
        $handledNamespace = Str::replaceLast('Service', '', $handledNamespace);
        $path = Str::snake($handledNamespace);

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return $path . '/' . $method;
    }
}
