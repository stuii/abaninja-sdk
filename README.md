# Featureset

## Addresses

| Status | Function                                           | SDK Syntax                                                                                                                                         | Reference                                                                                                                                      | Release |
|--------|----------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------|---------|
| ✅      | Check if the customerNumber already in use or not  | Addresses::checkCustomerNumber(<br/>&nbsp;&nbsp;string $customerNumber<br/>)                                                                       | [API Docs](https://www.abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1check-customer-number/get)       | v0.0.1  | 
| ✅      | Get a list of company addresses for given account  | Addresses::getCompanyAddressList(<br/>&nbsp;&nbsp;?int $page = null,<br/>&nbsp;&nbsp;?int $limit = null,<br/>&nbsp;&nbsp;?array $tags = null<br/>) | [API Docs](https://abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1companies/get)                       | v0.0.1  | 
| ❌      | Get Single Company                                 |                                                                                                                                                    | [API Docs](https://abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1companies~1%7BcompanyUuid%7D/get)    | -       | 
| ❌      | Update a single Company                            |                                                                                                                                                    | [API Docs](https://abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1companies~1%7BcompanyUuid%7D/patch)  | -       | 
| ❌      | Delete Company                                     |                                                                                                                                                    | [API Docs](https://abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1companies~1%7BcompanyUuid%7D/delete) | -       | 
| ❌      | Get a list of personal addresses for given account |                                                                                                                                                    | [API Docs](https://abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1persons/get)                         | -       | 
| ✅      | Get a single Person                                | Addresses::getSinglePrivateAddress(<br/>&nbsp;&nbsp;string $uuid<br/>)                                                                             | [API Docs](https://abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1persons~1%7BpersonUuid%7D/get)       | v0.0.2  | 
| ❌      | Update Person                                      |                                                                                                                                                    | [API Docs](https://abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1persons~1%7BpersonUuid%7D/patch)     | -       | 
| ❌      | Delete Person                                      |                                                                                                                                                    | [API Docs](https://abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1persons~1%7BpersonUuid%7D/delete)    | -       | 
| ✅      | Create new Person                                  | Addresses::createNewPrivateAddress(<br/>&nbsp;&nbsp;Person $person,<br/>&nbsp;&nbsp;bool $force = false<br/>)                                      | [API Docs](https://abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1addresses/post)                      | v0.0.1  | 
| ✅      | Create new Company                                 | Addresses::createNewCompanyAddress(<br/>&nbsp;&nbsp;Company $company,<br/>&nbsp;&nbsp;bool $force = false<br/>)                                    | [API Docs](https://abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1addresses/post)                      | v0.0.1  | 

## Documents

### Quotes

❌ No Features implemented yet

### Contract Notes

❌ No Features implemented yet

### Delivery Notes

❌ No Features implemented yet

### Invoices

| Status | Function                                 | SDK Syntax                                                                                              | Reference                                                                                                                                                                    | Release |
|--------|------------------------------------------|---------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------|
| ❌      | Invoice List                             |                                                                                                         | [API Docs](https://abaninja.ch/apidocs/#tag/DocumentsInvoices/paths/~1accounts~1%7BaccountUuid%7D~1documents~1v2~1invoices/get)                                              | -       | 
| ❌      | Create Invoice                           |                                                                                                         | [API Docs](https://abaninja.ch/apidocs/#tag/DocumentsInvoices/paths/~1accounts~1%7BaccountUuid%7D~1documents~1v2~1invoices/post)                                             | -       | 
| ✅      | Get single Invoice                       | Documents::getInvoiceByUuid(<br/>&nbsp;&nbsp;string $uuid<br/>)                                         | [API Docs](https://abaninja.ch/apidocs/#tag/DocumentsInvoices/paths/~1accounts~1%7BaccountUuid%7D~1documents~1v2~1invoices~1%7BdocumentUuid%7D/get)                          | v0.0.2  | 
| ❌      | Update Invoice                           |                                                                                                         | [API Docs](https://abaninja.ch/apidocs/#tag/DocumentsInvoices/paths/~1accounts~1%7BaccountUuid%7D~1documents~1v2~1invoices~1%7BdocumentUuid%7D/patch)                        | -       | 
| ✅      | Get available Actions for an Invoice     | Documents::availableActions(<br/>&nbsp;&nbsp;Invoice $invoice<br/>)                                     | [API Docs](https://abaninja.ch/apidocs/#tag/DocumentsInvoices/paths/~1accounts~1%7BaccountUuid%7D~1documents~1v2~1invoices~1%7BdocumentUuid%7D~1actions/get)                 | v0.0.2  | 
| ✅      | Execute an action on an existing invoice | Documents::executeAction(<br/>&nbsp;&nbsp;Invoice $invoice<br/>&nbsp;&nbsp;DocumentAction $action<br/>) | [API Docs](https://abaninja.ch/apidocs/#tag/DocumentsInvoices/paths/~1accounts~1%7BaccountUuid%7D~1documents~1v2~1invoices~1%7BdocumentUuid%7D~1actions~1%7Baction%7D/patch) | v0.0.2  | 
| ✅      | Create Invoice by importing PDF          | Documents::importInvoice(<br/>&nbsp;&nbsp;Invoice $invoice<br/>)                                        | [API Docs](https://abaninja.ch/apidocs/#tag/DocumentsInvoicesImported/paths/~1accounts~1%7BaccountUuid%7D~1documents~1v2~1invoices~1import/post)                             | v0.0.1  | 

### Credit Notes

❌ No Features implemented yet

### Recurring Invoices

❌ No Features implemented yet

### Templates

❌ No Features implemented yet

### Receipts

❌ No Features implemented yet

### Document Queue

❌ No Features implemented yet