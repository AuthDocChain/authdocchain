# AuthDocChain Protocol — Overview

**Version:** 1.0.0  
**Status:** Active  
**Last updated:** 2025  
**Maintainer:** AuthDocChain Protocol Labs

---

## 1. Purpose

The AuthDocChain Protocol defines a set of open, verifiable standards for the cryptographic certification and verification of institutional documents.

The protocol addresses three fundamental problems in document trust:

1. **Falsification** — Digital and physical documents can be forged. Existing verification mechanisms (manual checks, centralized registries) are slow, expensive, and failure-prone.
2. **Opacity** — Certifying parties must trust a third party with their document content. This is incompatible with confidentiality obligations in academic, government, and legal contexts.
3. **Fragmentation** — There is no universal standard for document authenticity. Verification processes vary by country, institution, and sector, creating friction and mutual distrust.

AuthDocChain solves all three by providing:
- A **zero-knowledge** certification model: only fingerprints are processed, never content.
- An **immutable public ledger**: once anchored, a certification cannot be altered or deleted.
- A **universal verification interface**: anyone can verify, with no account required.

---

## 2. Core Principles

### 2.1 Zero-Knowledge by Design

The protocol never requires, requests, or accepts document content for the purpose of creating a permanent record. The SHA-256 hash of a document is its sole identifier on the ledger. It is mathematically impossible to reconstruct the original document from its hash.

> **The protocol certifies without seeing. This is not a feature — it is a constraint.**

### 2.2 Immutability

A certification anchored on the Polygon blockchain is permanent. No party — including AuthDocChain Protocol Labs — can modify, delete, or invalidate a record once it has been committed to the chain.

### 2.3 Universal Verifiability

Verification is a public, unauthenticated operation. No account, no subscription, and no permission is required to verify a document. The verification endpoint is and will remain open.

### 2.4 Replay Attack Prevention

Physical QR stamps issued under this protocol are HMAC-signed with a document-specific secret. Copying a QR code from one document to another will fail verification. This is defined formally in [AIP-0003](../specs/AIP-0003-qr-stamp-format.md).

### 2.5 Server-Side Hash Verification

As of Protocol v1.0, all certification requests must include the raw file (`fileData`). The server independently computes the SHA-256 hash and rejects any request where the client-provided hash does not match. This prevents double-certification via QR-stamped copies. See [AIP-0001](../specs/AIP-0001-document-certification.md).

---

## 3. Protocol Stack

```
┌─────────────────────────────────────────────────────┐
│               Application Layer                      │
│   (authdocchain.com · REST SDK · Physical QR)        │
├─────────────────────────────────────────────────────┤
│               Protocol Layer                         │
│   Certification · Verification · QR Stamp Standard  │
├─────────────────────────────────────────────────────┤
│               Cryptographic Layer                    │
│   SHA-256 hashing · HMAC-SHA256 signing              │
│   bcrypt password hashing · JWT session management  │
├─────────────────────────────────────────────────────┤
│               Storage Layer                          │
│   Polygon blockchain (anchoring)                     │
│   IPFS / Pinata (metadata pinning)                   │
│   PostgreSQL (off-chain index, audit log)            │
└─────────────────────────────────────────────────────┘
```

---

## 4. Document Lifecycle

### 4.1 Certification

```
1. Client computes SHA-256(document)         → hash
2. Client sends { name, type, hash, fileData, fileName }
3. Server recomputes SHA-256(fileData)       → serverHash
4. Server asserts hash == serverHash         → or: HASH_MISMATCH
5. Server checks: hash not already anchored  → or: ALREADY_CERTIFIED
6. Server checks: PDF not already stamped    → or: ALREADY_STAMPED
7. Server generates AI semantic seal         → aiAnalysis
8. Server pins { hash, aiAnalysis, meta }    → ipfsCid
9. Server anchors { hash, ipfsCid } on chain → txId
10. Server stores stamped_hash (if physical) → deduplication
11. Server returns { txId, ipfsCid, hash }
```

### 4.2 Physical Certification (QR Stamp)

