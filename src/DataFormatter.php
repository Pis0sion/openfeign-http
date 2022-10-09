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

use Hyperf\Rpc\Contract\DataFormatterInterface;

/**
 * \Pis0sion\Openfeign\DataFormatter.
 */
class DataFormatter implements DataFormatterInterface
{
    /**
     * formatRequest.
     * @param array $data
     * @return array
     */
    public function formatRequest($data)
    {
        [$httpMethod, $routePath, $parameters, $id] = $data;
        return compact('httpMethod', 'routePath', 'parameters', 'id');
    }

    /**
     * formatResponse.
     * @param array $data
     * @param mixed $responseData
     * @return array
     */
    public function formatResponse($responseData)
    {
        [$data] = $responseData;

        return [
            'code' => 1,
            'msg' => '业务操作成功',
            'data' => $data,
            'serviceCode' => Constant::SERVICE_CODE_SUCCESS,
        ];
    }

    /**
     * formatErrorResponse.
     * @param array $responseData
     * @return array
     */
    public function formatErrorResponse($responseData)
    {
        [$code, $message, $data] = $responseData;

        if (isset($data) && $data instanceof \Throwable) {
            $data = [
                'class' => get_class($data),
                'code' => $data->getCode(),
                'message' => $data->getMessage(),
            ];
        }
        return [
            'code' => 0,
            'msg' => $message,
            'data' => $data,
            'serviceCode' => Constant::SERVICE_CODE_ERROR,
        ];
    }
}
