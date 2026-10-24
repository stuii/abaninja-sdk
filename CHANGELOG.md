# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Breaking changes

See [UPGRADE.md](UPGRADE.md) for how to update your code.

- The license changed from GPL-3.0-or-later to MIT.
- Requires `symfony/uid`. All uuids are `Symfony\Component\Uid\Uuid` objects instead of strings:
  - model properties, e.g. `Person::$uuid`, `TaxRate::$uuid`, `InvoiceReceiver::$companyUuid`
  - `InvoiceReceiver::setPerson()` and `setCompany()`
  - service parameters, e.g. `Addresses::getSinglePrivateAddress()`, `Addresses::checkCustomerNumber()`,
    `Documents::getInvoiceByUuid()`, `Payments::getPaymentByUuid()`, `Webhooks::getSingleWebhook()`, `Webhooks::deleteWebhook()`
  - the account uuid passed to the services
- `WebhookEventType::INVOICE_PAID` (`invoice-paid`) was replaced by `WebhookEventType::PRODUCT_UPDATED` (`product-updated`).
- `Client::send()` returns a `Response` object (`$response->httpCode`, `$response->body`, `$response->data()`)
  instead of an `['httpCode' => ..., 'response' => ...]` array.
- `Client` is immutable. `setApiKey()` is now an alias of the new `withApiKey()` and returns a new client
  instead of changing the existing one: use `$client = $client->setApiKey('...')`.
- The `Client` constructor accepts `null` as `$baseUrl` (default URL) and an optional `HttpClientInterface` as third argument.
- The base URL must use https, otherwise the `Client` constructor throws an `InvalidArgumentException`.
  `Client::send()` only accepts absolute URLs starting with the base URL, so the API key is never sent to another host.
- `Client` and the services are readonly classes, so classes extending them must be readonly as well.
- All HTTP status codes from 400 upwards now throw an exception. Previously only 401, 403, 404 and 500 did,
  and other errors were returned as if successful.
- Exceptions receive their data through the constructor (`new AbaNinjaException($message, $code, $data)`).
  `AbaNinjaException::setData()` was removed, `getData()` is unchanged.
- Dates on models are `DateTimeImmutable` instead of `DateTime`.
- Enum values the SDK does not know yet become `null` instead of throwing a `ValueError`, so `Contact::$type`
  is nullable (`Payment::$direction` and `Payment::$paymentType` already were). Unknown webhook event types
  are left out of `Webhook::$webhookEventTypes`.
- Properties that may be missing in API responses are nullable and default to `null`:
  - `Company::$idNumber`, `$vatNumber`, `$currencyCode`, `$language`, `$privateNotes`, `$paymentTerms`
    and `$automaticDunning` (was `bool`)
  - `ContactPerson::$salutation`
  - all `Payment` properties
  - `Webhook::$uuid` and `Webhook::$signatureKey`
  - `IbanPaymentInstructions::$bic`
  - `InvoiceImport::$paymentInstructions`
  - `TaxPosition::$taxRate`
- `Addresses::updatePrivateAddress()` throws a `RequiredDataMissingException` (code 1304) for persons without uuid.
- `Documents::availableActions()` and `executeAction()` throw a `RequiredDataMissingException` (code 1305)
  for invoices without uuid.
- `Payments::createIncomingPayment()` throws a `RequiredDataMissingException` for invoices without uuid (code 1305)
  and for payments without amount, currency, date, direction or type (code 1306) instead of sending them.
- `InvoiceReceiver::addAdditionalReceiver()` returns `false` for persons without a uuid.
- Codeception was replaced by PHPUnit as dev dependency.

### Added

- `AbaNinja` entry point: `AbaNinja::create($apiKey, $accountUuid)->addresses()`, `->documents()`, `->payments()`,
  `->taxRates()`, `->webhooks()`.
- `Http\HttpClientInterface` with the default implementation `Http\CurlHttpClient` (30s timeout, 10s connect timeout),
  so the HTTP layer can be replaced in tests.
