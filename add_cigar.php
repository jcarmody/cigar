<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

$humidors = $pdo->query("SELECT id, name FROM humidors ORDER BY name")->fetchAll();
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $photo = handleUpload('photo');
        $stmt = $pdo->prepare("
            INSERT INTO cigars (humidor_id, brand, name, vitola, wrapper, origin, strength, quantity, purchase_date, price, notes, photo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$humidor_id, $brand ?: null, $name, $vitola ?: null, $wrapper ?: null, $origin ?: null, $strength ?: null, $quantity, $purchase_date, $price, $notes ?: null, $photo]);
        header('Location: inventory.php?added=1');
        exit;
    }
}

$pageTitle = 'Add Cigar';
$active = 'add_cigar';
require __DIR__ . '/includes/header.php';
?>

<h1>Add Cigar to Inventory</h1>
<p class="subtitle">Track a new stick (or box) in your humidor</p>

<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card form-card">
    <div class="form-row">
        <div class="form-group">
            <label for="brand">Brand</label>
            <input type="text" id="brand" name="brand" value="<?= e($_POST['brand'] ?? '') ?>" placeholder="e.g. Padrón">
        </div>
        <div class="form-group">
            <label for="name">Name / Blend *</label>
            <input type="text" id="name" name="name" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="e.g. 1964 Anniversary Maduro">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="vitola">Vitola / Size</label>
            <input type="text" id="vitola" name="vitola" value="<?= e($_POST['vitola'] ?? '') ?>" placeholder="e.g. Robusto, 5×50">
        </div>
        <div class="form-group">
            <label for="wrapper">Wrapper</label>
            <input type="text" id="wrapper" name="wrapper" value="<?= e($_POST['wrapper'] ?? '') ?>" placeholder="e.g. Maduro, Connecticut">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="origin">Origin</label>
            <input type="text" id="origin" name="origin" value="<?= e($_POST['origin'] ?? '') ?>" placeholder="e.g. Nicaragua">
        </div>
        <div class="form-group">
            <label for="strength">Strength</label>
            <select id="strength" name="strength">
                <option value="">—</option>
                <option value="Mild" <?= ($_POST['strength'] ?? '') === 'Mild' ? 'selected' : '' ?>>Mild</option>
                <option value="Medium" <?= ($_POST['strength'] ?? '') === 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="Full" <?= ($_POST['strength'] ?? '') === 'Full' ? 'selected' : '' ?>>Full</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" min="0" value="<?= e($_POST['quantity'] ?? '1') ?>">
        </div>
        <div class="form-group">
            <label for="purchase_date">Purchase / Added date</label>
            <input type="date" id="purchase_date" name="purchase_date" value="<?= e($_POST['purchase_date'] ?? date('Y-m-d')) ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="price">Price paid (optional)</label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="<?= e($_POST['price'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="humidor_id">Humidor</label>
            <select id="humidor_id" name="humidor_id">
                <?php foreach ($humidors as $h): ?>
                    <option value="<?= (int)$h['id'] ?>"><?= e($h['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" placeholder="Box code, source, aging goals…"><?= e($_POST['notes'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label for="photo">Photo</label>
        <input type="file" id="photo" name="photo" accept="image/*">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save to Inventory</button>
        <a href="inventory.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
