<?php
/**
 * Communication Controller - Owner-scoped
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class CommunicationController
{
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $db = Database::getInstance();

        $type = $_GET['type'] ?? '';

        $sql = "SELECT * FROM communications WHERE owner_id = ?";
        $params = [$ownerId];

        if ($type && in_array($type, ['whatsapp', 'email', 'sms'])) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY created_at DESC";

        $communications = $db->fetchAll($sql, $params);
        Router::jsonResponse(['communications' => $communications]);
    }

    public function store(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $data = Router::getRequestBody();
        $db = Database::getInstance();

        if (empty($data['recipient']) || empty($data['message'])) {
            Router::jsonResponse(['error' => 'Recipient and message are required'], 400);
        }

        $commId = $db->insert('communications', [
            'owner_id'  => $ownerId,
            'type'      => $data['type'] ?? 'email',
            'recipient' => $data['recipient'],
            'phone'     => $data['phone'] ?? null,
            'email'     => $data['email'] ?? null,
            'subject'   => $data['subject'] ?? null,
            'message'   => $data['message'],
            'date'      => $data['date'] ?? date('Y-m-d'),
            'status'    => 'sent',
            'template'  => $data['template'] ?? null,
        ]);

        $communication = $db->fetchOne("SELECT * FROM communications WHERE id = ?", [$commId]);
        Router::jsonResponse(['message' => 'Message sent', 'communication' => $communication], 201);
    }

    public function templates(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $db = Database::getInstance();

        $templates = $db->fetchAll(
            "SELECT * FROM templates WHERE owner_id = ? ORDER BY name ASC",
            [$ownerId]
        );
        Router::jsonResponse(['templates' => $templates]);
    }
}