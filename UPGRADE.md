# Upgrade guide

## From 0.0.x to 0.1.0

0.1.0 contains breaking changes. Most projects only need the first three steps:
[uuids](#1-uuids-are-uuid-objects), [the client](#2-client) and [error handling](#3-error-handling).
The complete list of changes is in the [CHANGELOG](CHANGELOG.md).

```bash
composer require stui/abaninja-sdk:^0.1
```

PHP 8.4 is still the minimum version. The new dependency `symfony/uid` is installed automatically.

### 1. Uuids are `Uuid` objects

All uuids are [`Symfony\Component\Uid\Uuid`](https://symfony.com/doc/current/components/uid.html) objects
instead of strings: method parameters, model properties and the account uuid.

```php
// before
$addresses = new Addresses($client, 'f47ac10b-58cc-4372-a567-0e02b2c3d479');
$person = $addresses->getSinglePrivateAddress($personUuid);
$receiver->setPerson($person->uuid, $addressUuid);
echo $person->uuid;
```

```php
// after
use Symfony\Component\Uid\Uuid;

$addresses = new Addresses($client, Uuid::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479'));
$person = $addresses->getSinglePrivateAddress(Uuid::fromString($personUuid));
$receiver->setPerson($person->uuid, Uuid::fromString($addressUuid));
echo $person->uuid->toRfc4122(); // or (string) $person->uuid
```

`Uuid::fromString()` throws an `InvalidArgumentException` for invalid uuids, so invalid values are caught before a request is sent.
Compare uuids with `$a->equals($b)`, not with `===`.

Changed method parameters:

- `Addresses::checkCustomerNumber(string $customerNumber, ?Uuid $addressUuidToIgnore = null)`
- `Addresses::getSinglePrivateAddress(Uuid $personUuid)`
- `Documents::getInvoiceByUuid(Uuid $uuid)`
- `Payments::getPaymentByUuid(Uuid $uuid)`
- `Webhooks::getSingleWebhook(Uuid $uuid)` and `Webhooks::deleteWebhook(Uuid $uuid)`
- `InvoiceReceiver::setPerson()` and `InvoiceReceiver::setCompany()`
- the account uuid of all services

Changed model properties: every `$uuid` property, `InvoiceReceiver::$addressUuid`, `$personUuid`, `$companyUuid`,
`$contactPersonUuid` and `$deliveryAddressUuid`. `InvoiceReceiver::$additionalReceivers` contains `Person` or `Uuid` objects.

### 2. Client

#### The client is immutable

`setApiKey()` returns a new client instead of changing the existing one. Without assigning the result, the old key is still used.

```php
// before
$client->setApiKey($newKey);
```

```php
// after
$client = $client->setApiKey($newKey); // or $client->withApiKey($newKey)
```

Services keep the client they were created with, so create them again after changing the key.

#### Only https

The base URL must start with `https://`. `null` uses the default URL.

```php
// before
$client = new Client(apiKey: $apiKey, baseUrl: $_ENV['ABANINJA_API_BASE_URL']);
```

```php
// after
$client = new Client(apiKey: $apiKey, baseUrl: $_ENV['ABANINJA_API_BASE_URL'] ?: null);
```

An `http://` base URL throws an `InvalidArgumentException`. `Client::send()` only accepts paths or absolute URLs
starting with the base URL, so the API key is never sent to another host.

#### `Client::send()` returns a `Response`

Only relevant if you call `send()` directly.

```php
// before
$result = $client->send('/accounts/' . $accountUuid . '/accounting/v2/tax-rates');
if ($result['httpCode'] === 200) {
    $taxRates = $result['response']->data;
}
```

```php
// after
$response = $client->send('/accounts/' . $accountUuid . '/accounting/v2/tax-rates');
$taxRates = $response->data(); // $response->httpCode and $response->body are also available
```

Errors throw an exception (see below), so checking the status code is usually not needed anymore.

### 3. Error handling

Every HTTP status from 400 upwards throws an exception. Before, only 401, 403, 404 and 500 did,
and other errors were returned as if the request had succeeded.

| Situation                     | Before                         | After                                          |
|-------------------------------|--------------------------------|------------------------------------------------|
| HTTP 400, 422                 | returned as if successful      | `ValidationException` (9906)                   |
| HTTP 404                      | `ResponseException` (9904)     | unchanged                                      |
| other HTTP 4xx, e.g. 429      | returned as if successful      | `ResponseException` (9908)                     |
| HTTP 502, 503 and other 5xx   | returned as if successful      | `UnexpectedErrorException` (9905)              |
| API not reachable             | PHP warning or error           | `ConnectionException` (9907)                   |
| invalid uuid from the API     | –                              | `UnknownFormatException` (9805)                |
| unknown enum value from API   | `ValueError`                   | no exception, the value is `null` (see step 5) |

All exceptions still extend `AbaNinjaException`, so `catch (AbaNinjaException $e)` catches all of them.
The other codes are listed in the [README](README.md#errors).

Some methods also check their input before sending a request and throw a `RequiredDataMissingException`:

- `Addresses::updatePrivateAddress()` for a person without uuid (1304)
- `Documents::availableActions()`, `Documents::executeAction()` and `Payments::createIncomingPayment()`
  for an invoice without uuid (1305)
- `Payments::createIncomingPayment()` for a payment without amount, currency, date, direction or type (1306);
  `getData()` returns the missing fields

If you create exceptions yourself, pass the data to the constructor, `setData()` was removed:

```php
// before
$e = new AbaNinjaException('Something failed', 1000);
$e->setData($data);
```

```php
// after
$e = new AbaNinjaException('Something failed', 1000, $data);
```

### 4. Dates are `DateTimeImmutable`

All date properties (`InvoiceImport::$invoiceDate`, `$dueDate`, `$deliveryDate`, `Payment::$paymentDate`)
are `DateTimeImmutable`. Modifying methods return a new object instead of changing the existing one.

```php
// before
$invoice->invoiceDate = new DateTime('2025-11-01');
$invoice->dueDate = clone $invoice->invoiceDate;
$invoice->dueDate->modify('+20 days');
```

```php
// after
$invoice->invoiceDate = new DateTimeImmutable('2025-11-01');
$invoice->dueDate = $invoice->invoiceDate->modify('+20 days');
```

### 5. Nullable properties

Properties that the API does not always return are nullable now. Check for `null` before using them:

- `Company`: `$idNumber`, `$vatNumber`, `$currencyCode`, `$language`, `$privateNotes`, `$paymentTerms`
  and `$automaticDunning` (was `bool`)
- `ContactPerson::$salutation`
- all `Payment` properties
- `Webhook::$uuid` and `Webhook::$signatureKey`
- `IbanPaymentInstructions::$bic`
- `InvoiceImport::$paymentInstructions`
- `TaxPosition::$taxRate`

Enum values the SDK does not know yet are `null` instead of throwing a `ValueError`.
This affects `Contact::$type` (now nullable), `Payment::$direction` and `Payment::$paymentType`.
Unknown webhook event types and document actions are left out of their lists.

```php
// before
echo $contact->type->value;
```

```php
// after
echo $contact->type?->value ?? 'unknown';
```

### 6. Webhook event types

`WebhookEventType::INVOICE_PAID` (`invoice-paid`) was replaced by `WebhookEventType::PRODUCT_UPDATED` (`product-updated`).
Remove `INVOICE_PAID` from the webhooks you create.

### 7. Classes extending the SDK

`Client` and the services are readonly classes now. A class extending them must be declared `readonly` as well:

```php
// before
class MyAddresses extends Addresses
{
}
```

```php
// after
readonly class MyAddresses extends Addresses
{
}
```

To change how requests are sent, e.g. for logging or tests, implement `Stui\AbaNinja\Http\HttpClientInterface`
and pass it to the `Client` instead of extending it.

### Optional: new features

These need no changes, but may simplify your code:

- `AbaNinja::create($apiKey, $accountUuid)` creates all services for one account:
  `$abaNinja->addresses()`, `->documents()`, `->payments()`, `->taxRates()` and `->webhooks()`.
- `Http\HttpClientInterface` lets you test your code without calling the API, see `tests/FakeHttpClient.php`.
- `Support\Money::toCents()` and `fromCents()` convert between API amounts and the integer cents used by the models.

### License

The license changed from GPL-3.0-or-later to [MIT](LICENSE). You may use, change and redistribute the SDK,
including in closed source software, as long as the copyright notice is kept.
