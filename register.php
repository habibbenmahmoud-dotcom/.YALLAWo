<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$prenom   = post('prenom');
$nom      = post('nom');
$email    = post('email');
$password = post('password');
$role     = post('role');
if (!$prenom || !$nom || !$email || !$password) {
    jsonResponse(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Adresse email invalide.']);
}

if (strlen($password) < 8) {
    jsonResponse(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères.']);
}

$allowedRoles = ['etudiant', 'entreprise'];
if (!in_array($role, $allowedRoles, true)) {
    $role = 'etudiant'; 
}

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    jsonResponse(['success' => false, 'message' => 'Cette adresse email est déjà utilisée.']);
}
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare(
    'INSERT INTO users (prenom, nom, email, password_hash, role) VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$prenom, $nom, $email, $hash, $role]);

jsonResponse([
    'success' => true,
    'message' => 'Compte créé avec succès.',
    'user_id' => (int) $pdo->lastInsertId(),
    'prenom'  => $prenom,
    'role'    => $role,
]);
