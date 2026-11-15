# Aymudo PHP SDK

Official, dependency-light PHP 8.2+ client for the Aymudo Platform API.

## Install

```bash
composer require aymudo/aymudo-php-sdk
```

Until the first Packagist release, use this repository as a Composer VCS source.

## Configure

Never commit API keys. Store them in environment variables or a secret manager.

```dotenv
AYMUDO_API_KEY=apk_live_replace_me
AYMUDO_STORE_ID=str_replace_me
AYMUDO_API_BASE_URL=https://api.aymudo.com/api/platform/v1
```

```php
use Aymudo\Client;
$aymudo = Client::fromEnvironment();
$capabilities = $aymudo->capabilities();
$operations = $aymudo->operations();
```

`GET /capabilities` reports friendly first-class features. `GET /app/operations`
returns the exact source-reviewed operations assigned to the store, including request
and response schemas. Build menus from these responses instead of hard-coding tiers.

## Common operations

```php
$appointments = $aymudo->appointments(['from' => '2026-11-01', 'to' => '2026-11-30']);
$customers = $aymudo->customers(['search' => 'Ayse']);
$threads = $aymudo->conversations();
$posts = $aymudo->socialPosts();

$created = $aymudo->createAppointment([
    'customerID' => 7102,
    'serviceIDs' => [412],
    'branchID' => 18,
    'branchStaffID' => 83,
    'date' => '2026-11-18',
    'startTime' => '14:30',
]);

$result = $aymudo->execute('operation.tasks.save', [
    'note' => 'Call customer',
    'todoDateTime' => '2026-11-18 09:00:00',
]);
```

Mutating requests automatically receive an `Idempotency-Key`. Supply your own stable key
when a job may be retried. The SDK never automatically retries a write after an ambiguous
network failure; `UnknownOutcomeException` exposes the key that must be reused.

## Webhook verification

Verify the signature against the unmodified request body:

```php
$expected = 'v1=' . hash_hmac('sha256', $rawBody, $signingSecret);
if (!hash_equals($expected, $_SERVER['HTTP_X_AYMUDO_SIGNATURE'] ?? '')) {
    http_response_code(401);
    exit;
}
```

Full documentation: <https://www.aymudo.com/developers>

## Development

```bash
composer install
composer test
```

## License

MIT