- `ValidationException` for HTTP 400 and 422 (code 9906) and `ConnectionException` for network errors (code 9907).
  Other unexpected status codes throw a `ResponseException` with code 9908.
- `Client::withApiKey()` and `Client::DEFAULT_BASE_URL`.
- The API key is hidden in `var_dump()`/`print_r()` output and in stack traces.
- `.gitattributes` keeps tests, examples and other development files out of the Composer package.
- `Support\Money` helper to convert between API amounts and integer cents.
- `Support\Uuids` helper. Uuids in API responses must be in the RFC 4122 format, otherwise
  an `UnknownFormatException` (code 9805) is thrown.
- `TaxPosition::$taxRateUuid`, filled from API responses.
- `JsonSerializable` on all models, including `ContactPerson`, `Payment`, `TaxRate` and `Webhook`.
- `ContactPerson::toArray()` is implemented.
- Unit tests with full line coverage (`composer test`, `composer test:coverage`).
- GitHub Actions workflow running the tests on PHP 8.4 and 8.5 and uploading the coverage to Codecov.
- README badges, installation instructions, error overview and a short feature list.
- Examples for `Addresses::updatePrivateAddress()`, `Payments::getPaymentByUuid()`, `TaxRates::getAllTaxRates()`
  and all `Webhooks` methods.
- `CHANGELOG.md` and `UPGRADE.md`.

### Fixed

- `Addresses::checkCustomerNumber()` ignored the `$addressUuidToIgnore` parameter.
- `Addresses::getCompanyAddressList()` never sent the `page`, `limit` and `tags` filters.
- `DuplicatesFoundException::getData()` was always empty because the address type was compared incorrectly.
- `InvoiceReceiver::addAdditionalReceiver()` changed any receiver into a company receiver.
  Additional receivers are now sent as a list of person uuids.
- `Documents::getInvoiceByUuid()` returned a `NinjaInvoice` without uuid, so actions could not be run on it.
- Amounts were truncated when converted to cents (e.g. 19.99 became 1998 instead of 1999).
- Failed connections and invalid responses are reported as exceptions instead of causing PHP errors.
  Empty responses (e.g. HTTP 204) no longer throw an "invalid JSON" exception.
- Serializing a `Person` without salutation threw an error.
- Serializing a `TaxPosition` without tax rate, or one filled from the API, threw an error.
- A person invoice receiver dropped its delivery address when serialized.
- Filling contacts, payments and webhooks failed with a `ValueError` when the API returned an enum value
  the SDK does not know yet. `DocumentAction::fillAll()` skips unknown actions instead of failing.
- `Documents::availableActions()` failed when the API returned the actions as list or as empty array (`[]`).
- List methods failed when the API returned `"data": null`, they return an empty array now.
- Serializing a `Payment` with missing fields threw an error.
- Filling models from API responses no longer fails when optional fields are missing.
- The examples use `DocumentAction::MARK_SENT` (renamed in 0.0.13), a valid `DateInterval` and work from any directory.
- `ABANINJA_API_BASE_URL` in `.env.example` is empty, so the default API URL is used.

### Changed

- `declare(strict_types=1)` in all files.
- Requests send an `Accept: application/json` header. `DELETE` requests no longer send a request body.
- `fillAll()` on models accepts any iterable instead of only arrays.
- Services share the `Service` base class, models share the `FillsCollections` trait for `fillAll()`.
- Docblocks list the SDK exceptions each method can throw.
- The examples use the `AbaNinja` entry point and share `examples/bootstrap.php`.
- `minimum-stability` is `stable`.
- The feature tables with links to individual API endpoints were removed from the README, the links are outdated.

## [0.0.14] - 2026-10-08

### Added

- `PaymentType::STRIPE_CREDIT_CARD` and `PaymentType::STRIPE_TWINT`.

## [0.0.13] - 2025-12-22

### Changed

- `DocumentAction` updated to the current list of API actions. Several cases were renamed,
  e.g. `MARK_AS_SENT` → `MARK_SENT`, `SEND_EMAIL` → `EMAIL`, `CONVERT_TO_INVOICE` → `CONVERT_QUOTE_TO_INVOICE`.
