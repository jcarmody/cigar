<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

$id = (int)($_POST['cigar_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM cigars WHERE id = ?");
$stmt->execute([$id]);
$cigar = $stmt->fetch();

if ($cigar && $cigar['quantity'] > 0) {
    $pdo->prepare("UPDATE cigars SET quantity = quantity - 1 WHERE id = ?")->execute([$id]);
}

header('Location: view_cigar.php?id=' . $id);
exit;
