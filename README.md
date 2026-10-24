# AbaNinja SDK

[![Tests](https://github.com/stuii/abaninja-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/stuii/abaninja-sdk/actions/workflows/tests.yml)
[![Coverage](https://codecov.io/gh/stuii/abaninja-sdk/graph/badge.svg)](https://codecov.io/gh/stuii/abaninja-sdk)
[![Latest Version](https://img.shields.io/packagist/v/stui/abaninja-sdk)](https://packagist.org/packages/stui/abaninja-sdk)
[![PHP Version](https://img.shields.io/packagist/dependency-v/stui/abaninja-sdk/php)](composer.json)
[![Downloads](https://img.shields.io/packagist/dt/stui/abaninja-sdk)](https://packagist.org/packages/stui/abaninja-sdk)
[![License](https://img.shields.io/github/license/stuii/abaninja-sdk)](LICENSE)

PHP SDK for the public [AbaNinja API](https://abaninja.ch/apidocs/).

## Features

- **Addresses:** check customer numbers, list companies, get and update persons,
  create persons and companies with duplicate detection
- **Invoices:** get invoices, import invoices from PDF, list and execute invoice actions
- **Payments:** get payments, record incoming payments for invoices
- **Tax rates:** list all tax rates
- **Webhooks:** list, get, create and delete webhooks, validate signatures of incoming calls

Not supported yet: other document types (quotes, credit notes, …), creating or listing invoices,
listing persons, getting, updating or deleting companies and deleting persons.

## Requirements

- PHP 8.4 or newer
- `ext-curl`

## Installation

```bash
composer require stui/abaninja-sdk
```

Upgrading from 0.0.x? See the [upgrade guide](UPGRADE.md).

## Usage

```php
use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Symfony\Component\Uid\Uuid;

$abaNinja = AbaNinja::create(apiKey: '...', accountUuid: Uuid::fromString('...'));

try {
    $invoice = $abaNinja->documents()->getInvoiceByUuid(Uuid::fromString('...'));
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
    var_dump($e->getData()); // the decoded API response, if any
}
```

The services can also be created directly, e.g. `new Addresses($client, $accountUuid)`.

- All uuids are [`Symfony\Component\Uid\Uuid`](https://symfony.com/doc/current/components/uid.html) objects.
  `Uuid::fromString()` throws an `InvalidArgumentException` for invalid uuids.
- Amounts are integer cents, e.g. `10000` is CHF 100.00.
- Enum values the SDK does not know yet (e.g. a new payment type) are `null`. Unknown webhook event types
  and document actions are left out of their lists.
- The `Client` only connects via https and only sends the API key to its base URL.
  It is immutable: `setApiKey()` (alias of `withApiKey()`) returns a new client and leaves the original unchanged.

More examples can be found in [`examples/`](examples). Copy `.env.example` to `.env` to run them.

## Errors

All exceptions extend `Stui\AbaNinja\Exceptions\AbaNinjaException`. `getData()` returns the API response or the invalid value.

| Exception                      | Code                  | Thrown when                                                  |
|--------------------------------|-----------------------|--------------------------------------------------------------|
| `ValidationException`          | 9906                  | the API rejects the submitted data (HTTP 400, 422)           |
| `AuthenticationException`      | 9902                  | the API token is invalid (HTTP 401)                          |
| `ScopeException`               | 9903                  | the API token lacks the required scope (HTTP 403)            |
| `ResponseException`            | 9904                  | the resource does not exist (HTTP 404)                       |
| `ResponseException`            | 9908                  | the API returns any other error status                       |
| `ResponseException`            | 9901                  | the API returns invalid JSON                                 |
| `UnexpectedErrorException`     | 9905                  | the API has a server error (HTTP 5xx)                        |
| `ConnectionException`          | 9907                  | the API cannot be reached                                    |
| `UnknownFormatException`       | 9801, 9805            | the API returns an unknown invoice format or an invalid uuid |
| `DuplicatesFoundException`     | 1303                  | similar addresses exist, `getData()` returns them            |
| `RequiredDataMissingException` | 1301, 1302, 1304–1306 | required data is missing before the request is sent          |
| `WrongStateException`          | 9802                  | the document action is not available                         |

An invalid base URL, or a URL outside the base URL passed to `Client::send()`, throws an `InvalidArgumentException`.

## Testing your code

To test your own code without calling the API, pass an implementation of `Stui\AbaNinja\Http\HttpClientInterface`
to the `Client` (see [`tests/FakeHttpClient.php`](tests/FakeHttpClient.php)).

## Development

```bash
composer install
composer test
composer test:coverage # requires the pcov or xdebug extension
```

See [CHANGELOG.md](CHANGELOG.md) for the changes in each release and [UPGRADE.md](UPGRADE.md) for upgrading from 0.0.x.

## License

[MIT](LICENSE)
