# AuthDocChain™ — Technical Whitepaper

**Version:** 1.0  
**Published:** 2025  
**Authors:** AuthDocChain Protocol Labs  
**Contact:** contact@authdocchain.com

---

## Abstract

AuthDocChain is an open protocol for the cryptographic certification and universal verification of institutional documents. By combining SHA-256 fingerprinting, Polygon blockchain anchoring, IPFS decentralized storage, and AI-assisted semantic sealing, AuthDocChain enables any institution to certify the authenticity of a document without exposing its content — and enables any party to verify that authenticity without requiring an account or permission.

This whitepaper describes the technical architecture, cryptographic primitives, threat model, and protocol design decisions behind AuthDocChain v1.0.

---

## 1. Problem Statement

Document fraud is a systemic problem with significant institutional cost. Academic credential fraud, falsified government records, and manipulated legal documents represent billions of dollars in annual losses globally, and erode trust between institutions, employers, and individuals.

Existing solutions share three structural weaknesses:

**1.1 Centralized verification registries** require institutions to maintain and operate dedicated verification infrastructure. They are expensive, siloed, and create single points of failure. A diploma issued by a university in Cameroon cannot be easily verified by an employer in Canada without a bilateral agreement between the two institutions.

**1.2 Third-party document storage** requires the certifying party to upload document content to a third-party server. This is incompatible with confidentiality obligations governing contracts, medical records, notarial acts, and government documents. Certification-through-storage violates the data minimization principle of the GDPR (Article 5(1)(c)).

**1.3 Manual verification processes** are slow, costly, and do not scale. Most credential verification today involves phone calls, email exchanges, and PDF attachments — processes that are trivially forgeable and carry no cryptographic integrity guarantee.

AuthDocChain addresses all three weaknesses through a zero-knowledge, blockchain-anchored, universally verifiable protocol.

---

## 2. Architecture

### 2.1 System Overview

```
┌──────────────┐     HTTPS/TLS 1.3      ┌──────────────────────────┐
│   Client     │ ──────────────────────► │   AuthDocChain API       │
│  (Browser /  │                         │   (Node.js / Express)    │
│   SDK)       │ ◄────────────────────── │                          │
└──────────────┘                         └────────────┬─────────────┘
                                                      │
                              ┌───────────────────────┼──────────────────────┐
                              │                       │                      │
                    ┌─────────▼──────┐    ┌───────────▼───────┐   ┌─────────▼──────┐
                    │   PostgreSQL   │    │   Polygon (EVM)   │   │  IPFS / Pinata │
                    │  (off-chain    │    │  (on-chain anchor)│   │  (metadata pin)│
                    │   index)       │    └───────────────────┘   └────────────────┘
                    └────────────────┘
```

### 2.2 Components

| Component | Technology | Role |
|-----------|-----------|------|
| API Server | Node.js + Express + TypeScript | Protocol enforcement, routing, SDK |
| Database | PostgreSQL | Off-chain index, audit log, rate limiting |
| Blockchain | Polygon (Amoy testnet → Mainnet) | Immutable anchoring of fingerprints |
| Distributed storage | IPFS via Pinata | Metadata bundle pinning |
| AI analysis | Google Gemini | Semantic document sealing |
| PDF stamping | pdf-lib | QR stamp embedding |
| QR decoding | jsQR | Physical document verification |
| Auth | JWT + bcrypt + Cloudflare Turnstile | Session management, anti-bot |

---

## 3. Cryptographic Design

### 3.1 Document Fingerprinting

Every document certified under the AuthDocChain protocol is reduced to a **SHA-256 hash** — a 256-bit (64 hex character) cryptographic digest.

Properties relevant to this protocol:
- **Deterministic:** The same document always produces the same hash.
- **Collision-resistant:** It is computationally infeasible to find two documents with the same hash (2^128 operations under the birthday bound).
- **One-way:** It is computationally infeasible to reconstruct the original document from its hash.

As of Protocol v1.0, the server **independently recomputes** the hash from the submitted `fileData` and rejects any request where the client-provided hash does not match (`HASH_MISMATCH`). This prevents certification of forged or modified documents, and blocks double-certification of QR-stamped copies.

### 3.2 QR Stamp HMAC Signing

