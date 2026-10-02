<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

$q = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'date';

$sql = "SELECT * FROM reviews WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (brand LIKE ? OR name LIKE ? OR notes LIKE ? OR cold_draw LIKE ? OR first_third LIKE ? OR second_third LIKE ? OR final_third LIKE ?)";
    $like = '%' . $q . '%';
    $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like]);
}

switch ($sort) {
    case 'rating': $sql .= " ORDER BY overall_rating DESC NULLS LAST"; break;
    case 'name': $sql .= " ORDER BY brand, name"; break;
    default: $sql .= " ORDER BY smoke_date DESC, created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

$pageTitle = 'Reviews';
$active = 'reviews';
require __DIR__ . '/includes/header.php';
?>

<div class="list-header">
    <div>
        <h1>Reviews</h1>
        <p class="subtitle" style="margin-bottom:0;">Your tasting journal</p>
    </div>
    <a href="add_review.php" class="btn btn-primary">+ Log Review</a>
</div>

<form method="get" class="card mb-2" style="padding:1rem;">
    <div class="form-row" style="align-items:end;">
        <div class="form-group" style="margin:0;flex:2;">
            <label>Search notes & names</label>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Flavor notes, brand…">
        </div>
        <div class="form-group" style="margin:0;">
            <label>Sort</label>
            <select name="sort">
                <option value="date" <?= $sort === 'date' ? 'selected' : '' ?>>Smoke date</option>
                <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest rated</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Filter</button>
    </div>
</form>

<?php if (empty($reviews)): ?>
    <div class="card empty-state">
        <p>No reviews yet.</p>
        <p class="mt-1"><a href="add_review.php">Log your first smoke →</a></p>
    </div>
<?php else: ?>
    <div class="grid grid-2">
        <?php foreach ($reviews as $r): ?>
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
                        <?php if ($r['vitola']): ?><span><?= e($r['vitola']) ?></span><?php endif; ?>
                        <?php if ($r['overall_rating'] !== null): ?>
                            <span class="rating-stars"><?= str_repeat('★', (int)round($r['overall_rating'])) ?><?= str_repeat('☆', 5 - (int)round($r['overall_rating'])) ?></span>
                            <span><?= e(number_format((float)$r['overall_rating'], 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="actions">
                        <a href="view_review.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                        <a href="edit_review.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
