<?php
if (!isset($pageTitle)) $pageTitle = 'My Humidor Journal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> · My Humidor Journal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="index.php" class="logo">
                <span class="logo-icon">◆</span>
                My Humidor Journal
            </a>
            <nav class="main-nav">
                <a href="index.php" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="inventory.php" class="<?= ($active ?? '') === 'inventory' ? 'active' : '' ?>">Inventory</a>
                <a href="reviews.php" class="<?= ($active ?? '') === 'reviews' ? 'active' : '' ?>">Reviews</a>
                <a href="add_cigar.php" class="<?= ($active ?? '') === 'add_cigar' ? 'active' : '' ?>">+ Cigar</a>
                <a href="add_review.php" class="<?= ($active ?? '') === 'add_review' ? 'active' : '' ?>">+ Review</a>
            </nav>
        </div>
    </header>
    <main class="container">
