<?php
// ============================================================
// config.php
// اتصال به دیتابیس SQLite + چند تابع کمکی
// این فایل توی همه‌ی فایل‌های دیگه require میشه
// ============================================================

define('DB_PATH', __DIR__ . '/db/database.sqlite');
define('TOKEN_LIFETIME', 60 * 60 * 24 * 7); // اعتبار توکن: ۷ روز (به ثانیه)

// اتصال به دیتابیس (فقط یک بار ساخته میشه)
function db()
{
    static $pdo = null;

    if ($pdo === null) {
        // اگه پوشه‌ی db نبود بسازش
        if (!is_dir(dirname(DB_PATH))) {
            mkdir(dirname(DB_PATH), 0755, true);
        }

        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');

        // ساخت جدول‌ها (اگه از قبل وجود نداشتن)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                first_name TEXT NOT NULL,
                last_name  TEXT NOT NULL,
                username   TEXT NOT NULL UNIQUE,
                password   TEXT NOT NULL
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tokens (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                token       TEXT NOT NULL,
                user_id     INTEGER NOT NULL,
                expire_date TEXT NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS items (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id     INTEGER NOT NULL,
                title       TEXT NOT NULL,
                description TEXT,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
    }

    return $pdo;
}

// ارسال جواب JSON و تموم کردن اجرای اسکریپت
function json_out($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ساخت توکن جدید برای یوزر
// توکنی که به مرورگر داده میشه (و توی localStorage ذخیره میشه):  user_id|رشته_رندوم
//     مثلا:  2|6ddbefa30a6a...
// توی دیتابیس فقط هش sha256 بخش رندوم ذخیره میشه، نه خود توکن!
// (چون توکن رندوم و طولانیه، sha256 کافیه و سریعه؛ نیازی به password_hash نیست)
function create_token($user_id)
{
    $plain  = bin2hex(random_bytes(32));
    $expire = gmdate('Y-m-d H:i:s', time() + TOKEN_LIFETIME);

    $stmt = db()->prepare('INSERT INTO tokens (token, user_id, expire_date) VALUES (?, ?, ?)');
    $stmt->execute([hash('sha256', $plain), $user_id, $expire]);

    return $user_id . '|' . $plain;
}

// بررسی توکن ارسال‌شده توی هدر X-Token
// اگه معتبر بود اطلاعات یوزر برگردونده میشه، وگرنه ارور 403
function require_auth()
{
    $token = $_SERVER['HTTP_X_TOKEN'] ?? '';

    // مرحله ۱: جدا کردن user_id از توکن
    $parts = explode('|', $token, 2);
    if (count($parts) !== 2 || !ctype_digit($parts[0])) {
        json_out(['error' => 'Forbidden'], 403);
    }
    $user_id = (int) $parts[0];

    // مرحله ۲: سرچ توی جدول tokens با user_id (فقط توکن‌های منقضی‌نشده)
    $stmt = db()->prepare("SELECT token FROM tokens WHERE user_id = ? AND expire_date > datetime('now')");
    $stmt->execute([$user_id]);

    // مرحله ۳: هش کردن بخش رندوم توکن و مقایسه با هش‌های داخل دیتابیس
    // (هر یوزر ممکنه چند توکن داشته باشه)
    $hash  = hash('sha256', $parts[1]);
    $valid = false;
    foreach ($stmt->fetchAll() as $row) {
        if (hash_equals($row['token'], $hash)) {
            $valid = true;
            break;
        }
    }
    if (!$valid) {
        json_out(['error' => 'Forbidden'], 403);
    }

    // مرحله ۴: برگردوندن اطلاعات یوزر
    $stmt = db()->prepare('SELECT id, first_name, last_name, username FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        json_out(['error' => 'Forbidden'], 403);
    }

    return $user;
}
