<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$email    = post('email');
$password = post('password');

if (!$email || !$password) {
    jsonResponse(['success' => false, 'message' => 'Email et mot de passe requis.']);
}

$pdo  = getPDO();
$stmt = $pdo->prepare('SELECT id, prenom, nom, email, password_hash, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['password_hash'])) {
    jsonResponse(['success' => false, 'message' => 'Identifiants incorrects.']);
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['prenom']  = $user['prenom'];
$_SESSION['nom']     = $user['nom'];
$_SESSION['role']    = $user['role'];

jsonResponse([
    'success' => true,
    'message' => 'Connexion réussie.',
    'user_id' => $user['id'],
    'prenom'  => $user['prenom'],
    'nom'     => $user['nom'],
    'role'    => $user['role'],
]);