Physical QR stamps embed a signed payload to prevent replay attacks — the act of copying a QR code from one certified document and affixing it to a different, uncertified document.

The signature is computed as:

```
sig = HMAC-SHA256(
  key   = AUTHDOCCHAIN_QR_SECRET,
  input = hash + "|" + txId + "|" + iat
)
```

Where:
- `hash` is the SHA-256 fingerprint of the original document
- `txId` is the blockchain transaction ID of the anchoring transaction
- `iat` is the Unix timestamp of the stamp issuance
- `AUTHDOCCHAIN_QR_SECRET` is a server-side secret never exposed publicly

On verification, the server recomputes the HMAC and compares it to the signature in the QR payload. A mismatch returns `INVALID_QR_SIGNATURE`.

### 3.3 Double-Certification Prevention

When a PDF is stamped with a QR code, its SHA-256 hash changes (the file now contains additional bytes). Without additional safeguards, a stamped PDF could be submitted as a "new" document and receive a second blockchain anchor.

AuthDocChain prevents this through two mechanisms:

1. **PDF metadata detection:** Before certification, the server reads the PDF's metadata fields (`Keywords`, `Subject`). If AuthDocChain-specific metadata is detected, the request is rejected with `ALREADY_STAMPED`.

2. **Stamped hash storage:** After stamping, the server computes `SHA-256(stamped_pdf)` and stores it as `stamped_hash` alongside the original `hash`. All future certification requests check against both values:

```sql
SELECT * FROM documents WHERE hash = $1 OR stamped_hash = $1
```

### 3.4 Password and Secret Storage

| Secret type | Algorithm | Notes |
|-------------|-----------|-------|
| User passwords | bcrypt (cost 12) | Never stored in plaintext |
| MFA / OTP codes | bcrypt | Hashed before storage, TTL 10min |
| API keys | HMAC-SHA256 | Raw key shown once at creation only |
| JWT tokens | HS256 | Rotation on every refresh, blacklist in DB |
| QR signatures | HMAC-SHA256 | Server-side secret, never exposed |

### 3.5 Audit Log Integrity

Every action performed on the platform is logged in an immutable audit trail using **hash chaining**: each log entry includes a hash of the previous entry's content, making retroactive tampering detectable.

```
entry_n.hash = SHA-256(entry_n.content + entry_(n-1).hash)
```

---

## 4. Threat Model

### 4.1 Threats Addressed

| Threat | Mitigation |
|--------|-----------|
| Document forgery | SHA-256 fingerprint is anchored on-chain; any modification changes the hash |
| QR stamp copying | HMAC signature binds the QR to the specific document hash and TxID |
| Double-certification of stamped copy | `ALREADY_STAMPED` detection + `stamped_hash` column |
| Client-side hash manipulation | Server recomputes hash from `fileData`; `HASH_MISMATCH` on discrepancy |
| Replay attacks (QR) | HMAC-SHA256 with `iat` timestamp; server-side secret |
| Brute-force authentication | 5-attempt lockout, Cloudflare Turnstile, bcrypt cost 12 |
| Session hijacking | JWT blacklist, `session_version` invalidation, HttpOnly cookies |
| SQL injection | Parameterized queries throughout |
| CSRF | SameSite=Strict cookies + CSRF token |

### 4.2 Threats Out of Scope (v1.0)

| Threat | Notes |
|--------|-------|
| Compromise of `AUTHDOCCHAIN_QR_SECRET` | Operational security — key rotation procedure documented in SECURITY.md |
| Polygon blockchain reorg | Theoretical risk; mitigated by waiting for sufficient confirmations |
| IPFS content unavailability | Pinata pinning used; CID remains independently verifiable even if pin is lost |
| Certifying party fraud | The protocol certifies that *a specific institution certified a specific hash at a specific time*. It does not verify the legitimacy of the certifying institution itself. |

---

## 5. API Reference

### 5.1 Public Verification (no authentication)

```http
GET /api/v1/verify/{hash_or_txid}
```

**Parameters:**
- `hash_or_txid` — SHA-256 hash (64 hex chars) or blockchain TxID (`0x...`) or base64url-encoded QR payload

