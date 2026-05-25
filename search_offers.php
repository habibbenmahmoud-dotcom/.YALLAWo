<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$keyword  = trim($_GET['keyword']  ?? '');
$location = trim($_GET['location'] ?? '');

$pdo    = getPDO();
$params = [];
$where  = [];

if ($keyword !== '') {
    $where[]  = '(o.titre LIKE ? OR o.entreprise LIKE ? OR o.description LIKE ?)';
    $like     = '%' . $keyword . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($location !== '') {
    $where[]  = 'o.ville LIKE ?';
    $params[] = '%' . $location . '%';
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT
        o.id,
        o.titre,
        o.entreprise,
        o.type_contrat,
        o.ville,
        o.salaire,
        o.logo,
        o.description,
        o.created_at,
        DATEDIFF(NOW(), o.created_at) AS jours_passes
    FROM offers o
    $whereSql
    ORDER BY o.created_at DESC
    LIMIT 50
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offers = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'count'   => count($offers),
    'offers'  => $offers,
]);
