<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) {
    redirect(is_admin() ? '/admin/dashboard.php' : '/user/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        flash('Please fill in both fields.', 'error');
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $userRow = $stmt->get_result()->fetch_assoc();

        if ($userRow && password_verify($password, $userRow['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$userRow['id'];
            $_SESSION['role'] = $userRow['role'];
            $_SESSION['user'] = $userRow;
            flash('Welcome back, ' . $userRow['name'] . '!');
            redirect($userRow['role'] === 'admin' ? '/admin/dashboard.php' : '/user/dashboard.php');
        } else {
            flash('Invalid email or password.', 'error');
        }
    }
}

$title = 'Sign In';
$active = '';
include __DIR__ . '/inc/header.php';
?>

<section class="max-w-md mx-auto px-4 py-14">
    <div class="bg-white rounded-2xl shadow-lg border border-stone-100 p-8 fade-up">
        <div class="text-center mb-6">
            <span class="text-4xl">🔐</span>
            <h1 class="font-display text-2xl font-bold text-stone-800 mt-2">Welcome back</h1>
            <p class="text-sm text-stone-500 mt-1">Sign in to borrow books and read online.</p>
        </div>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Email</label>
                <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400" placeholder="you@example.com">
            </div>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400" placeholder="••••••••">
            </div>
            <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl transition">Sign In</button>
        </form>

        <p class="text-sm text-center text-stone-500 mt-5">
            New here? <a href="<?= BASE_URL ?>/register.php" class="text-brand-700 font-bold hover:underline">Create an account</a>
        </p>

        <div class="mt-6 bg-cream border border-stone-200 rounded-xl p-4 text-xs text-stone-600 space-y-1">
            <p class="font-bold text-stone-700">Demo accounts</p>
            <p>👑 Admin — admin@library.com / admin123</p>
            <p>📖 User — user@library.com / user123</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/inc/footer.php'; ?>
