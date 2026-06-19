# AuthDocChain SDK — Laravel Starter

A production-ready Laravel integration for the AuthDocChain SDK.
Demonstrates certification, physical QR sealing, file-based verification,
and paginated document listing — with zero file storage and no exposure
of internal infrastructure.

---

## What this starter does

| Feature | How |
|---|---|
| **Certify a document** | Upload → integrity verified server-side → record created |
| **Certify + QR seal** | Sealed PDF streamed directly to the user, never written to disk |
| **Verify by upload** | Fingerprint computed on your server — file never sent to third parties |
| **Verify by reference** | Paste a reference ID or fingerprint |
| **List documents** | Paginated table, 15 per page |
| **Dashboard + quota** | Live quota bar and account stats |

---

## Requirements

- PHP 8.1+
- Composer
- Laravel 10 or 11
- AuthDocChain API key (Pro plan or higher)

---

## Installation

```bash
# 1. Create a new Laravel project
composer create-project laravel/laravel my-app
cd my-app

# 2. Install Guzzle
composer require guzzlehttp/guzzle

# 3. Copy all files from this starter into the project

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# 5. Set your API key
# Edit .env → AUTHDOCCHAIN_API_KEY=adc_live_your_key

# 6. Start
php artisan serve
```

Open http://localhost:8000

---

## Files

```
app/Services/AuthDocChainService.php      SDK wrapper
app/Http/Controllers/DocumentController.php
app/Http/Requests/CertifyRequest.php
app/Http/Requests/VerifyRequest.php
config/services.php
routes/web.php
resources/views/layouts/app.blade.php
resources/views/documents/index.blade.php   Dashboard
resources/views/documents/certify.blade.php Certify form
resources/views/documents/result.blade.php  Certification result
resources/views/documents/verify.blade.php  Verify (file upload or reference)
resources/views/documents/list.blade.php    Paginated document list
```

---

## Key design decisions

**Zero file storage** — Certified PDFs are streamed directly from the API
response to the browser. No file is written to disk on your server,
consistent with AuthDocChain's zero-storage architecture.

**Server-side fingerprinting** — When verifying by upload, the fingerprint
is computed on your server (`hash('sha256', $content)`). The file itself
never leaves your infrastructure.

**No tech exposure** — The views show only what the user needs to see:
name, institution, type, date, status. No internal references to the
underlying stack are displayed.

---

## Error codes

| Code | Meaning |
|---|---|
| `400 HASH_MISMATCH` | File content doesn't match the provided fingerprint |
| `400 ALREADY_STAMPED` | PDF was already sealed — use the original |
| `400 ALREADY_CERTIFIED` | This document was already certified |
| `400 QR_SIG_INVALID` | Seal was copied from another document |
| `403` | API key doesn't have SDK access |
| `429` | Rate limit exceeded (200 req/min) |

---

## License

MIT
