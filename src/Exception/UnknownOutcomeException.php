<?php
declare(strict_types=1);
namespace Aymudo\Exception;
final class UnknownOutcomeException extends AymudoException
{
    public function __construct(string $message,public readonly string $idempotencyKey,?\Throwable $previous=null)
    { parent::__construct($message,'unknown_write_outcome',0,null,['idempotencyKey'=>$idempotencyKey],$previous); }
}
