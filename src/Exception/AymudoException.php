<?php
declare(strict_types=1);
namespace Aymudo\Exception;
use RuntimeException;
class AymudoException extends RuntimeException
{
    public function __construct(string $message, public readonly string $errorCode='aymudo_error', public readonly int $statusCode=0, public readonly ?string $requestId=null, public readonly array $details=[], ?\Throwable $previous=null)
    { parent::__construct($message,$statusCode,$previous); }
}
