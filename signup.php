<?php
// signup.php - ثبت نام
require 'config.php';

// ---------- بخش ۱: دریافت فرم (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';

    // اعتبارسنجی
    if ($first_name === '' || $last_name === '' || $username === '' || $password === '') {
        json_out(['error' => 'همه‌ی فیلدها الزامی هستند'], 422);
    }
    if (strlen($password) < 6) {
        json_out(['error' => 'رمز عبور باید حداقل ۶ کاراکتر باشد'], 422);
    }

    // چک تکراری نبودن username
    $stmt = db()->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        json_out(['error' => 'این نام کاربری قبلا ثبت شده'], 409);
    }

    // ذخیره‌ی یوزر - پسورد با password_hash هش میشه (هیچوقت پسورد خام ذخیره نکن!)
    $stmt = db()->prepare('INSERT INTO users (first_name, last_name, username, password) VALUES (?, ?, ?, ?)');
    $stmt->execute([$first_name, $last_name, $username, password_hash($password, PASSWORD_DEFAULT)]);

    json_out(['message' => 'ثبت نام با موفقیت انجام شد']);
}

// ---------- بخش ۲: نمایش فرم (GET) ----------
$title = 'ثبت نام';
require 'header.php';
?>

<h2>ثبت نام</h2>

<form id="form">
    <label>نام</label>
    <input  style="direction:rtl" type="text" name="first_name" required>

    <label>نام خانوادگی</label>
    <input  style="direction:rtl" type="text" name="last_name" required>

    <label>نام کاربری</label>
    <input type="text" name="username" required>

    <label>رمز عبور</label>
    <input type="password" name="password" required>

    <button type="submit">ثبت نام</button>
</form>

<p id="msg" class="msg"></p>
<p>قبلا ثبت نام کرده‌اید؟ <a href="login.php">ورود</a></p>

<script>
    document.getElementById('form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = document.getElementById('msg');

        const res  = await fetch('signup.php', { method: 'POST', body: new FormData(e.target) });
        const data = await res.json();

        if (res.ok) {
            msg.className = 'msg ok';
            msg.textContent = data.message + ' - در حال انتقال به صفحه‌ی ورود...';
            setTimeout(() => location.href = 'login.php', 1000);
        } else {
            msg.className = 'msg';
            msg.textContent = data.error;
        }
    });
</script>

<?php require 'footer.php'; ?>
