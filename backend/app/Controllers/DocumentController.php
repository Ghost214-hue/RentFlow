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

        if ($role === 'tenant') {
            $tenantId = Router::getAuthTenantId();
            $house = $db->fetchOne(
                "SELECT h.property_id FROM houses h JOIN tenants t ON h.tenant_id = t.id WHERE t.id = ? AND t.owner_id = ? AND h.status = 'occupied' LIMIT 1",
                [$tenantId, $ownerId]
            );
            if (!$house) {
                Router::jsonResponse(['documents' => []]);
                return;
            }
            $sql = "SELECT pd.*, p.name as property_name FROM property_documents pd LEFT JOIN properties p ON pd.property_id = p.id WHERE pd.owner_id = ? AND pd.is_active = 1 AND pd.property_id = ? ORDER BY pd.type, pd.created_at DESC";
            $documents = $db->fetchAll($sql, [$ownerId, $house['property_id']]);
            Router::jsonResponse(['documents' => $documents]);
            return;
        }

        if ($role === 'caretaker') {
            $caretakerId = Router::getAuthActorId();
            $caretaker = $db->fetchOne(
                "SELECT assigned_properties FROM caretakers WHERE id = ? AND owner_id = ?",
                [$caretakerId, $ownerId]
            );
            $propertyIds = [];
            if (!empty($caretaker['assigned_properties'])) {
                $propertyIds = array_map('intval', explode(',', $caretaker['assigned_properties']));
            }
            if (empty($propertyIds)) {
                Router::jsonResponse(['documents' => []]);
                return;
            }
            $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
            $sql = "SELECT pd.*, p.name as property_name FROM property_documents pd LEFT JOIN properties p ON pd.property_id = p.id WHERE pd.owner_id = ? AND pd.is_active = 1 AND pd.property_id IN ($placeholders) ORDER BY pd.type, pd.created_at DESC";
            $queryParams = array_merge([$ownerId], $propertyIds);
            $documents = $db->fetchAll($sql, $queryParams);
            Router::jsonResponse(['documents' => $documents]);
            return;
        }

        // Owner: see all their documents
        $sql = "SELECT pd.*, p.name as property_name FROM property_documents pd LEFT JOIN properties p ON pd.property_id = p.id WHERE pd.owner_id = ? ORDER BY pd.type, pd.created_at DESC";
        $documents = $db->fetchAll($sql, [$ownerId]);
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

        $document = $db->fetchOne(
            "SELECT pd.*, p.name as property_name FROM property_documents pd LEFT JOIN properties p ON pd.property_id = p.id WHERE pd.id = ? AND pd.owner_id = ?",
            [$documentId, $ownerId]
        );

        if (!$document) {
            Router::jsonResponse(['error' => 'Document not found'], 404);
        }

        if ($role === 'tenant') {
            $tenantId = Router::getAuthTenantId();
            $house = $db->fetchOne(
                "SELECT h.property_id FROM houses h JOIN tenants t ON h.tenant_id = t.id WHERE t.id = ? AND t.owner_id = ? AND h.status = 'occupied' LIMIT 1",
                [$tenantId, $ownerId]
            );
            if (!$house || $document['property_id'] != $house['property_id']) {
                Router::jsonResponse(['error' => 'Document not found'], 404);
            }
            if (!$document['is_active']) {
                Router::jsonResponse(['error' => 'Document not found'], 404);
            }
        } elseif ($role === 'caretaker') {
            $caretakerId = Router::getAuthActorId();
            $caretaker = $db->fetchOne(
                "SELECT assigned_properties FROM caretakers WHERE id = ? AND owner_id = ?",
                [$caretakerId, $ownerId]
            );
            $propertyIds = [];
            if (!empty($caretaker['assigned_properties'])) {
                $propertyIds = array_map('intval', explode(',', $caretaker['assigned_properties']));
            }
            if (!empty($propertyIds) && !in_array($document['property_id'], $propertyIds, true)) {
                Router::jsonResponse(['error' => 'Document not found'], 404);
            }
            if (!$document['is_active']) {
                Router::jsonResponse(['error' => 'Document not found'], 404);
            }
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
        // No JSON content type - this is a binary download
        header('Content-Type: text/html; charset=UTF-8');
        
        // Don't send JSON headers - allow binary PDF response
        $ownerId = Router::getAuthUserId();
        $role = Router::getAuthRole();
        $documentId = (int) ($params['id'] ?? 0);
        $db = Database::getInstance();

        $sql = "SELECT pd.*, p.name as property_name FROM property_documents pd LEFT JOIN properties p ON pd.property_id = p.id WHERE pd.id = ? AND pd.owner_id = ?";
        $queryParams = [$documentId, $ownerId];

        // Tenants and caretakers can only download active documents
        if ($role === 'tenant' || $role === 'caretaker') {
            $sql .= " AND pd.is_active = 1";
        }

        $document = $db->fetchOne($sql, $queryParams);

        if (!$document) {
            http_response_code(404);
            echo 'Document not found';
            exit;
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
        $propertyName = $document['property_name'] ?? 'All Properties';

        $paymentBlock = '';
        $propertyId = isset($document['property_id']) ? (int) $document['property_id'] : 0;
        $db = Database::getInstance();

        // Resolve property ID from document, tenant house, or caretaker assignment
        if (!$propertyId) {
            $role = Router::getAuthRole();
            if ($role === 'tenant') {
                $tenantId = Router::getAuthTenantId();
                $ownerId = Router::getAuthUserId();
                $house = $db->fetchOne(
                    "SELECT h.property_id FROM houses h JOIN tenants t ON h.tenant_id = t.id WHERE t.id = ? AND t.owner_id = ? AND h.status = 'occupied' LIMIT 1",
                    [$tenantId, $ownerId]
                );
                if ($house) $propertyId = (int) $house['property_id'];
            } elseif ($role === 'caretaker') {
                $ownerId = Router::getAuthUserId();
                $caretakerId = Router::getAuthActorId();
                $caretaker = $db->fetchOne(
                    "SELECT assigned_properties FROM caretakers WHERE id = ? AND owner_id = ?",
                    [$caretakerId, $ownerId]
                );
                if (!empty($caretaker['assigned_properties'])) {
                    $ids = array_map('intval', explode(',', $caretaker['assigned_properties']));
                    $propertyId = $ids[0];
                }
            }
        }

        if ($propertyId) {
            $prop = $db->fetchOne("SELECT payment_method_type, paybill_number, paybill_account, till_number, bank_name, bank_account, bank_branch, mobile_money_number FROM properties WHERE id = ?", [$propertyId]);
            if ($prop) {
                $parts = [];
                $method = $prop['payment_method_type'] ?? '';
                if ($method === 'paybill' && ($prop['paybill_number'] || $prop['paybill_account'])) {
                    $parts[] = 'Paybill: ' . trim(($prop['paybill_number'] ?? '') . ' ' . ($prop['paybill_account'] ?? ''));
                }
                if ($method === 'till' && ($prop['till_number'] ?? '')) {
                    $parts[] = 'Till Number: ' . $prop['till_number'];
                }
                if ($method === 'bank' && ($prop['bank_name'] || $prop['bank_account'] || $prop['bank_branch'])) {
                    $parts[] = 'Bank: ' . trim(($prop['bank_name'] ?? '') . ' ' . ($prop['bank_account'] ?? '') . ' ' . ($prop['bank_branch'] ?? ''));
                }
                if ($method === 'mobile_money' && ($prop['mobile_money_number'] ?? '')) {
                    $parts[] = 'Mobile Money: ' . $prop['mobile_money_number'];
                }
                if ($parts) {
                    $paymentBlock = '<div class="payment-section" style="margin-top:30px; padding:15px; background:#f8fafc; border-left:4px solid #2563eb; border-radius:6px;"><strong>Payment Instructions</strong><br>' . nl2br(htmlspecialchars(implode("\n", $parts))) . '</div>';
                }
            }
        }

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        @page { margin: 2cm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 40px; color: #333; }
        .page { background: white; }
        .header { text-align: center; border-bottom: 3px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #2563eb; margin: 0 0 15px 0; font-size: 28px; }
        .property-name { font-size: 16px; font-weight: 600; color: #1e40af; margin-bottom: 10px; }
        .meta-info { color: #666; font-size: 13px; margin-top: 15px; }
        .meta-item { display: inline-block; margin: 0 10px; padding: 4px 12px; background: #f1f5f9; border-radius: 4px; }
        .content { line-height: 1.8; font-size: 14px; margin-top: 30px; white-space: pre-wrap; }
        .footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="property-name">{$propertyName}</div>
            <h1>{$title}</h1>
            <div class="meta-info">
                <span class="meta-item">{$type}</span>
                <span class="meta-item">Version {$version}</span>
                <span class="meta-item">{$publishedDate}</span>
            </div>
        </div>
        
        <div class="content">
            {$content}
        </div>
        
        {$paymentBlock}
        
        <div class="footer">
            <p>RentFlow Property Management System</p>
            <p>Generated on {date('F j, Y')}</p>
        </div>
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
            error_log('PDF generation error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to generate PDF: ' . $e->getMessage()]);
            http_response_code(500);
            exit;
        }
    }
}