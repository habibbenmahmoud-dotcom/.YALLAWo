<?php
// ============================================================
// send_message.php — Save a message between two users
// POST: receiver_id, body
// Requires an active session (logged-in user as sender)
// ============================================================

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

// Start session and check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'Vous devez être connecté pour envoyer un message.'], 401);
}

$sender_id   = (int) $_SESSION['user_id'];
$receiver_id = (int) post('receiver_id');
$body        = post('body');

if (!$receiver_id || !$body) {
    jsonResponse(['success' => false, 'message' => 'Destinataire et message sont obligatoires.']);
}

if ($sender_id === $receiver_id) {
    jsonResponse(['success' => false, 'message' => 'Vous ne pouvez pas vous envoyer un message à vous-même.']);
}

$pdo = getPDO();

// Verify receiver exists
$check = $pdo->prepare('SELECT id FROM users WHERE id = ?');
$check->execute([$receiver_id]);
if (!$check->fetch()) {
    jsonResponse(['success' => false, 'message' => 'Destinataire introuvable.']);
}

$stmt = $pdo->prepare(
    'INSERT INTO messages (sender_id, receiver_id, body, is_read) VALUES (?, ?, ?, 0)'
);
$stmt->execute([$sender_id, $receiver_id, $body]);

jsonResponse([
    'success'    => true,
    'message'    => 'Message envoyé.',
    'message_id' => (int) $pdo->lastInsertId(),
]);
