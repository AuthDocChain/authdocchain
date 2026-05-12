# AuthDocChain™ Protocol

> **The cryptographic standard for institutional document certification.**  
> Immutable blockchain anchoring · Zero-knowledge architecture · Physical QR verification

[![License: Apache-2.0](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](./LICENSE)
[![Protocol Version](https://img.shields.io/badge/Protocol-v1.0.0-indigo)](./PROTOCOL.md)
[![Status: Active](https://img.shields.io/badge/Status-Active-green)]()
[![AIPs](https://img.shields.io/badge/AIPs-3%20active-orange)](./aip/)

---

## What is AuthDocChain?

AuthDocChain is an open protocol for cryptographic document certification and verification. It defines the standards by which any institution — university, government agency, law firm, or enterprise — can anchor a document's authenticity on a public blockchain, without ever exposing the document's content.

The protocol is built on three core primitives:

1. **Zero-knowledge hashing** — Documents are reduced to a SHA-256 fingerprint locally. The original content never leaves the certifying party's control.
2. **Immutable anchoring** — Fingerprints are anchored on the Polygon blockchain with an IPFS CID and an AI-generated semantic seal, creating a permanent, tamper-proof record.
3. **Universal verification** — Any party can verify a document's authenticity by hash, blockchain TxID, file upload, or physical QR scan — without an account or permission.

---

## Protocol Specifications

| AIP | Title | Status |
|-----|-------|--------|
| [AIP-0000](./aip/AIP-0000-purpose-and-process.md) | Purpose and Process | Active |
| [AIP-0001](./specs/AIP-0001-document-certification.md) | Document Certification Standard | Active |
| [AIP-0002](./specs/AIP-0002-hash-standard.md) | Hash & Fingerprint Standard | Active |
| [AIP-0003](./specs/AIP-0003-qr-stamp-format.md) | Physical QR Stamp Format | Active |

---

## Repository Structure

```
authdocchain-protocol/
├── README.md                   ← You are here
├── PROTOCOL.md                 ← Human-readable protocol overview
├── WHITEPAPER.md               ← Technical whitepaper
├── CHANGELOG.md                ← Protocol version history
├── CONTRIBUTING.md             ← How to contribute
├── CODE_OF_CONDUCT.md          ← Community standards
├── SECURITY.md                 ← Vulnerability disclosure policy
├── LICENSE                     ← Apache 2.0
│
├── specs/                      ← Normative technical specifications
│   ├── AIP-0001-document-certification.md
│   ├── AIP-0002-hash-standard.md
│   └── AIP-0003-qr-stamp-format.md
│
├── aip/                        ← AuthDocChain Improvement Proposals
│   ├── AIP-0000-purpose-and-process.md
│   └── AIP-template.md
│
├── docs/                       ← Non-normative documentation
│   ├── architecture.md
│   ├── certification-flow.md
│   ├── verification-flow.md
│   └── diagrams/
│
└── legal/
    └── trademark.md
```

---

## Certification Flow (Summary)

```
Certifying Party                AuthDocChain Protocol              Blockchain / IPFS
      │                                │                                  │
      │  1. hash(document) → SHA-256   │                                  │
      │──────────────────────────────► │                                  │
      │                                │  2. AI analysis → semantic seal  │
      │                                │  3. pin(metadata) → IPFS CID     │──► ipfs://Qm...
      │                                │  4. anchor(hash, CID) → TxID     │──► polygon:0x...
      │◄────────────────────────────── │                                  │
      │  { txId, ipfsCid, hash }        │                                  │
```

---

## Verification Flow (Summary)

```
Verifying Party                 AuthDocChain Protocol              Blockchain
      │                                │                                  │
      │  hash / txId / QR payload      │                                  │
      │──────────────────────────────► │                                  │
      │                                │  lookup(hash) → on-chain record  │──► ✓ or ✗
      │◄────────────────────────────── │                                  │
      │  { valid, institution,          │                                  │
      │    timestamp, txId, ipfsCid }   │                                  │
```

---

## Quick Start — Verification API

No authentication required for public verification.

**HTTP**
```http
GET https://api.authdocchain.com/api/v1/verify/{hash}
```

**cURL**
```bash
curl https://api.authdocchain.com/api/v1/verify/a3f9b2e14c7d8f1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5a6
```

**Response**
```json
{
  "valid": true,
  "document": {
    "name": "Diploma_2025.pdf",
    "institution": "Cambridge Research Labs",
    "type": "diploma",
    "timestamp": 1714000000000,
    "hash": "a3f9b2e14c7d8f1...",
    "txId": "0x7a3f9b2e14c7d8f1...",
    "ipfsCid": "QmXyZ..."
  }
}
```

---

## Certification API (SDK — Pro plan and above)

```bash
curl -X POST https://api.authdocchain.com/api/v1/certify \
  -H "x-api-key: adc_live_••••••••••••••••" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Diploma_2025.pdf",
    "type": "diploma",
    "hash": "a3f9b2e14...",
    "fileData": "base64encodedcontent...",
    "fileName": "Diploma_2025.pdf"
  }'
```

> ⚠️ `fileData` is **required** as of Protocol v1.0. The server recomputes the hash independently and rejects any request where the client-provided hash does not match. See [AIP-0001](./specs/AIP-0001-document-certification.md) for the full rationale.

---

## Error Codes

| Code | HTTP | Description |
|------|------|-------------|
| `HASH_MISMATCH` | 400 | Client hash does not match server-computed hash from `fileData` |
| `ALREADY_CERTIFIED` | 409 | A document with this exact hash is already anchored |
| `ALREADY_STAMPED` | 409 | Document contains an existing AuthDocChain QR stamp — re-certification blocked |
| `INVALID_QR_SIGNATURE` | 400 | QR payload HMAC signature is invalid — replay attack blocked |
| `QUOTA_EXCEEDED` | 429 | Monthly certification quota reached for this account |
| `INVALID_FILE_TYPE` | 415 | File type not supported for certification |

---

## Contributing

We welcome proposals, corrections, and discussions. Please read [CONTRIBUTING.md](./CONTRIBUTING.md) before opening a pull request.

To propose a change to the protocol, open an **AuthDocChain Improvement Proposal (AIP)** using the [AIP template](./aip/AIP-template.md).

---

## License

The AuthDocChain Protocol specifications are licensed under the [Apache License 2.0](./LICENSE).

AuthDocChain™ is a trademark of AuthDocChain Protocol Labs.  
See [legal/trademark.md](./legal/trademark.md) for permitted use.

---

## Links

- **Platform:** [authdocchain.com](https://authdocchain.com)
- **API:** `https://api.authdocchain.com`
- **Contact:** [contact@authdocchain.com](mailto:contact@authdocchain.com)
- **Security:** [security@authdocchain.com](mailto:security@authdocchain.com)
