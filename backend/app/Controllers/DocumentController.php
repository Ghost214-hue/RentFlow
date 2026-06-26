<?php
/**
 * Document Controller - Property rules, regulations, policies
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class DocumentController
{
    /**
     * GET /api/documents
     * List all documents (filtered by role)
     */
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $db = Database::getInstance();

        $sql = "SELECT * FROM property_documents WHERE owner_id = ?";
        $params = [$ownerId];

        // Tenants and caretakers only see active documents
        if ($role === 'tenant' || $role === 'caretaker') {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY type, created_at DESC";
        $documents = $db->fetchAll($sql, $params);

        Router::jsonResponse(['documents' => $documents]);
    }

    /**
     * GET /api/documents/{id}
     */
    public function show(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $documentId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $sql = "SELECT * FROM property_documents WHERE id = ? AND owner_id = ?";
        $queryParams = [$documentId, $ownerId];

        // Tenants and caretakers can only view active documents
        if ($role === 'tenant' || $role === 'caretaker') {
            $sql .= " AND is_active = 1";
        }

        $document = $db->fetchOne($sql, $queryParams);

        if (!$document) {
            Router::jsonResponse(['error' => 'Document not found'], 404);
        }

        Router::jsonResponse(['document' => $document]);
    }

    /**
     * POST /api/documents
     * Create new document (owner only)
     */
    public function store(array $params = []): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $data['title'] = trim((string) ($data['title'] ?? ''));
        $data['content'] = trim((string) ($data['content'] ?? ''));

        if ($data['title'] === '') {
            Router::jsonResponse(['error' => 'Document title is required'], 400);
        }
        if ($data['content'] === '') {
            Router::jsonResponse(['error' => 'Document content is required'], 400);
        }

        $documentId = $db->insert('property_documents', [
            'owner_id' => $ownerId,
            'property_id' => !empty($data['property_id']) ? (int) $data['property_id'] : null,
            'title' => $data['title'],
            'content' => $data['content'],
            'type' => $data['type'] ?? 'rules',
            'version' => $data['version'] ?? '1.0',
            'is_active' => $data['is_active'] ?? true,
            'published_at' => $data['is_active'] ? date('Y-m-d H:i:s') : null,
        ]);

        $document = $db->fetchOne("SELECT * FROM property_documents WHERE id = ?", [$documentId]);
        Router::jsonResponse(['message' => 'Document created', 'document' => $document], 201);
    }

    /**
     * PUT /api/documents/{id}
     * Update document (owner only)
     */
    public function update(array $params): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $documentId = (int) ($params['id'] ?? 0);
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        $existing = $db->fetchOne(
            "SELECT id FROM property_documents WHERE id = ? AND owner_id = ?",
            [$documentId, $ownerId]
        );
        if (!$existing) {
            Router::jsonResponse(['error' => 'Document not found'], 404);
        }

        $updateData = [];
        foreach (['title', 'content', 'type', 'version', 'property_id'] as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        // Handle is_active status change
        if (isset($data['is_active'])) {
            $updateData['is_active'] = (bool) $data['is_active'];
            // Set published_at when first activated
            if ($updateData['is_active']) {
                $current = $db->fetchOne("SELECT published_at FROM property_documents WHERE id = ?", [$documentId]);
                if (!$current['published_at']) {
                    $updateData['published_at'] = date('Y-m-d H:i:s');
                }
            }
        }

        if (!empty($updateData)) {
            $db->update('property_documents', $updateData, 'id = ?', [$documentId]);
        }

        $document = $db->fetchOne("SELECT * FROM property_documents WHERE id = ?", [$documentId]);
        Router::jsonResponse(['message' => 'Document updated', 'document' => $document]);
    }

    /**
     * DELETE /api/documents/{id}
     * Delete document (owner only)
     */
    public function destroy(array $params): void
    {
        Router::requireOwner();
        $ownerId = Router::getAuthUserId();
        $documentId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $existing = $db->fetchOne(
            "SELECT id FROM property_documents WHERE id = ? AND owner_id = ?",
            [$documentId, $ownerId]
        );
        if (!$existing) {
            Router::jsonResponse(['error' => 'Document not found'], 404);
        }

        $db->delete('property_documents', 'id = ?', [$documentId]);
        Router::jsonResponse(['message' => 'Document deleted']);
    }

    /**
     * GET /api/documents/{id}/pdf
     * Download document as PDF
     */
    public function downloadPdf(array $params): void
    {
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $documentId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $sql = "SELECT * FROM property_documents WHERE id = ? AND owner_id = ?";
        $queryParams = [$documentId, $ownerId];

        // Tenants and caretakers can only download active documents
        if ($role === 'tenant' || $role === 'caretaker') {
            $sql .= " AND is_active = 1";
        }

        $document = $db->fetchOne($sql, $queryParams);

        if (!$document) {
            Router::jsonResponse(['error' => 'Document not found'], 404);
        }

        // Generate PDF (simple HTML-to-PDF conversion)
        $this->generatePdf($document);
    }

    /**
     * Generate and download PDF using Dompdf
     */
    private function generatePdf(array $document): void
    {
        $title = $document['title'];
        $content = nl2br(htmlspecialchars($document['content']));
        $type = ucfirst($document['type']);
        $version = $document['version'];
        $publishedDate = $document['published_at'] ? date('F j, Y', strtotime($document['published_at'])) : 'N/A';

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 40px; color: #333; }
        .header { text-align: center; border-bottom: 3px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #2563eb; margin: 0; font-size: 28px; }
        .header .meta { color: #666; margin-top: 10px; font-size: 14px; }
        .content { line-height: 1.8; font-size: 14px; white-space: pre-wrap; }
        .footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 12px; }
        .badge { display: inline-block; padding: 5px 15px; background: #2563eb; color: white; border-radius: 20px; font-size: 12px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{$title}</h1>
        <div class="meta">
            <span class="badge">{$type}</span>
            <span>Version {$version}</span> | 
            <span>Published: {$publishedDate}</span>
        </div>
    </div>
    
    <div class="content">
        {$content}
    </div>
    
    <div class="footer">
        <p>This document is confidential and intended for authorized recipients only.</p>
        <p>Generated by RentFlow Property Management System</p>
    </div>
</body>
</html>
HTML;

        // Use Dompdf to generate PDF
        try {
            require_once __DIR__ . '/../../../vendor/autoload.php';
            
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.pdf';
            $dompdf->stream($filename, ['Attachment' => true]);
            exit;
        } catch (\Exception $e) {
            // Fallback to HTML if Dompdf fails
            error_log('PDF generation error: ' . $e->getMessage());
            header('Content-Type: text/html');
            echo $html;
            exit;
        }
    }
}