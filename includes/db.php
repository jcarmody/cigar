<?php
/**
 * SQLite database connection and schema initialization
 * for My Humidor Journal
 */

define('DB_PATH', __DIR__ . '/../data/humidor.db');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');

// Ensure directories exist
if (!is_dir(dirname(DB_PATH))) {
    mkdir(dirname(DB_PATH), 0755, true);
}
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');
        initSchema($pdo);
    }
    return $pdo;
}

function initSchema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS humidors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            created_at TEXT DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS cigars (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            humidor_id INTEGER NOT NULL DEFAULT 1,
            brand TEXT,
            name TEXT NOT NULL,
            vitola TEXT,
            wrapper TEXT,
            origin TEXT,
            strength TEXT,
            quantity INTEGER NOT NULL DEFAULT 1,
            purchase_date TEXT,
            price REAL,
            notes TEXT,
            photo TEXT,
            created_at TEXT DEFAULT (datetime('now')),
            updated_at TEXT DEFAULT (datetime('now')),
            FOREIGN KEY (humidor_id) REFERENCES humidors(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS reviews (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cigar_id INTEGER,
            brand TEXT,
            name TEXT NOT NULL,
            vitola TEXT,
            smoke_date TEXT,
            overall_rating REAL,
            appearance REAL,
            construction REAL,
            burn REAL,
            draw REAL,
            flavor REAL,
            aroma REAL,
            cold_draw TEXT,
            first_third TEXT,
            second_third TEXT,
            final_third TEXT,
            pairings TEXT,
            location TEXT,
            notes TEXT,
            photo TEXT,
            created_at TEXT DEFAULT (datetime('now')),
            FOREIGN KEY (cigar_id) REFERENCES cigars(id) ON DELETE SET NULL
        );

        CREATE INDEX IF NOT EXISTS idx_cigars_humidor ON cigars(humidor_id);
        CREATE INDEX IF NOT EXISTS idx_cigars_brand ON cigars(brand);
        CREATE INDEX IF NOT EXISTS idx_reviews_smoke_date ON reviews(smoke_date);
        CREATE INDEX IF NOT EXISTS idx_reviews_name ON reviews(name);
    ");

    // Seed default humidor if empty
    $count = $pdo->query("SELECT COUNT(*) FROM humidors")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO humidors (name, description) VALUES ('Main Humidor', 'Primary collection')");
    }
}

/**
 * Simple helper to sanitize output
 */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Handle photo upload. Returns filename or null.
 */
function handleUpload(string $field = 'photo'): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file = $_FILES[$field];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed)) {
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        return null;
    }
    $ext = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        default => 'jpg'
    };
    $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = UPLOAD_DIR . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $filename;
    }
    return null;
}

/**
 * Calculate days aging
 */
function daysAging(?string $date): ?int {
    if (!$date) return null;
    try {
        $start = new DateTime($date);
        $now = new DateTime('today');
        return (int)$start->diff($now)->days;
    } catch (Exception $e) {
        return null;
    }
}
