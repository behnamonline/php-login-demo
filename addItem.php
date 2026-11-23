<?php
// addItem.php - API (خروجی JSON)
//   GET  -> لیست آیتم‌های یوزر
//   POST -> اضافه کردن آیتم جدید
// هر دو حالت توکن می‌خوان (هدر X-Token)
require 'config.php';

$user = require_auth();

// ---------- اضافه کردن آیتم ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') {
        json_out(['error' => 'عنوان الزامی است'], 422);
    }

    $stmt = db()->prepare('INSERT INTO items (user_id, title, description) VALUES (?, ?, ?)');
    $stmt->execute([$user['id'], $title, $description]);

    json_out(['message' => 'آیتم اضافه شد', 'id' => (int) db()->lastInsertId()]);
}

// ---------- لیست آیتم‌ها ----------
$stmt = db()->prepare('SELECT id, title, description FROM items WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$user['id']]);

json_out(['items' => $stmt->fetchAll()]);
