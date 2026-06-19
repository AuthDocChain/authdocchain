````markdown
# AuthDocChain Laravel Starter

Production-ready Laravel integration for the AuthDocChain Protocol.

This starter demonstrates certification, verification, QR sealing, and document management while preserving document integrity and privacy.

---

## Features

| Feature | Description |
|----------|-------------|
| Document Certification | Certify documents through the AuthDocChain Protocol |
| QR-Sealed PDFs | Generate certified PDFs with verification QR seals |
| File Verification | Verify authenticity from uploaded files |
| Reference Verification | Verify using a certification reference or fingerprint |
| Document Listing | Paginated document management |
| Dashboard & Quotas | Monitor usage and account limits |

---

## Protocol Compliance

This starter follows the AuthDocChain Protocol specifications:

- AIP-0001 — Document Certification
- AIP-0002 — Hash Standard
- AIP-0003 — QR Stamp Format

For protocol details, see:

- `/specs`
- `/docs`
- `/PROTOCOL.md`

---

## Requirements

- PHP 8.1+
- Composer
- Laravel 10 or 11
- AuthDocChain API Key

---

## Installation

```bash
# Create a new Laravel project
composer create-project laravel/laravel my-app

cd my-app

# Install HTTP client
composer require guzzlehttp/guzzle

# Copy starter files into the project

# Configure environment
cp .env.example .env
php artisan key:generate

# Add your API key
AUTHDOCCHAIN_API_KEY=adc_live_your_key

# Start the application
php artisan serve
````

Open:

```text
http://localhost:8000
```

---

## Project Structure

```text
app/
├── Services/
│   └── AuthDocChainService.php
│
├── Http/
│   ├── Controllers/
│   │   └── DocumentController.php
│   │
│   └── Requests/
│       ├── CertifyRequest.php
│       └── VerifyRequest.php
│
config/
└── services.php
│
routes/
└── web.php
│
resources/
└── views/
    ├── layouts/
    │   └── app.blade.php
    │
    └── documents/
        ├── index.blade.php
        ├── certify.blade.php
        ├── result.blade.php
        ├── verify.blade.php
        └── list.blade.php
```

---

## Security Principles

### Zero Storage

Certified PDF files are streamed directly to the browser and are never written to local storage.

### Local Fingerprinting

When verifying uploaded documents, fingerprints are generated locally using SHA-256 before interacting with the AuthDocChain API.

### Tamper Detection

QR seals are cryptographically linked to document fingerprints, preventing seal reuse and document substitution.

---

## Error Codes

| Code              | Meaning                                                  |
| ----------------- | -------------------------------------------------------- |
| HASH_MISMATCH     | Document content does not match the expected fingerprint |
| ALREADY_STAMPED   | Document already contains a certification seal           |
| ALREADY_CERTIFIED | Document has already been certified                      |
| QR_SIG_INVALID    | Verification seal validation failed                      |
| UNAUTHORIZED      | Invalid API credentials                                  |
| RATE_LIMITED      | Request quota exceeded                                   |

---

## Related Resources

* PROTOCOL.md
* WHITEPAPER.md
* docs/architecture.md
* docs/certification-flow.md
* docs/verification-flow.md

---

## License

MIT

```
```
