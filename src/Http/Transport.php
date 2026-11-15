<?php
declare(strict_types=1);
namespace Aymudo\Http;
interface Transport
{
    /** @return array{status:int,headers:array<string,string>,body:string} */
    public function send(string $method,string $url,array $headers,?string $body,int $timeoutSeconds):array;
}
