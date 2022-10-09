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

namespace Pis0sion\Openfeign\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * \Pis0sion\Openfeign\Annotation\OpenFeignRouter.
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class OpenFeignRouter extends AbstractAnnotation
{
    /**
     * @param string $path
     * @param string $method
     */
    public function __construct(public string $path = "/", public string $method = 'GET')
    {
    }
}
