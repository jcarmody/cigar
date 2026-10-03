<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) {
    header('Location: reviews.php');
    exit;
}

$pageTitle = trim(($r['brand'] ?? '') . ' ' . $r['name']) . ' Review';
$active = 'reviews';
require __DIR__ . '/includes/header.php';

function stars(?float $n): string {
    if ($n === null) return '—';
    $full = (int)round($n);
    return str_repeat('★', $full) . str_repeat('☆', 5 - $full) . ' ' . number_format($n, 1);
}
?>

<div class="list-header">
    <div>
        <h1><?= e(trim(($r['brand'] ?? '') . ' ' . $r['name'])) ?></h1>
        <p class="subtitle" style="margin-bottom:0;">
            <?= e($r['vitola'] ?? '') ?>
            <?php if ($r['smoke_date']): ?> · Smoked <?= e($r['smoke_date']) ?><?php endif; ?>
        </p>
    </div>
    <div class="actions">
        <a href="add_review.php?brand=<?= urlencode($r['brand'] ?? '') ?>&name=<?= urlencode($r['name']) ?>&vitola=<?= urlencode($r['vitola'] ?? '') ?>" class="btn btn-primary">Review Again</a>
        <a href="edit_review.php?id=<?= $id ?>" class="btn btn-secondary">Edit</a>
        <a href="reviews.php" class="btn btn-secondary">All Reviews</a>
    </div>
</div>

<div class="card">
    <div class="detail-header">
        <?php if ($r['photo']): ?>
            <img src="<?= e(UPLOAD_URL . $r['photo']) ?>" alt="" class="detail-photo">
        <?php else: ?>
            <div class="detail-photo" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:var(--text-muted);">◆</div>
        <?php endif; ?>
        <dl class="detail-meta" style="flex:1;">
            <dt>Overall</dt>
            <dd class="rating-stars"><?= stars($r['overall_rating'] !== null ? (float)$r['overall_rating'] : null) ?></dd>
            <?php if ($r['appearance'] !== null): ?><dt>Appearance</dt><dd><?= stars((float)$r['appearance']) ?></dd><?php endif; ?>
            <?php if ($r['construction'] !== null): ?><dt>Construction</dt><dd><?= stars((float)$r['construction']) ?></dd><?php endif; ?>
            <?php if ($r['burn'] !== null): ?><dt>Burn</dt><dd><?= stars((float)$r['burn']) ?></dd><?php endif; ?>
            <?php if ($r['draw'] !== null): ?><dt>Draw</dt><dd><?= stars((float)$r['draw']) ?></dd><?php endif; ?>
            <?php if ($r['flavor'] !== null): ?><dt>Flavor</dt><dd><?= stars((float)$r['flavor']) ?></dd><?php endif; ?>
            <?php if ($r['aroma'] !== null): ?><dt>Aroma</dt><dd><?= stars((float)$r['aroma']) ?></dd><?php endif; ?>
            <?php if ($r['pairings']): ?><dt>Pairings</dt><dd><?= e($r['pairings']) ?></dd><?php endif; ?>
            <?php if ($r['location']): ?><dt>Location</dt><dd><?= e($r['location']) ?></dd><?php endif; ?>
        </dl>
    </div>

    <div class="notes-section">
        <?php if ($r['cold_draw']): ?>
            <h3>Cold Draw</h3>
            <p><?= nl2br(e($r['cold_draw'])) ?></p>
        <?php endif; ?>
        <?php if ($r['first_third']): ?>
            <h3>First Third</h3>
            <p><?= nl2br(e($r['first_third'])) ?></p>
        <?php endif; ?>
        <?php if ($r['second_third']): ?>
            <h3>Second Third</h3>
            <p><?= nl2br(e($r['second_third'])) ?></p>
        <?php endif; ?>
        <?php if ($r['final_third']): ?>
            <h3>Final Third</h3>
            <p><?= nl2br(e($r['final_third'])) ?></p>
        <?php endif; ?>
        <?php if ($r['notes']): ?>
            <h3>Additional Notes</h3>
            <p><?= nl2br(e($r['notes'])) ?></p>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
