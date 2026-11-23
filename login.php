<?php
// login.php - ورود
require 'config.php';

// ---------- بخش ۱: دریافت فرم (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT id, password FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // password_verify پسورد خام رو با هش داخل دیتابیس مقایسه می‌کنه
    if (!$user || !password_verify($password, $user['password'])) {
        json_out(['error' => 'نام کاربری یا رمز عبور اشتباه است'], 401);
    }

    // لاگین موفق: ساخت توکن و ذخیره توی جدول tokens
    $token = create_token($user['id']);

    json_out(['token' => $token]);
}

// ---------- بخش ۲: نمایش فرم (GET) ----------
$title = 'ورود';
require 'header.php';
?>

<h2>ورود</h2>

<form id="form">
    <label>نام کاربری</label>
    <input type="text" name="username" required>

    <label>رمز عبور</label>
    <input type="password" name="password" required>

    <button type="submit">ورود</button>
</form>

<p id="msg" class="msg"></p>
<p>حساب ندارید؟ <a href="signup.php">ثبت نام</a></p>

<script>
    document.getElementById('form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = document.getElementById('msg');

        const res  = await fetch('login.php', { method: 'POST', body: new FormData(e.target) });
        const data = await res.json();

        if (res.ok) {
            // توکن رو توی localStorage ذخیره می‌کنیم و میریم داشبورد
            localStorage.setItem('token', data.token);
            location.href = 'dashboard.php';
        } else {
            msg.textContent = data.error;
        }
    });
</script>

<?php require 'footer.php'; ?>