**Response:**
```json
{
  "valid": true,
  "qr_signature_verified": true,
  "document": {
    "name": "Diploma_2025.pdf",
    "institution": "Cambridge Research Labs",
    "type": "diploma",
    "timestamp": 1714000000000,
    "hash": "a3f9b2e14c...",
    "txId": "0x7a3f9b2e...",
    "ipfsCid": "QmXyZ...",
    "isPhysical": false
  }
}
```

### 5.2 Document Certification (requires `x-api-key`)

```http
POST /api/v1/certify
Content-Type: application/json
x-api-key: adc_live_••••••••••••••••
```

**Request body:**
```json
{
  "name": "Diploma_2025.pdf",
  "type": "diploma",
  "hash": "a3f9b2e14c...",
  "fileData": "<base64-encoded file content>",
  "fileName": "Diploma_2025.pdf",
  "physical": false
}
```

> ⚠️ `fileData` is **required** as of Protocol v1.0. The server recomputes the hash independently. Requests without `fileData` are rejected.

**Physical certification additional fields:**
```json
{
  "physical": true,
  "qrPosition": "bottom-right",
  "qrSize": 50,
  "stampAllPages": false
}
```

**Response:**
```json
{
  "txId": "0x7a3f9b2e...",
  "ipfsCid": "QmXyZ...",
  "hash": "a3f9b2e14c...",
  "stampedPdf": "<base64-encoded stamped PDF>"
}
```

### 5.3 Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `HASH_MISMATCH` | 400 | `fileData` hash does not match provided `hash` |
| `ALREADY_CERTIFIED` | 409 | Document already anchored on this hash |
| `ALREADY_STAMPED` | 409 | PDF already contains an AuthDocChain QR stamp |
| `INVALID_QR_SIGNATURE` | 400 | QR HMAC signature verification failed |
| `QUOTA_EXCEEDED` | 429 | Monthly certification quota reached |
| `INVALID_FILE_TYPE` | 415 | Unsupported MIME type |
| `SERVER_OFFLINE` | 503 | Blockchain or IPFS service temporarily unavailable |

---

## 6. SDK Code Examples

### 6.1 JavaScript / Node.js

```javascript
const fs = require('fs');

// Read file and encode to base64
const fileBuffer = fs.readFileSync('./Diploma_2025.pdf');
const fileData = fileBuffer.toString('base64');

// Certify
const response = await fetch('https://api.authdocchain.com/api/v1/certify', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'x-api-key': 'adc_live_••••••••••••••••',
  },
  body: JSON.stringify({
    name: 'Diploma_2025.pdf',
    type: 'diploma',
    hash: '<sha256-of-file>',   // client pre-computed
    fileData,                    // required: server will verify
    fileName: 'Diploma_2025.pdf',
  }),
});

const { txId, ipfsCid, hash } = await response.json();
```

### 6.2 Python

```python
import base64
import hashlib
import requests

# Read and encode file
with open('Diploma_2025.pdf', 'rb') as f:
    raw = f.read()

file_data = base64.b64encode(raw).decode('utf-8')
file_hash = hashlib.sha256(raw).hexdigest()

# Certify
response = requests.post(
    'https://api.authdocchain.com/api/v1/certify',
    headers={
        'Content-Type': 'application/json',
        'x-api-key': 'adc_live_••••••••••••••••',
    },
    json={
        'name': 'Diploma_2025.pdf',
        'type': 'diploma',
        'hash': file_hash,     # client pre-computed
        'fileData': file_data, # required: server will verify
        'fileName': 'Diploma_2025.pdf',
    }
)

result = response.json()
print(result['txId'], result['ipfsCid'])
```

---

## 7. Protocol Roadmap

| Phase | Target | Description |
|-------|--------|-------------|
| v1.0 | Q2 2025 | Core certification, physical QR, SDK, AI seal |
| v1.1 | Q3 2025 | Batch certification endpoint, webhook support |
| v1.2 | Q4 2025 | Multi-chain support (Ethereum mainnet, Arbitrum) |
| v2.0 | 2026 | Decentralized governance, open AIP editor role, protocol council |

---

## 8. License and Trademark

The AuthDocChain Protocol specifications are licensed under the [Apache License 2.0](../LICENSE).

AuthDocChain™ is a trademark of AuthDocChain Protocol Labs.  
See [legal/trademark.md](../legal/trademark.md) for permitted use of the AuthDocChain name and mark.
