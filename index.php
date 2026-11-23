<?php
// index.php - صفحه‌ی اصلی
$title = 'خانه';
require 'header.php';
?>

<h1>سیستم لاگین ساده</h1>
<p>سیستم ورود ساده</p>

<p id="guest">
    <a href="signup.php">ثبت نام</a> |
    <a href="login.php">ورود</a>
</p>

<p id="member" style="display:none">
    شما وارد شده‌اید. <a href="dashboard.php">رفتن به داشبورد</a>
</p>

<script>
    // اگه توکن توی localStorage بود، لینک داشبورد رو نشون بده
    if (localStorage.getItem('token')) {
        document.getElementById('guest').style.display = 'none';
        document.getElementById('member').style.display = 'block';
    }
</script>

<?php require 'footer.php'; ?>
