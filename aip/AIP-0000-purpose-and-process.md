# AIP-0000: Purpose and Process

| Field | Value |
|-------|-------|
| **AIP** | 0000 |
| **Title** | AuthDocChain Improvement Proposal — Purpose and Process |
| **Author** | AuthDocChain Protocol Labs |
| **Status** | Active |
| **Type** | Meta |
| **Created** | 2025 |

---

## What is an AIP?

An **AuthDocChain Improvement Proposal (AIP)** is a design document providing information to the AuthDocChain community, or describing a new feature, standard, or process for the AuthDocChain Protocol.

The AIP process is modeled after established standards processes:
- [Ethereum Improvement Proposals (EIPs)](https://eips.ethereum.org)
- [IETF Requests for Comments (RFCs)](https://www.rfc-editor.org)
- [W3C Technical Reports](https://www.w3.org/TR/)

AIPs are the primary mechanism for proposing changes to the AuthDocChain protocol, for collecting community input on a technical issue, and for documenting design decisions.

---

## AIP Types

| Type | Description |
|------|-------------|
| **Standards Track** | Changes to the AuthDocChain protocol, certification flow, verification flow, data formats, or API contracts. Requires community review. |
| **Informational** | Describes a design issue, provides guidelines, or documents existing behavior. Does not propose a change. |
| **Meta** | Describes a process around AuthDocChain development, including this document itself. |

---

## AIP Status Flow

```
[ Draft ] → [ Review ] → [ Last Call ] → [ Final ]
                │                              │
                └──────────────────────────────┘
                         [ Withdrawn ]
                         [ Stagnant ]
```

| Status | Description |
|--------|-------------|
| `Draft` | AIP is being written. Not yet ready for review. |
| `Review` | AIP is complete and open for community feedback. |
| `Last Call` | Final review period before acceptance. Duration: 14 days minimum. |
| `Final` | AIP has been accepted and is part of the protocol. |
| `Withdrawn` | Author has withdrawn the AIP. |
| `Stagnant` | No activity for 6 months. May be resurrected by the author. |

---

## AIP Numbering

- `AIP-0000` to `AIP-0099` — Meta and process AIPs
- `AIP-0100` to `AIP-0999` — Core protocol specifications
- `AIP-1000+` — Extensions and optional standards

---

## How to Submit an AIP

1. **Fork** this repository.
2. **Copy** `aip/AIP-template.md` to `specs/AIP-XXXX-short-title.md`.
3. **Fill in** all required fields.
4. **Open a Pull Request** with the title `AIP-XXXX: Short Title`.
5. The AIP Editor will assign a number and move it to `Draft`.
6. Discuss and iterate via PR comments.
7. Once consensus is reached, the AIP Editor moves it to `Review`, then `Last Call`, then `Final`.

---

## AIP Editor

The current AIP Editor is **AuthDocChain Protocol Labs** (`contact@authdocchain.com`).

As the community grows, additional editors will be appointed from active contributors.

---

## Reference

- [AIP Template](./AIP-template.md)
- [Active AIPs](../specs/)
