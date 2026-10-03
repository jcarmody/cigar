<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

$prefill = null;
$cigar_id = (int)($_GET['cigar_id'] ?? 0);
if ($cigar_id) {
    $stmt = $pdo->prepare("SELECT * FROM cigars WHERE id = ?");
    $stmt->execute([$cigar_id]);
    $prefill = $stmt->fetch();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cigar_id = (int)($_POST['cigar_id'] ?? 0) ?: null;
    $brand = trim($_POST['brand'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $vitola = trim($_POST['vitola'] ?? '');
    $smoke_date = $_POST['smoke_date'] ?? null ?: null;
    $overall = $_POST['overall_rating'] !== '' ? (float)$_POST['overall_rating'] : null;
    $appearance = $_POST['appearance'] !== '' ? (float)$_POST['appearance'] : null;
    $construction = $_POST['construction'] !== '' ? (float)$_POST['construction'] : null;
    $burn = $_POST['burn'] !== '' ? (float)$_POST['burn'] : null;
    $draw = $_POST['draw'] !== '' ? (float)$_POST['draw'] : null;
    $flavor = $_POST['flavor'] !== '' ? (float)$_POST['flavor'] : null;
    $aroma = $_POST['aroma'] !== '' ? (float)$_POST['aroma'] : null;
    $cold_draw = trim($_POST['cold_draw'] ?? '');
    $first_third = trim($_POST['first_third'] ?? '');
    $second_third = trim($_POST['second_third'] ?? '');
    $final_third = trim($_POST['final_third'] ?? '');
    $pairings = trim($_POST['pairings'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $decrement = !empty($_POST['decrement_inventory']);

    if ($name === '') {
        $error = 'Cigar name is required.';
    } else {
        $photo = handleUpload('photo');
        $stmt = $pdo->prepare(""
            INSERT INTO reviews (
                cigar_id, brand, name, vitola, smoke_date, overall_rating,
                appearance, construction, burn, draw, flavor, aroma,
                cold_draw, first_third, second_third, final_third,
                pairings, location, notes, photo
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        """);
        $stmt->execute([
            $cigar_id, $brand ?: null, $name, $vitola ?: null, $smoke_date, $overall,
            $appearance, $construction, $burn, $draw, $flavor, $aroma,
            $cold_draw ?: null, $first_third ?: null, $second_third ?: null, $final_third ?: null,
            $pairings ?: null, $location ?: null, $notes ?: null, $photo
        ]);

        // Optionally decrement inventory
        if ($decrement && $cigar_id) {
            $pdo->prepare("UPDATE cigars SET quantity = MAX(0, quantity - 1), updated_at = datetime('now') WHERE id = ?")
                ->execute([$cigar_id]);
        }

        header('Location: reviews.php?added=1');
        exit;
    }
}

$pageTitle = 'Log Review';
$active = 'add_review';
require __DIR__ . '/includes/header.php';
?>

<h1>Log a Review</h1>
<p class="subtitle">Capture the full experience from cold draw to final third</p>

<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card form-card">
    <?php if ($prefill): ?>
        <input type="hidden" name="cigar_id" value="<?= (int)$prefill['id'] ?>">
        <div class="alert alert-success">Prefilling from inventory: <?= e(trim(($prefill['brand'] ?? '') . ' ' . $prefill['name'])) ?></div>
    <?php endif; ?>

    <div class="form-row">
        <div class="form-group">
            <label for="brand">Brand</label>
            <input type="text" id="brand" name="brand" value="<?= e($_POST['brand'] ?? $_GET['brand'] ?? $prefill['brand'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="name">Name / Blend *</label>
            <input type="text" id="name" name="name" required value="<?= e($_POST['name'] ?? $_GET['name'] ?? $prefill['name'] ?? '') ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="vitola">Vitola</label>
            <input type="text" id="vitola" name="vitola" value="<?= e($_POST['vitola'] ?? $_GET['vitola'] ?? $prefill['vitola'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="smoke_date">Smoke date</label>
            <input type="date" id="smoke_date" name="smoke_date" value="<?= e($_POST['smoke_date'] ?? $_GET['smoke_date'] ?? date('Y-m-d')) ?>">
        </div>
    </div>

    <h3 style="font-size:1rem;color:var(--accent);margin:1.25rem 0 0.75rem;">Ratings (1–5)</h3>
    <div class="rating-inputs">
        <?php
        $ratingFields = [
            'overall_rating' => 'Overall',
            'appearance' => 'Appearance',
            'construction' => 'Construction',
            'burn' => 'Burn',
            'draw' => 'Draw',
            'flavor' => 'Flavor',
            'aroma' => 'Aroma',
        ];
        foreach ($ratingFields as $field => $label):
        ?>
            <div class="form-group">
                <label for="<?= $field ?>"><?= $label ?></label>
                <input type="number" id="<?= $field ?>" name="<?= $field ?>" min="0" max="5" step="0.5"
                       value="<?= e($_POST[$field] ?? '') ?>" placeholder="0–5">
            </div>
        <?php endforeach; ?>
    </div>

    <h3 style="font-size:1rem;color:var(--accent);margin:1.5rem 0 0.75rem;">Tasting Notes by Stage</h3>

    <div class="form-group">
        <label for="cold_draw">Cold draw</label>
        <textarea id="cold_draw" name="cold_draw" placeholder="Aromas before lighting…"><?= e($_POST['cold_draw'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label for="first_third">First third</label>
        <textarea id="first_third" name="first_third"><?= e($_POST['first_third'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label for="second_third">Second third</label>
        <textarea id="second_third" name="second_third"><?= e($_POST['second_third'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label for="final_third">Final third</label>
        <textarea id="final_third" name="final_third"><?= e($_POST['final_third'] ?? '') ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="pairings">Pairings</label>
            <input type="text" id="pairings" name="pairings" value="<?= e($_POST['pairings'] ?? '') ?>" placeholder="Coffee, whisky, rum…">
        </div>
        <div class="form-group">
            <label for="location">Location / Occasion</label>
            <input type="text" id="location" name="location" value="<?= e($_POST['location'] ?? '') ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="notes">Additional notes</label>
        <textarea id="notes" name="notes"><?= e($_POST['notes'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label for="photo">Photo</label>
        <input type="file" id="photo" name="photo" accept="image/*">
    </div>

    <?php if ($prefill && (int)$prefill['quantity'] > 0): ?>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                <input type="checkbox" name="decrement_inventory" value="1" checked>
                Remove 1 from inventory after saving (currently ×<?= (int)$prefill['quantity'] ?>)
            </label>
        </div>
    <?php endif; ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Review</button>
        <a href="reviews.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
