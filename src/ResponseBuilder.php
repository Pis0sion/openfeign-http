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

use Hyperf\Contract\PackerInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * \Pis0sion\Openfeign\ResponseBuilder.
 */
class ResponseBuilder
{
    /**
     * SERVER_ERROR.
     */
    public const SERVER_ERROR = -32000;

    /**
     * INVALID_REQUEST.
     */
    public const INVALID_REQUEST = -32600;

    /**
     * METHOD_NOT_FOUND.
     */
    public const METHOD_NOT_FOUND = -32601;

    /**
     * INVALID_PARAMS.
     */
    public const INVALID_PARAMS = -32602;

    /**
     * INTERNAL_ERROR.
     */
    public const INTERNAL_ERROR = -32603;

    /**
     * PARSE_ERROR.
     */
    public const PARSE_ERROR = -32700;

    /**
     * @var \Hyperf\Rpc\Contract\DataFormatterInterface
     */
    protected $dataFormatter;

    /**
     * @var PackerInterface
     */
    protected $packer;

    /**
     * @param \Hyperf\Rpc\Contract\DataFormatterInterface $dataFormatter
     * @param \Hyperf\Contract\PackerInterface $packer
     */
    public function __construct(DataFormatterInterface $dataFormatter, PackerInterface $packer)
    {
        $this->dataFormatter = $dataFormatter;
        $this->packer = $packer;
    }

    /**
     * buildErrorResponse.
     */
    public function buildErrorResponse(ServerRequestInterface $request, int $code, \Throwable $error = null): ResponseInterface
    {
        $body = new SwooleStream($this->formatErrorResponse($request, $code, $error));
        return $this->response()->withHeader('content-type', 'application/json')->withBody($body);
    }

    /**
     * buildResponse.
     * @param $response
     */
    public function buildResponse($response): ResponseInterface
    {
        $body = new SwooleStream($this->formatResponse($response));
        return $this->response()->withHeader('content-type', 'application/json')->withBody($body);
    }

    /**
     * persistToContext.
     */
    public function persistToContext(ResponseInterface $response): ResponseInterface
    {
        return Context::set(ResponseInterface::class, $response);
    }

    /**
     * formatResponse.
     * @param $response
     */
    protected function formatResponse($response): string
    {
        $response = $this->dataFormatter->formatResponse([$response]);
        return $this->packer->pack($response);
    }

    /**
     * formatErrorResponse.
     */
    protected function formatErrorResponse(ServerRequestInterface $request, int $code, \Throwable $error = null): string
    {
        [$code, $message] = $this->error($code, $error ? $error->getMessage() : null);
        $response = $this->dataFormatter->formatErrorResponse([$code, $message, $error]);
        return $this->packer->pack($response);
    }

    /**
     * error.
     */
    protected function error(int $code, ?string $message = null): array
    {
        $mapping = [
            self::PARSE_ERROR => 'Parse error.',
            self::INVALID_REQUEST => 'Invalid request.',
            self::METHOD_NOT_FOUND => 'Method not found.',
            self::INVALID_PARAMS => 'Invalid params.',
            self::INTERNAL_ERROR => 'Internal error.',
        ];
        if (isset($mapping[$code])) {
            return [$code, $mapping[$code]];
        }
        return [$code, $message ?? ''];
    }

    /**
     * Get response instance from context.
     */
    protected function response(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }
}
