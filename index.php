<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

// Stats
$totalCigars = (int)$pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM cigars")->fetchColumn();
$uniqueCigars = (int)$pdo->query("SELECT COUNT(*) FROM cigars")->fetchColumn();
$totalReviews = (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$avgRating = $pdo->query("SELECT ROUND(AVG(overall_rating), 1) FROM reviews WHERE overall_rating IS NOT NULL")->fetchColumn();

// Recent inventory
$recentCigars = $pdo->query("
    SELECT c.*, h.name AS humidor_name
    FROM cigars c
    LEFT JOIN humidors h ON h.id = c.humidor_id
    ORDER BY c.updated_at DESC
    LIMIT 5
")->fetchAll();

// Recent reviews
$recentReviews = $pdo->query("
    SELECT * FROM reviews
    ORDER BY smoke_date DESC, created_at DESC
    LIMIT 5
")->fetchAll();

$pageTitle = 'Dashboard';
$active = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<h1>Dashboard</h1>
<p class="subtitle">Your personal cigar collection and tasting journal</p>

<div class="grid grid-stats mb-2">
    <div class="card stat-card">
        <div class="value"><?= $totalCigars ?></div>
        <div class="label">Sticks in stock</div>
    </div>
    <div class="card stat-card">
        <div class="value"><?= $uniqueCigars ?></div>
        <div class="label">Unique entries</div>
    </div>
    <div class="card stat-card">
        <div class="value"><?= $totalReviews ?></div>
        <div class="label">Reviews logged</div>
    </div>
    <div class="card stat-card">
        <div class="value"><?= $avgRating !== null ? e($avgRating) : '—' ?></div>
        <div class="label">Avg rating</div>
    </div>
</div>

<div class="grid grid-2">
    <div>
        <div class="list-header">
            <h2 style="font-size:1.15rem;">Recent Inventory</h2>
            <a href="inventory.php" class="btn btn-sm btn-secondary">View all</a>
        </div>
        <?php if (empty($recentCigars)): ?>
            <div class="card empty-state">
                <p>No cigars yet.</p>
                <p class="mt-1"><a href="add_cigar.php">Add your first cigar →</a></p>
            </div>
        <?php else: ?>
            <?php foreach ($recentCigars as $c): ?>
                <div class="card item-card">
                    <?php if ($c['photo']): ?>
                        <img src="<?= e(UPLOAD_URL . $c['photo']) ?>" alt="" class="item-photo">
                    <?php else: ?>
                        <div class="item-photo placeholder">◆</div>
                    <?php endif; ?>
                    <div class="item-body">
                        <div class="item-title">
                            <a href="view_cigar.php?id=<?= (int)$c['id'] ?>" style="color:inherit;text-decoration:none;">
                                <?= e(trim(($c['brand'] ?? '') . ' ' . $c['name'])) ?>
                            </a>
                        </div>
                        <div class="item-meta">
                            <?php if ($c['vitola']): ?><span><?= e($c['vitola']) ?></span><?php endif; ?>
                            <span class="badge qty">×<?= (int)$c['quantity'] ?></span>
                            <?php
                            $days = daysAging($c['purchase_date']);
                            if ($days !== null): ?>
                                <span><?= $days ?> days aging</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div>
        <div class="list-header">
            <h2 style="font-size:1.15rem;">Recent Reviews</h2>
            <a href="reviews.php" class="btn btn-sm btn-secondary">View all</a>
        </div>
        <?php if (empty($recentReviews)): ?>
            <div class="card empty-state">
                <p>No reviews yet.</p>
                <p class="mt-1"><a href="add_review.php">Log a smoke →</a></p>
            </div>
        <?php else: ?>
            <?php foreach ($recentReviews as $r): ?>
                <div class="card item-card">
                    <?php if ($r['photo']): ?>
                        <img src="<?= e(UPLOAD_URL . $r['photo']) ?>" alt="" class="item-photo">
                    <?php else: ?>
                        <div class="item-photo placeholder">◆</div>
                    <?php endif; ?>
                    <div class="item-body">
                        <div class="item-title">
                            <a href="view_review.php?id=<?= (int)$r['id'] ?>" style="color:inherit;text-decoration:none;">
                                <?= e(trim(($r['brand'] ?? '') . ' ' . $r['name'])) ?>
                            </a>
                        </div>
                        <div class="item-meta">
                            <?php if ($r['smoke_date']): ?><span><?= e($r['smoke_date']) ?></span><?php endif; ?>
                            <?php if ($r['overall_rating'] !== null): ?>
                                <span class="rating-stars"><?= str_repeat('★', (int)round($r['overall_rating'])) ?><?= str_repeat('☆', 5 - (int)round($r['overall_rating'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
