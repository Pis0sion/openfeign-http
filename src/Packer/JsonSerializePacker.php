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

namespace Pis0sion\Openfeign\Packer;

use Hyperf\Rpc\Contract\PackerInterface;
use Hyperf\Utils\Codec\Json;

/**
 * \Pis0sion\Openfeign\Packer\JsonSerializePacker.
 */
class JsonSerializePacker implements PackerInterface
{
    /**
     * pack.
     * @param $data
     */
    public function pack($data): string
    {
        return Json::encode($data);
    }

    /**
     * unpack.
     * @return mixed
     */
    public function unpack(string $data)
    {
        return Json::decode($data);
    }
}
