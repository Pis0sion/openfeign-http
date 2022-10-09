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

namespace Pis0sion\Openfeign\Exception\Handler;

use App\Library\Enum\ServiceCodeEnum;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * \Pis0sion\Openfeign\Exception\Handler\DefaultExceptionHandler.
 */
class DefaultExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger, protected FormatterInterface $formatter)
    {
    }

    /**
     * handle.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        //$this->logger->warning($this->formatter->format($throwable));
        return $response->withHeader('content-type', 'application/json')
            ->withStatus($throwable->getCode() ?? 400)->withBody(new SwooleStream(json_encode([
                'code' => 0, 'data' => [], 'msg' => $throwable->getMessage(),
                'serviceCode' => ServiceCodeEnum::SERVICECODE_ERROR_DEFAULT,
            ])));
    }

    /**
     * isValid.
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
