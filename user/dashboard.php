<?php
require_once __DIR__ . '/../auth.php';
require_login();
$uid = (int)$_SESSION['user_id'];

$myIssued = (int)$conn->query("SELECT COUNT(*) AS c FROM issues WHERE user_id=$uid AND status='issued'")->fetch_assoc()['c'];
$pendingCount = (int)$conn->query("SELECT COUNT(*) AS c FROM borrow_requests WHERE user_id=$uid AND status='pending'")->fetch_assoc()['c'];
$totalBorrowed = (int)$conn->query("SELECT COUNT(*) AS c FROM issues WHERE user_id=$uid")->fetch_assoc()['c'];
$fines = (float)$conn->query("SELECT COALESCE(SUM(fine),0) AS c FROM issues WHERE user_id=$uid AND status='returned'")->fetch_assoc()['c'];

$current = $conn->query("SELECT i.*, b.title, b.author FROM issues i JOIN books b ON b.id=i.book_id WHERE i.user_id=$uid AND i.status='issued' ORDER BY i.due_date ASC");
$pending = $conn->query("SELECT r.*, b.title, b.author FROM borrow_requests r JOIN books b ON b.id=r.book_id WHERE r.user_id=$uid AND r.status='pending' ORDER BY r.requested_at DESC");

$today = new DateTime();

$title = 'My Dashboard';
$active = 'dashboard';
include __DIR__ . '/../inc/header.php';
?>

<section class="bg-gradient-to-r from-brand-800 to-brand-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <h1 class="font-display text-3xl font-bold">Welcome, <?= e(current_user()['name']) ?>! 👋</h1>
        <p class="text-brand-100 mt-1">Your reading world at a glance.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 mt-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php
        $cards = [
            ['label' => 'Books with me', 'value' => $myIssued, 'icon' => '📦'],
            ['label' => 'Pending requests', 'value' => $pendingCount, 'icon' => '🕐'],
            ['label' => 'Total borrowed', 'value' => $totalBorrowed, 'icon' => '📚'],
            ['label' => 'Fines owed', 'value' => $fines > 0 ? '$' . number_format($fines, 2) : '$0.00', 'icon' => '💰'],
        ];
        foreach ($cards as $c): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5 fade-up">
            <div class="text-3xl mb-2"><?= $c['icon'] ?></div>
            <div class="text-2xl font-extrabold text-brand-700"><?= $c['value'] ?></div>
            <div class="text-xs font-semibold text-stone-500 mt-1"><?= $c['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Currently borrowed -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden mt-8">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
            <h2 class="font-display font-bold text-stone-800">📦 Currently Borrowed</h2>
            <a href="<?= BASE_URL ?>/books.php" class="text-sm font-bold text-brand-700 hover:underline">Borrow more →</a>
        </div>
        <?php if ($current && $current->num_rows): ?>
            <div class="divide-y divide-stone-100">
                <?php while ($i = $current->fetch_assoc()):
                    $due = new DateTime($i['due_date']);
                    $overdue = $today > $due;
                    $daysLate = $overdue ? (int)$today->diff($due)->days : 0;
                ?>
                <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                    <span class="w-12 h-16 rounded-lg bg-brand-700 text-white text-xs font-bold flex items-center justify-center shrink-0"><?= e(strtoupper(mb_substr($i['title'], 0, 2))) ?></span>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-stone-800 truncate"><?= e($i['title']) ?></p>
                        <p class="text-xs text-stone-500">by <?= e($i['author']) ?></p>
                        <p class="text-xs mt-1 <?= $overdue ? 'text-rose-600 font-bold' : 'text-stone-500' ?>">
                            Due <?= e(date('M j, Y', strtotime($i['due_date']))) ?>
                            <?= $overdue ? '· ⏰ ' . $daysLate . ' day' . ($daysLate > 1 ? 's' : '') . ' late' : '' ?>
                        </p>
                    </div>
                    <a href="<?= BASE_URL ?>/user/read.php?id=<?= (int)$i['book_id'] ?>" class="inline-block text-center bg-gold-400 hover:bg-gold-500 text-stone-900 text-sm font-bold px-5 py-2 rounded-xl transition shrink-0">Read Online</a>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="px-5 py-10 text-center text-stone-500 text-sm">You are not borrowing anything right now. <a href="<?= BASE_URL ?>/books.php" class="text-brand-700 font-bold hover:underline">Browse the shelves →</a></p>
        <?php endif; ?>
    </div>

    <!-- Pending requests -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden mt-8">
        <div class="px-5 py-4 border-b border-stone-100">
            <h2 class="font-display font-bold text-stone-800">🕐 Pending Requests</h2>
        </div>
        <?php if ($pending && $pending->num_rows): ?>
            <div class="divide-y divide-stone-100">
                <?php while ($r = $pending->fetch_assoc()): ?>
                <div class="px-5 py-4 flex items-center gap-4">
                    <span class="w-11 h-11 rounded-xl bg-gold-100 text-gold-700 font-bold flex items-center justify-center">⏳</span>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-stone-800 truncate"><?= e($r['title']) ?></p>
                        <p class="text-xs text-stone-500">by <?= e($r['author']) ?> · requested <?= e(date('M j, Y', strtotime($r['requested_at']))) ?></p>
                    </div>
                    <span class="bg-gold-100 text-gold-800 text-xs font-bold px-3 py-1 rounded-full">Waiting for approval</span>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="px-5 py-8 text-center text-stone-500 text-sm">No pending requests.</p>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../inc/footer.php'; ?>
