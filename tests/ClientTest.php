<?php
declare(strict_types=1);
namespace Aymudo\Tests;
use Aymudo\Client;
use Aymudo\Exception\AymudoException;
use PHPUnit\Framework\TestCase;
final class ClientTest extends TestCase
{
    public function testCapabilitiesUsesPlatformHeaders():void{$transport=new FakeTransport([['status'=>200,'headers'=>[],'body'=>'{"data":{"operations":["workspace.read"]}}']]);$client=new Client('secret','str_demo','https://api.example.test/v1',$transport);self::assertSame(['operations'=>['workspace.read']],$client->capabilities());self::assertSame('GET',$transport->requests[0]['method']);self::assertContains('Authorization: Bearer secret',$transport->requests[0]['headers']);self::assertContains('X-Aymudo-Store: str_demo',$transport->requests[0]['headers']);}
    public function testWritesAlwaysContainIdempotencyKey():void{$transport=new FakeTransport([['status'=>201,'headers'=>[],'body'=>'{"data":{"appointmentID":1}}']]);$client=new Client('secret','str_demo','https://api.example.test/v1',$transport);self::assertSame(['appointmentID'=>1],$client->createAppointment(['branchID'=>1]));self::assertNotEmpty(array_filter($transport->requests[0]['headers'],static fn(string $h):bool=>str_starts_with($h,'Idempotency-Key: php_')));}
    public function testApiErrorsAreTyped():void{$transport=new FakeTransport([['status'=>403,'headers'=>[],'body'=>'{"error":{"code":"scope_required","message":"Missing scope","requestID":"req_1"}}']]);$client=new Client('secret','str_demo','https://api.example.test/v1',$transport);$this->expectException(AymudoException::class);$client->customers();}
}
