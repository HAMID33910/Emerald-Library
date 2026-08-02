<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) {
    redirect(is_admin() ? '/admin/dashboard.php' : '/user/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $errors = [];
    if (mb_strlen($name) < 2) $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (mb_strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
        }
        $check->close();
    }

    if (!empty($errors)) {
        flash($errors[0], 'error');
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?, 'user')");
        $ins->bind_param('sss', $name, $email, $hash);
        $ins->execute();
        $userId = $conn->insert_id;

        session_regenerate_id(true);
        $row = $conn->query("SELECT * FROM users WHERE id=$userId")->fetch_assoc();
        $_SESSION['user_id'] = (int)$userId;
        $_SESSION['role'] = 'user';
        $_SESSION['user'] = $row;
        flash('Account created! Welcome to Emerald Library, ' . $name . '!');
        redirect('/user/dashboard.php');
    }
}

$title = 'Create Account';
$active = '';
include __DIR__ . '/inc/header.php';
?>

<section class="max-w-md mx-auto px-4 py-14">
    <div class="bg-white rounded-2xl shadow-lg border border-stone-100 p-8 fade-up">
        <div class="text-center mb-6">
            <span class="text-4xl">✨</span>
            <h1 class="font-display text-2xl font-bold text-stone-800 mt-2">Join Emerald Library</h1>
            <p class="text-sm text-stone-500 mt-1">Membership is free — borrow books and read online.</p>
        </div>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Full name</label>
                <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400" placeholder="Alex Reader">
            </div>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Email</label>
                <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400" placeholder="you@example.com">
            </div>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400" placeholder="At least 6 characters">
            </div>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Confirm password</label>
                <input type="password" name="confirm" required class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400" placeholder="Repeat your password">
            </div>
            <button type="submit" class="w-full bg-gold-500 hover:bg-gold-600 text-stone-900 font-bold py-3 rounded-xl transition">Create Account</button>
        </form>

        <p class="text-sm text-center text-stone-500 mt-5">
            Already a member? <a href="<?= BASE_URL ?>/login.php" class="text-brand-700 font-bold hover:underline">Sign in</a>
        </p>
    </div>
</section>

<?php include __DIR__ . '/inc/footer.php'; ?>
