<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Response;

class AuthDocChainService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.authdocchain.com',
            'headers'  => [
                'x-api-key'    => config('services.authdocchain.api_key'),
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'timeout' => 90,
        ]);
    }

    /**
     * Certify a digital document.
     * Returns certification record + anchoring confirmation.
     */
    public function certifyDocument(
        string $filePath,
        string $docName,
        string $docType
    ): array {
        $content  = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/pdf';

        try {
            $response = $this->client->post('/api/v1/certify', [
                'json' => [
                    'name'         => $docName,
                    'hash'         => hash('sha256', $content),
                    'type'         => $docType,
                    'fileName'     => basename($filePath),
                    'fileData'     => base64_encode($content),
                    'fileMimeType' => $mimeType,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            $this->throw($e);
        }
    }

    /**
     * Certify a physical document and embed a tamper-proof QR seal.
     * Returns the certified PDF as base64 — stream it, never store it.
     *
     * @param string $qrPosition  top-left|top-center|top-right|bottom-left|bottom-center|bottom-right
     * @param int    $qrSize      30–80 pt (default 60)
     */
    public function certifyPhysical(
        string $filePath,
        string $docName,
        string $docType,
        string $qrPosition    = 'bottom-right',
        bool   $stampAllPages = false,
        int    $qrSize        = 60
    ): array {
        $content  = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/pdf';

        try {
            $response = $this->client->post('/api/v1/certify', [
                'json' => [
                    'name'          => $docName,
                    'hash'          => hash('sha256', $content),
                    'type'          => $docType,
                    'fileName'      => basename($filePath),
                    'fileData'      => base64_encode($content),
                    'fileMimeType'  => $mimeType,
                    'physical'      => true,
                    'qrPosition'    => $qrPosition,
                    'stampAllPages' => $stampAllPages,
                    'qrSize'        => max(30, min(80, $qrSize)),
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            $this->throw($e);
        }
    }

    /**
     * Stream the certified PDF directly to the browser.
     * Never persist it — AuthDocChain stores no files server-side.
     */
    public function streamCertifiedPdf(array $result, string $originalName): Response
    {
        $pdfBytes = base64_decode($result['document']['certifiedPdf'] ?? '');
        $filename = pathinfo($originalName, PATHINFO_FILENAME) . '_certified.pdf';

        return response($pdfBytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($pdfBytes),
        ]);
    }

    /**
     * Verify a document by its fingerprint.
     * Accepts: SHA-256 of original file, SHA-256 of certified PDF, or reference ID.
     */
    public function verifyByFingerprint(string $fingerprint): array
    {
        try {
            $response = $this->client->get('/api/v1/verify/' . urlencode($fingerprint));
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                return ['found' => false, 'valid' => false];
            }
            $this->throw($e);
        }
    }

    /**
     * Verify a document by uploading the file.
     * Computes the fingerprint server-side — the file never leaves your server.
     */
    public function verifyByFile(string $filePath): array
    {
        $content     = file_get_contents($filePath);
        $fingerprint = hash('sha256', $content);

        return $this->verifyByFingerprint($fingerprint);
    }

    /**
     * List all certified documents for this account (paginated).
     */
    public function listDocuments(int $limit = 50, int $offset = 0): array
    {
        try {
            $response = $this->client->get('/api/v1/documents', [
                'query' => ['limit' => $limit, 'offset' => $offset],
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            $this->throw($e);
        }
    }

    /**
     * Fetch account info and quota.
     */
    public function getAccount(): array
    {
        try {
            $response = $this->client->get('/api/v1/account');
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            $this->throw($e);
        }
    }

    // ── Internal ─────────────────────────────────────────────────────────────

    private function throw(ClientException $e): never
    {
        $body  = json_decode($e->getResponse()->getBody()->getContents(), true);
        $error = $body['error'] ?? 'API error';
        $code  = $body['code']  ?? null;
        throw new \RuntimeException($error . ($code ? " [{$code}]" : ''), $e->getCode());
    }
}
