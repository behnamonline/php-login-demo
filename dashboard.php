<?php
// dashboard.php - داشبورد چند صفحه‌ای
//   dashboard.php?page=profile
//   dashboard.php?page=addItem

// فقط صفحه‌های مجاز (whitelist)
$allowed = ['profile', 'addItem'];
$page = $_GET['page'] ?? 'profile';
if (!in_array($page, $allowed, true)) {
    $page = 'profile';
}

$title = 'داشبورد';
$wide  = 'wide';
require 'header.php';
?>

<nav>
    <a href="dashboard.php?page=profile">پروفایل</a>
    <a href="dashboard.php?page=addItem">افزودن آیتم</a>
    <a href="#" id="logout">خروج</a>
    <span id="who" style="float:left"></span>
</nav>

<?php if ($page === 'profile'): ?>

    <h2>پروفایل</h2>
    <table id="profile"></table>

<?php elseif ($page === 'addItem'): ?>

    <h2>افزودن آیتم</h2>

    <form id="form">
        <label>عنوان</label>
        <input  style="direction:rtl" type="text" name="title" required>

        <label>توضیحات</label>
        <textarea style="direction:rtl" name="description" rows="3"></textarea>

        <button type="submit">ذخیره</button>
    </form>
    <p id="msg" class="msg"></p>

    <h3>آیتم‌های من</h3>
    <ul id="items" class="items"></ul>

<?php endif; ?>

<script>
    // ---------- بخش مشترک بین همه‌ی صفحه‌های داشبورد ----------
    const token = localStorage.getItem('token');

    // توکن نداریم؟ برو صفحه‌ی ورود
    if (!token) {
        location.href = 'login.php';
    }

    // تابع کمکی: درخواست به سرور با هدر توکن
    async function api(url, options = {}) {
        options.headers = { ...(options.headers || {}), 'X-Token': token };
        const res = await fetch(url, options);

        // توکن نامعتبر یا منقضی (403): توکن رو پاک کن و برو صفحه‌ی ورود
        if (res.status === 403) {
            localStorage.removeItem('token');
            location.href = 'login.php';
            throw new Error('Forbidden');
        }
        return { ok: res.ok, data: await res.json() };
    }

    // خروج: فقط توکن رو از مرورگر پاک می‌کنیم
    document.getElementById('logout').addEventListener('click', (e) => {
        e.preventDefault();
        localStorage.removeItem('token');
        location.href = 'login.php';
    });

    // درخواست به profile.php با هدر توکن
    async function loadProfile() {
        const { data } = await api('profile.php');
        document.getElementById('who').textContent = data.user.first_name + ' ' + data.user.last_name;
        return data.user;
    }
</script>

<?php if ($page === 'profile'): ?>
<script>
    // ---------- صفحه‌ی پروفایل ----------
    loadProfile().then((user) => {
        const rows = [
            ['شناسه', user.id],
            ['نام', user.first_name],
            ['نام خانوادگی', user.last_name],
            ['نام کاربری', user.username],
        ];
        const table = document.getElementById('profile');
        rows.forEach(([label, value]) => {
            const tr = document.createElement('tr');
            const th = document.createElement('th');
            const td = document.createElement('td');
            th.textContent = label;
            td.textContent = value;      // textContent = جلوگیری از XSS
            tr.append(th, td);
            table.appendChild(tr);
        });
    });
</script>
<?php elseif ($page === 'addItem'): ?>
<script>
    // ---------- صفحه‌ی افزودن آیتم ----------
    loadProfile();

    // نمایش لیست آیتم‌ها (GET به addItem.php)
    async function loadItems() {
        const { data } = await api('addItem.php');
        const ul = document.getElementById('items');
        ul.innerHTML = '';
        data.items.forEach((item) => {
            const li = document.createElement('li');
            li.textContent = item.title + (item.description ? ' - ' + item.description : '');
            ul.appendChild(li);
        });
    }
    loadItems();

    // ثبت آیتم جدید (POST به addItem.php)
    document.getElementById('form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = document.getElementById('msg');

        const { ok, data } = await api('addItem.php', { method: 'POST', body: new FormData(e.target) });

        msg.className = ok ? 'msg ok' : 'msg';
        msg.textContent = ok ? data.message : data.error;

        if (ok) {
            e.target.reset();
            loadItems();
        }
    });
</script>
<?php endif; ?>

<?php require 'footer.php'; ?>
