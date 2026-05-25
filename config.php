<?php
// ============================================================
// config.php — PDO MySQL connection
// Edit DB_USER and DB_PASS if your setup is different
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'yallawork');
define('DB_USER', 'root');      // change if needed
define('DB_PASS', '');          // change if needed
define('DB_CHARSET', 'utf8mb4');

// Folder where CVs are stored (relative to project root)
define('CV_UPLOAD_DIR', __DIR__ . '/../uploads/cv/');
define('CV_MAX_SIZE', 5 * 1024 * 1024); // 5 MB

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données.']);
            exit;
        }
    }
    return $pdo;
}

// Helper: send JSON response and stop
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Helper: get POST field safely
function post(string $key, string $default = ''): string {
    return trim($_POST[$key] ?? $default);
}