```
1. Steps 1–9 above
2. Server embeds QR code into PDF at specified position
3. QR payload = base64url(JSON { v, origin, hash, txId, iat, sig })
4. sig = HMAC-SHA256(hash + txId + iat, secret)
5. Server computes SHA-256(stamped_pdf)      → stampedHash
6. Server stores stampedHash alongside hash  → deduplication covers both
7. Server returns { txId, ipfsCid, hash, stampedPdf (base64) }
```

### 4.3 Verification

```
Input: hash | txId | QR payload (raw or base64url)

1. If QR payload: decode → extract hash, verify HMAC sig
   → INVALID_QR_SIGNATURE if sig mismatch
2. Lookup: WHERE hash = $1 OR stamped_hash = $1 OR tx_id = $1
3. If found: return { valid: true, document: { ... } }
4. If not found: return { valid: false }
```

---

## 5. Data Model

### 5.1 On-Chain Record

The following data is written to the Polygon blockchain:

```
{
  hash:    string   // SHA-256 fingerprint of the document
  ipfsCid: string   // IPFS CID of the off-chain metadata bundle
}
```

### 5.2 IPFS Metadata Bundle

```json
{
  "protocol": "authdocchain",
  "version": "1.0",
  "hash": "a3f9b2e14c...",
  "institution": "Cambridge Research Labs",
  "type": "diploma",
  "timestamp": 1714000000000,
  "aiSeal": {
    "summary": "...",
    "docType": "diploma",
    "confidence": 0.98
  }
}
```

### 5.3 QR Payload (Physical Documents)

```json
{
  "v": 1,
  "origin": "authdocchain",
  "hash": "a3f9b2e14c...",
  "txId": "0x7a3f9b2e...",
  "iat": 1714000000,
  "sig": "hmac_sha256_signature"
}
```

Encoded as `base64url(JSON.stringify(payload))` and embedded in the QR code.

---

## 6. Error Reference

| Code | Description | Resolution |
|------|-------------|------------|
| `HASH_MISMATCH` | Client hash ≠ server-computed hash | Recompute hash from the original unmodified file |
| `ALREADY_CERTIFIED` | Hash already exists in the ledger | Document was previously certified — retrieve existing record |
| `ALREADY_STAMPED` | PDF contains an existing AuthDocChain QR stamp | Do not re-certify stamped documents — use the original |
| `INVALID_QR_SIGNATURE` | HMAC signature verification failed | QR may have been copied from another document |
| `QUOTA_EXCEEDED` | Monthly certification quota reached | Upgrade plan or wait for quota reset |
| `INVALID_FILE_TYPE` | Unsupported MIME type | Use PDF, images, Word, Excel, or plain text |

---

## 7. Protocol Versioning

The AuthDocChain Protocol follows **Semantic Versioning** (`MAJOR.MINOR.PATCH`):

- `MAJOR` — Breaking changes to the certification or verification flow
- `MINOR` — New capabilities, backward-compatible
- `PATCH` — Clarifications, corrections, non-breaking updates

The current protocol version is embedded in every IPFS metadata bundle and QR payload (`"version": "1.0"`).

Changes to the protocol are governed by the **AuthDocChain Improvement Proposal (AIP)** process. See [AIP-0000](../aip/AIP-0000-purpose-and-process.md).

---

## 8. Governance

The AuthDocChain Protocol is currently maintained by **AuthDocChain Protocol Labs**.

As the protocol matures, governance will progressively open to:
- External AIP authorship and community review
- A formal AIP Editor role
- A protocol advisory council with institutional representation

---

## 9. References

- [AIP-0001 — Document Certification Standard](../specs/AIP-0001-document-certification.md)
- [AIP-0002 — Hash & Fingerprint Standard](../specs/AIP-0002-hash-standard.md)
- [AIP-0003 — Physical QR Stamp Format](../specs/AIP-0003-qr-stamp-format.md)
- [Polygon Documentation](https://docs.polygon.technology)
- [IPFS Documentation](https://docs.ipfs.tech)
- [SHA-256 — FIPS 180-4](https://csrc.nist.gov/publications/detail/fips/180/4/final)
- [HMAC — RFC 2104](https://tools.ietf.org/html/rfc2104)
