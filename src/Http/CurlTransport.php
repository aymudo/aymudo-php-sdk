<?php
declare(strict_types=1);
namespace Aymudo\Http;
use RuntimeException;
final class CurlTransport implements Transport
{
    public function send(string $method,string $url,array $headers,?string $body,int $timeoutSeconds):array
    {
        $responseHeaders=[];$handle=curl_init($url);if($handle===false)throw new RuntimeException('Unable to initialize cURL.');
        curl_setopt_array($handle,[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>min(10,$timeoutSeconds),CURLOPT_TIMEOUT=>$timeoutSeconds,CURLOPT_HTTPHEADER=>$headers,CURLOPT_HEADERFUNCTION=>static function($curl,string $line)use(&$responseHeaders):int{$length=strlen($line);if(str_contains($line,':')){[$name,$value]=explode(':',$line,2);$responseHeaders[strtolower(trim($name))]=trim($value);}return $length;}]);
        if($body!==null)curl_setopt($handle,CURLOPT_POSTFIELDS,$body);$response=curl_exec($handle);
        if($response===false){$message=curl_error($handle)?:'Aymudo request failed.';curl_close($handle);throw new RuntimeException($message);}
        $status=(int)curl_getinfo($handle,CURLINFO_HTTP_CODE);curl_close($handle);return['status'=>$status,'headers'=>$responseHeaders,'body'=>(string)$response];
    }
}
