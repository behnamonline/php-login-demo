<?php // header.php - بالای همه‌ی صفحه‌ها. قبل از include میشه $title رو تعریف کرد ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'سیستم لاگین') ?></title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f3f4f6; margin: 0; color: #222; }
        .box { max-width: 480px; margin: 40px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.15); }
        .box.wide { max-width: 640px; }
        h1, h2 { margin-top: 0; }
        label { display: block; margin: 12px 0 4px; }
        input, textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; direction: ltr;}
        button { margin-top: 16px; padding: 8px 20px; background: #2563eb; color: #fff; border: 0; border-radius: 4px; cursor: pointer; }
        a { color: #2563eb;text-decoration: none; }
        nav { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #ddd; }
        nav a { margin-left: 12px; }
        .msg { margin-top: 12px; color: #b91c1c; }
        .msg.ok { color: #15803d; }
        ul.items { padding-right: 20px; }
    </style>
</head>
<body>
<div class="box <?= $wide ?? '' ?>">
