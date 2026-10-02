<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

$q = trim($_GET['q'] ?? '');
$strength = $_GET['strength'] ?? '';
$sort = $_GET['sort'] ?? 'updated';

$sql = "SELECT c.*, h.name AS humidor_name FROM cigars c LEFT JOIN humidors h ON h.id = c.humidor_id WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (c.brand LIKE ? OR c.name LIKE ? OR c.vitola LIKE ? OR c.notes LIKE ?)";
    $like = '%' . $q . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}
if ($strength !== '') {
    $sql .= " AND c.strength = ?";
    $params[] = $strength;
}

switch ($sort) {
    case 'name': $sql .= " ORDER BY c.brand, c.name"; break;
    case 'qty': $sql .= " ORDER BY c.quantity DESC"; break;
    case 'age': $sql .= " ORDER BY c.purchase_date ASC NULLS LAST"; break;
    case 'rating': // not applicable directly
    default: $sql .= " ORDER BY c.updated_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cigars = $stmt->fetchAll();

$pageTitle = 'Inventory';
$active = 'inventory';
require __DIR__ . '/includes/header.php';
?>

<div class="list-header">
    <div>
        <h1>Inventory</h1>
        <p class="subtitle" style="margin-bottom:0;">Cigars currently in your humidors</p>
    </div>
    <a href="add_cigar.php" class="btn btn-primary">+ Add Cigar</a>
</div>

<form method="get" class="card mb-2" style="padding:1rem;">
    <div class="form-row" style="align-items:end;">
        <div class="form-group" style="margin:0;">
            <label>Search</label>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Brand, name, vitola…">
        </div>
        <div class="form-group" style="margin:0;">
            <label>Strength</label>
            <select name="strength">
                <option value="">All</option>
                <option value="Mild" <?= $strength === 'Mild' ? 'selected' : '' ?>>Mild</option>
                <option value="Medium" <?= $strength === 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="Full" <?= $strength === 'Full' ? 'selected' : '' ?>>Full</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label>Sort</label>
            <select name="sort">
                <option value="updated" <?= $sort === 'updated' ? 'selected' : '' ?>>Recently updated</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option>
                <option value="qty" <?= $sort === 'qty' ? 'selected' : '' ?>>Quantity</option>
                <option value="age" <?= $sort === 'age' ? 'selected' : '' ?>>Oldest aging</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Filter</button>
    </div>
</form>

<?php if (empty($cigars)): ?>
    <div class="card empty-state">
        <p>No cigars match your filters (or inventory is empty).</p>
        <p class="mt-1"><a href="add_cigar.php">Add a cigar →</a></p>
    </div>
<?php else: ?>
    <div class="grid grid-2">
        <?php foreach ($cigars as $c): ?>
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
                        <?php if ($c['strength']): ?>
                            <span class="badge strength-<?= strtolower($c['strength']) ?>"><?= e($c['strength']) ?></span>
                        <?php endif; ?>
                        <?php
                        $days = daysAging($c['purchase_date']);
                        if ($days !== null): ?>
                            <span><?= $days ?>d aging</span>
                        <?php endif; ?>
                    </div>
                    <div class="actions">
                        <a href="view_cigar.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                        <a href="edit_cigar.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <a href="add_review.php?cigar_id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-primary">Review</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
