<?php
/**
 * Form Builder API - Save/Load/List form templates
 */

header('Content-Type: application/json');

// Directory to store form templates
$storageDir = __DIR__ . '/templates';

// Create storage directory if it doesn't exist
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        // List all saved templates
        $files = glob($storageDir . '/*.json');
        $templates = [];

        foreach ($files as $file) {
            $content = json_decode(file_get_contents($file), true);
            $templates[] = [
                'id' => basename($file, '.json'),
                'title' => $content['title'] ?? 'Untitled',
                'modified' => date('Y-m-d H:i:s', filemtime($file)),
                'filename' => basename($file)
            ];
        }

        // Sort by modified date, newest first
        usort($templates, function($a, $b) {
            return strtotime($b['modified']) - strtotime($a['modified']);
        });

        echo json_encode(['success' => true, 'templates' => $templates]);
        break;

    case 'save':
        // Save a template
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['data'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No data provided']);
            exit;
        }

        $data = $input['data'];
        $title = $data['title'] ?? 'Untitled';

        // Generate a safe filename from title or use provided id
        $id = $input['id'] ?? null;
        if (!$id) {
            $id = preg_replace('/[^a-z0-9]+/i', '_', strtolower($title));
            $id = trim($id, '_');
            if (empty($id)) {
                $id = 'form_' . time();
            }
        }

        // Add timestamp to data
        $data['savedAt'] = date('Y-m-d H:i:s');

        $filepath = $storageDir . '/' . $id . '.json';
        $isNew = !file_exists($filepath);

        if (file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT))) {
            echo json_encode([
                'success' => true,
                'id' => $id,
                'isNew' => $isNew,
                'message' => $isNew ? 'Template created' : 'Template updated'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save template']);
        }
        break;

    case 'load':
        // Load a specific template
        $id = $_GET['id'] ?? '';

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No template ID provided']);
            exit;
        }

        // Sanitize ID to prevent directory traversal
        $id = basename($id);
        $filepath = $storageDir . '/' . $id . '.json';

        if (!file_exists($filepath)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Template not found']);
            exit;
        }

        $content = file_get_contents($filepath);
        $data = json_decode($content, true);

        echo json_encode(['success' => true, 'data' => $data, 'id' => $id]);
        break;

    case 'delete':
        // Delete a template
        $id = $_GET['id'] ?? '';

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No template ID provided']);
            exit;
        }

        // Sanitize ID to prevent directory traversal
        $id = basename($id);
        $filepath = $storageDir . '/' . $id . '.json';

        if (!file_exists($filepath)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Template not found']);
            exit;
        }

        if (unlink($filepath)) {
            echo json_encode(['success' => true, 'message' => 'Template deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete template']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
