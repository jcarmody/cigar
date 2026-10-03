<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT c.*, h.name AS humidor_name
    FROM cigars c
    LEFT JOIN humidors h ON h.id = c.humidor_id
    WHERE c.id = ?
");
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) {
    header('Location: inventory.php');
    exit;
}

$reviews = $pdo->prepare("SELECT * FROM reviews WHERE cigar_id = ? ORDER BY smoke_date DESC");
$reviews->execute([$id]);
$linkedReviews = $reviews->fetchAll();

$pageTitle = trim(($c['brand'] ?? '') . ' ' . $c['name']);
$active = 'inventory';
require __DIR__ . '/includes/header.php';
?>

<div class="list-header">
    <div>
        <h1><?= e($pageTitle) ?></h1>
        <p class="subtitle" style="margin-bottom:0;"><?= e($c['vitola'] ?? '') ?> · <?= e($c['humidor_name'] ?? '') ?></p>
    </div>
    <div class="actions">
        <a href="add_cigar.php?brand=<?= urlencode($c['brand'] ?? '') ?>&name=<?= urlencode($c['name']) ?>&vitola=<?= urlencode($c['vitola'] ?? '') ?>&wrapper=<?= urlencode($c['wrapper'] ?? '') ?>&origin=<?= urlencode($c['origin'] ?? '') ?>&strength=<?= urlencode($c['strength'] ?? '') ?>" class="btn btn-primary">Add Again</a>
        <a href="edit_cigar.php?id=<?= $id ?>" class="btn btn-secondary">Edit</a>
        <a href="add_review.php?cigar_id=<?= $id ?>" class="btn btn-primary">Log Review</a>
    </div>
</div>

<div class="card">
    <div class="detail-header">
        <?php if ($c['photo']): ?>
            <img src="<?= e(UPLOAD_URL . $c['photo']) ?>" alt="" class="detail-photo">
        <?php else: ?>
            <div class="detail-photo" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:var(--text-muted);">◆</div>
        <?php endif; ?>
        <dl class="detail-meta" style="flex:1;">
            <dt>Quantity</dt>
            <dd><span class="badge qty">×<?= (int)$c['quantity'] ?></span></dd>
            <?php if ($c['strength']): ?>
                <dt>Strength</dt>
                <dd><span class="badge strength-<?= strtolower($c['strength']) ?>"><?= e($c['strength']) ?></span></dd>
            <?php endif; ?>
            <?php if ($c['wrapper']): ?>
                <dt>Wrapper</dt>
                <dd><?= e($c['wrapper']) ?></dd>
            <?php endif; ?>
            <?php if ($c['origin']): ?>
                <dt>Origin</dt>
                <dd><?= e($c['origin']) ?></dd>
            <?php endif; ?>
            <?php if ($c['purchase_date']): ?>
                <dt>Added / Purchased</dt>
                <dd><?= e($c['purchase_date']) ?>
                    <?php $days = daysAging($c['purchase_date']); if ($days !== null): ?>
                        · <strong><?= $days ?> days aging</strong>
                    <?php endif; ?>
                </dd>
            <?php endif; ?>
            <?php if ($c['price'] !== null): ?>
                <dt>Price paid</dt>
                <dd>$<?= number_format((float)$c['price'], 2) ?></dd>
            <?php endif; ?>
        </dl>
    </div>

    <?php if ($c['notes']): ?>
        <div class="notes-section">
            <h3>Notes</h3>
            <p><?= nl2br(e($c['notes'])) ?></p>
        </div>
    <?php endif; ?>
</div>

<?php if ($linkedReviews): ?>
    <h2 class="mt-2" style="font-size:1.15rem;margin-bottom:0.75rem;">Linked Reviews</h2>
    <?php foreach ($linkedReviews as $r): ?>
        <div class="card item-card">
            <div class="item-body">
                <div class="item-title">
                    <a href="view_review.php?id=<?= (int)$r['id'] ?>" style="color:inherit;text-decoration:none;">
                        Review · <?= e($r['smoke_date'] ?? 'no date') ?>
                    </a>
                </div>
                <div class="item-meta">
                    <?php if ($r['overall_rating'] !== null): ?>
                        <span class="rating-stars"><?= str_repeat('★', (int)round($r['overall_rating'])) ?><?= str_repeat('☆', 5 - (int)round($r['overall_rating'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
