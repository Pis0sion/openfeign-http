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

/**
 * \Pis0sion\Openfeign\Constant.
 */
class Constant
{
    /**
     * PROTOCOL_DEFAULT.
     */
    public const PROTOCOL_DEFAULT = 'openfeign-http';

    /**
     * NOT_FOUND_HANDLER.
     */
    public const NOT_FOUND_HANDLER = 'Rpc服务没有找到,请及时联系管理员...';

    /**
     * SERVICE_CODE_SUCCESS.
     */
    public const SERVICE_CODE_SUCCESS = 120000000;

    /**
     * SERVICE_CODE_ERROR.
     */
    public const SERVICE_CODE_ERROR = 120000001;

    /**
     * DEFAULT_METHOD.
     */
    public const DEFAULT_METHOD = 'POST';

    /**
     * HTTP_GET_METHOD.
     */
    public const HTTP_GET_METHOD = 'GET';
}
