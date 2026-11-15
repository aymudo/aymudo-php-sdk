<?php
declare(strict_types=1);
namespace Aymudo;
use Aymudo\Exception\AymudoException;
use Aymudo\Exception\UnknownOutcomeException;
use Aymudo\Http\CurlTransport;
use Aymudo\Http\Transport;
use JsonException;
final class Client
{
    private const DEFAULT_BASE='https://api.aymudo.com/api/platform/v1';
    public function __construct(private readonly string $apiKey,private readonly string $storeId,private readonly string $baseUrl=self::DEFAULT_BASE,private readonly ?Transport $transport=null,private readonly int $timeoutSeconds=30)
    {if(trim($apiKey)==='')throw new \InvalidArgumentException('Aymudo API key is required.');if(trim($storeId)==='')throw new \InvalidArgumentException('Aymudo Store ID is required.');if(!str_starts_with($baseUrl,'https://'))throw new \InvalidArgumentException('Aymudo base URL must use HTTPS.');}
    public static function fromEnvironment():self{return new self(self::env('AYMUDO_API_KEY'),self::env('AYMUDO_STORE_ID'),getenv('AYMUDO_API_BASE_URL')?:self::DEFAULT_BASE);}
    public function capabilities():array{return $this->get('/capabilities');}
    public function operations():array{return $this->get('/app/operations');}
    public function appointments(array $query=[]):array{return $this->get('/appointments',$query);}
    public function createAppointment(array $appointment,?string $key=null):array{return $this->post('/appointments',$appointment,$key);}
    public function customers(array $query=[]):array{return $this->get('/customers',$query);}
    public function createCustomer(array $customer,?string $key=null):array{return $this->post('/customers',$customer,$key);}
    public function conversations(array $query=[]):array{return $this->get('/conversations',$query);}
    public function reply(int $threadId,string $text,?string $key=null):array{return $this->post("/conversations/{$threadId}/messages",['text'=>$text],$key);}
    public function socialPosts(array $query=[]):array{return $this->get('/social/posts',$query);}
    public function createSocialPost(array $post,?string $key=null):array{return $this->post('/social/posts',$post,$key);}
    public function tasks(array $query=[]):array{return $this->get('/tasks',$query);}
    public function createTask(array $task,?string $key=null):array{return $this->post('/tasks',$task,$key);}
    public function additions(array $query=[]):array{return $this->get('/additions',$query);}
    public function notifications(array $query=[]):array{return $this->get('/notifications',$query);}
    public function analytics(array $query=[]):array{return $this->get('/analytics/overview',$query);}
    public function execute(string $operationKey,array $input=[],?string $key=null):array{return $this->post('/app/actions/execute',['operationKey'=>$operationKey,'input'=>$input],$key);}
    public function get(string $path,array $query=[]):array{return $this->request('GET',$path,$query);}
    public function post(string $path,array $body=[],?string $key=null):array{return $this->request('POST',$path,$body,$key);}
    public function patch(string $path,array $body=[],?string $key=null):array{return $this->request('PATCH',$path,$body,$key);}
    public function put(string $path,array $body=[],?string $key=null):array{return $this->request('PUT',$path,$body,$key);}
    public function delete(string $path,array $body=[],?string $key=null):array{return $this->request('DELETE',$path,$body,$key);}
    public function request(string $method,string $path,array $data=[],?string $idempotencyKey=null):array
    {
        $method=strtoupper($method);if(!in_array($method,['GET','POST','PATCH','PUT','DELETE'],true))throw new \InvalidArgumentException('Unsupported HTTP method.');if(!str_starts_with($path,'/')||str_contains($path,'://'))throw new \InvalidArgumentException('Path must be relative to the configured API base URL.');
        $mutating=$method!=='GET';$idempotencyKey=$mutating?($idempotencyKey?:self::idempotencyKey()):null;$url=rtrim($this->baseUrl,'/').$path;$body=null;
        if($method==='GET'&&$data!==[])$url.='?'.http_build_query($data,'','&',PHP_QUERY_RFC3986);elseif($method!=='GET')$body=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        $headers=['Accept: application/json','Authorization: Bearer '.$this->apiKey,'X-Aymudo-Store: '.$this->storeId,'User-Agent: Aymudo-PHP/1.0'];if($body!==null)$headers[]='Content-Type: application/json';if($idempotencyKey!==null)$headers[]='Idempotency-Key: '.$idempotencyKey;
        $attempts=$method==='GET'?3:1;for($attempt=1;$attempt<=$attempts;$attempt++){try{$response=($this->transport??new CurlTransport())->send($method,$url,$headers,$body,$this->timeoutSeconds);}catch(\Throwable $error){if($mutating)throw new UnknownOutcomeException('The write outcome is unknown. Retry only with the same idempotency key.',(string)$idempotencyKey,$error);if($attempt===$attempts)throw new AymudoException($error->getMessage(),'transport_error',0,null,[],$error);usleep(150000*$attempt);continue;}if($method==='GET'&&($response['status']===429||$response['status']>=500)&&$attempt<$attempts){$seconds=min(2,max(1,(int)($response['headers']['retry-after']??1)));usleep($seconds*1000000);continue;}return $this->decode($response);}throw new AymudoException('Aymudo request failed.','request_failed');
    }
    private function decode(array $response):array
    {try{$decoded=$response['body']===''?[]:json_decode($response['body'],true,512,JSON_THROW_ON_ERROR);}catch(JsonException $error){throw new AymudoException('Aymudo returned invalid JSON.','invalid_response',(int)$response['status'],null,[],$error);}if(!is_array($decoded))$decoded=[];if($response['status']>=400){$error=is_array($decoded['error']??null)?$decoded['error']:$decoded;throw new AymudoException((string)($error['message']??'Aymudo API request failed.'),(string)($error['code']??'api_error'),(int)$response['status'],isset($error['requestID'])?(string)$error['requestID']:null,(array)($error['details']??[]));}return isset($decoded['data'])&&is_array($decoded['data'])?$decoded['data']:$decoded;}
    public static function idempotencyKey():string{return 'php_'.bin2hex(random_bytes(16));}
    private static function env(string $key):string{$value=trim((string)getenv($key));if($value==='')throw new \RuntimeException("{$key} is required.");return $value;}
}
