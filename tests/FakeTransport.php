<?php
declare(strict_types=1);
namespace Aymudo\Tests;
use Aymudo\Http\Transport;
final class FakeTransport implements Transport
{
    public array $requests=[];
    public function __construct(private array $responses){}
    public function send(string $method,string $url,array $headers,?string $body,int $timeoutSeconds):array{$this->requests[]=compact('method','url','headers','body','timeoutSeconds');return array_shift($this->responses)??['status'=>200,'headers'=>[],'body'=>'{}'];}
}
