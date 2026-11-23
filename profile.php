<?php
// profile.php - API (خروجی JSON)
// توکن رو از هدر X-Token می‌گیره، بررسی می‌کنه و اطلاعات یوزر رو برمی‌گردونه
// توکن نامعتبر = ارور 403
require 'config.php';

$user = require_auth();

json_out(['user' => $user]);
