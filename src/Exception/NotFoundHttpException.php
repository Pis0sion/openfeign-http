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

namespace Pis0sion\Openfeign\Exception;

use Hyperf\HttpMessage\Exception\HttpException;

/**
 * \Pis0sion\Openfeign\Exception\NotFoundHttpException.
 */
class NotFoundHttpException extends HttpException
{
    /**
     * @param null $message
     * @param int $code
     */
    public function __construct($message = null, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}
