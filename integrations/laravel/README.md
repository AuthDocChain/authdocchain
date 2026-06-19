# AuthDocChain Laravel Integration

Reference Laravel integration for the AuthDocChain Protocol.

This package demonstrates how to integrate document certification and verification into a Laravel application using the AuthDocChain API.

---

## Directory Structure

```text
laravel/
├── app/
│   └── Services/
│       └── AuthDocChainService.php
│
├── config/
│   └── services.php
│
├── examples/
│   ├── CertifyRequest.php
│   ├── VerifyRequest.php
│   ├── certify-example.php
│   └── verify-example.php
│
└── README.md
```

---

## Requirements

* PHP 8.1+
* Laravel 10+
* Composer
* AuthDocChain API Key

---

## Installation

Install an HTTP client:

```bash
composer require guzzlehttp/guzzle
```

Copy the integration files into your Laravel application:

```text
app/Services/AuthDocChainService.php
config/services.php
```

Add your API key to `.env`:

```env
AUTHDOCCHAIN_API_KEY=your_api_key
```

---

## Configuration

Add the AuthDocChain configuration to `config/services.php`:

```php
'authdocchain' => [
    'api_key' => env('AUTHDOCCHAIN_API_KEY'),
],
```

---

## Service Registration

Create the service:

```php
use App\Services\AuthDocChainService;

$authDocChain = new AuthDocChainService();
```

---

## Certify a Document

```php
$result = $authDocChain->certify(
    $uploadedFile->getRealPath(),
    [
        'title' => 'Bachelor Degree',
        'recipient_name' => 'John Doe'
    ]
);
```

---

## Verify a Document

Using a file:

```php
$result = $authDocChain->verifyFile(
    $uploadedFile->getRealPath()
);
```

Using a certification reference:

```php
$result = $authDocChain->verifyReference(
    'ADC-XXXXXXXX'
);
```

---

## Examples

The `examples/` directory contains sample request classes and usage examples that can be adapted to your application.

---

## Security Notes

* Document fingerprints are generated locally before verification.
* Original files remain under your control.
* Certified documents can be verified independently through the AuthDocChain Protocol.
* Never expose your API key in client-side code.

---

## Related Documentation

* `../../PROTOCOL.md`
* `../../WHITEPAPER.md`
* `../../specs/AIP-0001-document-certification.md`
* `../../specs/AIP-0002-hash-standard.md`
* `../../specs/AIP-0003-qr-stamp-format.md`

---

## License

Apache-2.0
