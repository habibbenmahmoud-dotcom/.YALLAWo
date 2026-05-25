<?php
// ============================================================
// apply.php — Submit a job application + optional CV upload
// POST (multipart/form-data): prenom, nom, email, lettre,
//                              offre_id, cv (file, optional)
// ============================================================

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$prenom   = post('prenom');
$nom      = post('nom');
$email    = post('email');
$lettre   = post('lettre');
$offre_id = (int) post('offre_id');

// --- Validation ---
if (!$prenom || !$nom || !$email) {
    jsonResponse(['success' => false, 'message' => 'Prénom, nom et email sont obligatoires.']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Adresse email invalide.']);
}

// --- CV Upload ---
$cv_path = null;

if (!empty($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['cv'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(['success' => false, 'message' => 'Erreur lors du téléchargement du CV (code ' . $file['error'] . ').']);
    }

    // Check file size (max 5 MB)
    if ($file['size'] > CV_MAX_SIZE) {
        jsonResponse(['success' => false, 'message' => 'Le CV ne doit pas dépasser 5 Mo.']);
    }

    // Check MIME type — only PDF allowed
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if ($mimeType !== 'application/pdf') {
        jsonResponse(['success' => false, 'message' => 'Seuls les fichiers PDF sont acceptés pour le CV.']);
    }

    // Create upload directory if it doesn't exist
    if (!is_dir(CV_UPLOAD_DIR)) {
        mkdir(CV_UPLOAD_DIR, 0755, true);
    }

    // Build a unique filename to avoid collisions
    $ext      = 'pdf';
    $filename = sprintf(
        '%s_%s_%s.%s',
        preg_replace('/[^a-z0-9]/', '', strtolower($prenom . '_' . $nom)),
        date('Ymd_His'),
        bin2hex(random_bytes(4)),
        $ext
    );

    $destination = CV_UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        jsonResponse(['success' => false, 'message' => 'Impossible de sauvegarder le CV. Vérifiez les permissions du dossier uploads/cv/.']);
    }

    // Store relative path in DB (relative to project root)
    $cv_path = 'uploads/cv/' . $filename;
}

// --- Resolve user_id from session (optional) ---
$user_id = null;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
}

// --- Insert application ---
$pdo = getPDO();

// Verify offer exists (if an id was given)
if ($offre_id > 0) {
    $check = $pdo->prepare('SELECT id FROM offers WHERE id = ?');
    $check->execute([$offre_id]);
    if (!$check->fetch()) {
        $offre_id = null; // offer not found, save application without FK
    }
}

$stmt = $pdo->prepare(
    'INSERT INTO applications (offer_id, user_id, prenom, nom, email, lettre, cv_path, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, "envoyee")'
);
$stmt->execute([
    $offre_id ?: null,
    $user_id,
    $prenom,
    $nom,
    $email,
    $lettre ?: null,
    $cv_path,
]);

jsonResponse([
    'success'        => true,
    'message'        => 'Candidature envoyée avec succès.',
    'application_id' => (int) $pdo->lastInsertId(),
    'cv_uploaded'    => $cv_path !== null,
]);
