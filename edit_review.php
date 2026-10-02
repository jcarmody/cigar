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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        if ($r['photo'] && file_exists(UPLOAD_DIR . $r['photo'])) {
            @unlink(UPLOAD_DIR . $r['photo']);
        }
        $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
        header('Location: reviews.php?deleted=1');
        exit;
    }

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

    if ($name === '') {
        $error = 'Cigar name is required.';
    } else {
        $photo = handleUpload('photo') ?? $r['photo'];
        $stmt = $pdo->prepare("
            UPDATE reviews SET
                brand=?, name=?, vitola=?, smoke_date=?, overall_rating=?,
                appearance=?, construction=?, burn=?, draw=?, flavor=?, aroma=?,
                cold_draw=?, first_third=?, second_third=?, final_third=?,
                pairings=?, location=?, notes=?, photo=?
            WHERE id=?
        ");
        $stmt->execute([
            $brand ?: null, $name, $vitola ?: null, $smoke_date, $overall,
            $appearance, $construction, $burn, $draw, $flavor, $aroma,
            $cold_draw ?: null, $first_third ?: null, $second_third ?: null, $final_third ?: null,
            $pairings ?: null, $location ?: null, $notes ?: null, $photo, $id
        ]);
        header('Location: view_review.php?id=' . $id);
        exit;
    }
}

$pageTitle = 'Edit Review';
$active = 'reviews';
require __DIR__ . '/includes/header.php';
?>

<h1>Edit Review</h1>
<p class="subtitle"><?= e(trim(($r['brand'] ?? '') . ' ' . $r['name'])) ?></p>

<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card form-card">
    <div class="form-row">
        <div class="form-group">
            <label for="brand">Brand</label>
            <input type="text" id="brand" name="brand" value="<?= e($r['brand']) ?>">
        </div>
        <div class="form-group">
            <label for="name">Name / Blend *</label>
            <input type="text" id="name" name="name" required value="<?= e($r['name']) ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="vitola">Vitola</label>
            <input type="text" id="vitola" name="vitola" value="<?= e($r['vitola']) ?>">
        </div>
        <div class="form-group">
            <label for="smoke_date">Smoke date</label>
            <input type="date" id="smoke_date" name="smoke_date" value="<?= e($r['smoke_date']) ?>">
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
                       value="<?= e($r[$field]) ?>">
            </div>
        <?php endforeach; ?>
    </div>

    <h3 style="font-size:1rem;color:var(--accent);margin:1.5rem 0 0.75rem;">Tasting Notes by Stage</h3>

    <div class="form-group">
        <label for="cold_draw">Cold draw</label>
        <textarea id="cold_draw" name="cold_draw"><?= e($r['cold_draw']) ?></textarea>
    </div>
    <div class="form-group">
        <label for="first_third">First third</label>
        <textarea id="first_third" name="first_third"><?= e($r['first_third']) ?></textarea>
    </div>
    <div class="form-group">
        <label for="second_third">Second third</label>
        <textarea id="second_third" name="second_third"><?= e($r['second_third']) ?></textarea>
    </div>
    <div class="form-group">
        <label for="final_third">Final third</label>
        <textarea id="final_third" name="final_third"><?= e($r['final_third']) ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="pairings">Pairings</label>
            <input type="text" id="pairings" name="pairings" value="<?= e($r['pairings']) ?>">
        </div>
        <div class="form-group">
            <label for="location">Location / Occasion</label>
            <input type="text" id="location" name="location" value="<?= e($r['location']) ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="notes">Additional notes</label>
        <textarea id="notes" name="notes"><?= e($r['notes']) ?></textarea>
    </div>

    <div class="form-group">
        <label for="photo">Photo <?= $r['photo'] ? '(leave blank to keep current)' : '' ?></label>
        <?php if ($r['photo']): ?>
            <div class="mb-2"><img src="<?= e(UPLOAD_URL . $r['photo']) ?>" alt="" style="max-width:120px;border-radius:6px;"></div>
        <?php endif; ?>
        <input type="file" id="photo" name="photo" accept="image/*">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="view_review.php?id=<?= $id ?>" class="btn btn-secondary">Cancel</a>
        <button type="submit" name="delete" value="1" class="btn btn-danger" data-confirm="Delete this review permanently?">Delete</button>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
