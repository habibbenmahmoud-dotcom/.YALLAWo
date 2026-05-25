<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'yallawork');
define('DB_USER', 'root');      
define('DB_PASS', '');          
define('DB_CHARSET', 'utf8mb4');
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
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
function post(string $key, string $default = ''): string {
    return trim($_POST[$key] ?? $default);
}