- `Address::$address` and `Address::$streetNumber` are nullable.

## [0.0.12] - 2025-10-01

### Added

- `Webhooks::createWebhook()`.

### Changed

- `Webhook` properties are public.

## [0.0.11] - 2025-10-01

### Added

- Webhooks: `Webhooks::getAllWebhooks()`, `getSingleWebhook()`, `deleteWebhook()` and `validateSignature()`,
  `Webhook` model and `WebhookEventType` enum.

### Fixed

- `TaxRate::$isInactive` was read from the wrong field.

## [0.0.10] - 2025-09-30

### Added

- Payments: `Payments::getPaymentByUuid()` and `createIncomingPayment()`, `Payment` model,
  `PaymentDirection` and `PaymentType` enums.
- Tax rates: `TaxRates::getAllTaxRates()` and `TaxRate` model.

### Changed

- Requires PHP 8.4.

## [0.0.9] - 2025-03-03

### Changed

- License changed from Apache-2.0 to GPL-3.0-or-later, `LICENSE` file added.

## [0.0.8] - 2024-03-10

### Added

- `DocumentAction::ACTIVITY`.

### Changed

- The decoded API response is attached to all exceptions thrown by the client.

## [0.0.7] - 2023-11-02

### Added

- `Salutation::MRS`.

### Changed

- `Person::$salutation` is optional.

## [0.0.6] - 2023-10-30

### Fixed

- Filling a person `InvoiceReceiver` from an API response.

## [0.0.5] - 2023-10-30

### Fixed

- Request data for the different `InvoiceReceiver` types.

## [0.0.4] - 2023-10-30

### Added

- `Address` includes its uuid when serialized.

## [0.0.3] - 2023-10-29

### Changed

- `Documents::importInvoice()` returns the imported `InvoiceImport`.

## [0.0.2] - 2023-10-27

### Added

- `Documents::getInvoiceByUuid()`, `availableActions()` and `executeAction()`, `DocumentAction` enum.
- `Addresses::getSinglePrivateAddress()`.
- `UnexpectedErrorException` for HTTP 500, `UnknownFormatException`, `WrongStateException`.

### Changed

- Invoice models moved to `Models\Documents\Invoices`.

## [0.0.1] - 2023-10-26

### Added

- Initial release with `Client`, `Addresses::checkCustomerNumber()`, `getCompanyAddressList()`,
  `createNewCompanyAddress()`, `createNewPrivateAddress()` and `Documents::importInvoice()`.

[Unreleased]: https://github.com/stuii/abaninja-sdk/compare/0.0.14...HEAD
[0.0.14]: https://github.com/stuii/abaninja-sdk/compare/0.0.13...0.0.14
[0.0.13]: https://github.com/stuii/abaninja-sdk/compare/0.0.12...0.0.13
[0.0.12]: https://github.com/stuii/abaninja-sdk/compare/0.0.11...0.0.12
[0.0.11]: https://github.com/stuii/abaninja-sdk/compare/0.0.10...0.0.11
[0.0.10]: https://github.com/stuii/abaninja-sdk/compare/0.0.9...0.0.10
[0.0.9]: https://github.com/stuii/abaninja-sdk/compare/0.0.8...0.0.9
[0.0.8]: https://github.com/stuii/abaninja-sdk/compare/0.0.7...0.0.8
[0.0.7]: https://github.com/stuii/abaninja-sdk/compare/0.0.6...0.0.7
[0.0.6]: https://github.com/stuii/abaninja-sdk/compare/0.0.5...0.0.6
[0.0.5]: https://github.com/stuii/abaninja-sdk/compare/0.0.4...0.0.5
[0.0.4]: https://github.com/stuii/abaninja-sdk/compare/0.0.3...0.0.4
[0.0.3]: https://github.com/stuii/abaninja-sdk/compare/0.0.2...0.0.3
[0.0.2]: https://github.com/stuii/abaninja-sdk/compare/0.0.1...0.0.2
[0.0.1]: https://github.com/stuii/abaninja-sdk/releases/tag/0.0.1
