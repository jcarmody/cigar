<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM cigars WHERE id = ?");
$stmt->execute([$id]);
$cigar = $stmt->fetch();
if (!$cigar) {
    header('Location: inventory.php');
    exit;
}

$humidors = $pdo->query("SELECT id, name FROM humidors ORDER BY name")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Optionally delete photo file
        if ($cigar['photo'] && file_exists(UPLOAD_DIR . $cigar['photo'])) {
            @unlink(UPLOAD_DIR . $cigar['photo']);
        }
        $pdo->prepare("DELETE FROM cigars WHERE id = ?")->execute([$id]);
        header('Location: inventory.php?deleted=1');
        exit;
    }

    $brand = trim($_POST['brand'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $vitola = trim($_POST['vitola'] ?? '');
    $wrapper = trim($_POST['wrapper'] ?? '');
    $origin = trim($_POST['origin'] ?? '');
    $strength = $_POST['strength'] ?? '';
    $quantity = max(0, (int)($_POST['quantity'] ?? 1));
    $purchase_date = $_POST['purchase_date'] ?? null ?: null;
    $price = $_POST['price'] !== '' ? (float)$_POST['price'] : null;
    $notes = trim($_POST['notes'] ?? '');
    $humidor_id = (int)($_POST['humidor_id'] ?? 1);

    if ($name === '') {
        $error = 'Cigar name is required.';
    } else {
        $photo = handleUpload('photo') ?? $cigar['photo'];
        $stmt = $pdo->prepare("
            UPDATE cigars SET
                humidor_id=?, brand=?, name=?, vitola=?, wrapper=?, origin=?, strength=?,
                quantity=?, purchase_date=?, price=?, notes=?, photo=?, updated_at=datetime('now')
            WHERE id=?
        ");
        $stmt->execute([$humidor_id, $brand ?: null, $name, $vitola ?: null, $wrapper ?: null, $origin ?: null, $strength ?: null, $quantity, $purchase_date, $price, $notes ?: null, $photo, $id]);
        header('Location: view_cigar.php?id=' . $id);
        exit;
    }
}

$pageTitle = 'Edit Cigar';
$active = 'inventory';
require __DIR__ . '/includes/header.php';
?>

<h1>Edit Cigar</h1>
<p class="subtitle"><?= e(trim(($cigar['brand'] ?? '') . ' ' . $cigar['name'])) ?></p>

<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card form-card">
    <div class="form-row">
        <div class="form-group">
            <label for="brand">Brand</label>
            <input type="text" id="brand" name="brand" value="<?= e($cigar['brand']) ?>">
        </div>
        <div class="form-group">
            <label for="name">Name / Blend *</label>
            <input type="text" id="name" name="name" required value="<?= e($cigar['name']) ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="vitola">Vitola / Size</label>
            <input type="text" id="vitola" name="vitola" value="<?= e($cigar['vitola']) ?>">
        </div>
        <div class="form-group">
            <label for="wrapper">Wrapper</label>
            <input type="text" id="wrapper" name="wrapper" value="<?= e($cigar['wrapper']) ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="origin">Origin</label>
            <input type="text" id="origin" name="origin" value="<?= e($cigar['origin']) ?>">
        </div>
        <div class="form-group">
            <label for="strength">Strength</label>
            <select id="strength" name="strength">
                <option value="">—</option>
                <option value="Mild" <?= $cigar['strength'] === 'Mild' ? 'selected' : '' ?>>Mild</option>
                <option value="Medium" <?= $cigar['strength'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="Full" <?= $cigar['strength'] === 'Full' ? 'selected' : '' ?>>Full</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" min="0" value="<?= (int)$cigar['quantity'] ?>">
        </div>
        <div class="form-group">
            <label for="purchase_date">Purchase / Added date</label>
            <input type="date" id="purchase_date" name="purchase_date" value="<?= e($cigar['purchase_date']) ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="price">Price paid</label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="<?= e($cigar['price']) ?>">
        </div>
        <div class="form-group">
            <label for="humidor_id">Humidor</label>
            <select id="humidor_id" name="humidor_id">
                <?php foreach ($humidors as $h): ?>
                    <option value="<?= (int)$h['id'] ?>" <?= (int)$cigar['humidor_id'] === (int)$h['id'] ? 'selected' : '' ?>><?= e($h['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes"><?= e($cigar['notes']) ?></textarea>
    </div>

    <div class="form-group">
        <label for="photo">Photo <?= $cigar['photo'] ? '(leave blank to keep current)' : '' ?></label>
        <?php if ($cigar['photo']): ?>
            <div class="mb-2"><img src="<?= e(UPLOAD_URL . $cigar['photo']) ?>" alt="" style="max-width:120px;border-radius:6px;"></div>
        <?php endif; ?>
        <input type="file" id="photo" name="photo" accept="image/*">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="view_cigar.php?id=<?= $id ?>" class="btn btn-secondary">Cancel</a>
        <button type="submit" name="delete" value="1" class="btn btn-danger" data-confirm="Delete this cigar from inventory? Reviews will remain.">Delete</button>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
