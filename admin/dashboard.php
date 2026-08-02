<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$totalBooks = (int)$conn->query("SELECT COUNT(*) AS c FROM books")->fetch_assoc()['c'];
$totalCopies = (int)$conn->query("SELECT COALESCE(SUM(total_copies),0) AS c FROM books")->fetch_assoc()['c'];
$totalMembers = (int)$conn->query("SELECT COUNT(*) AS c FROM users WHERE role='user'")->fetch_assoc()['c'];
$pendingCount = (int)$conn->query("SELECT COUNT(*) AS c FROM borrow_requests WHERE status='pending'")->fetch_assoc()['c'];
$issuedCount = (int)$conn->query("SELECT COUNT(*) AS c FROM issues WHERE status='issued'")->fetch_assoc()['c'];
$returnedCount = (int)$conn->query("SELECT COUNT(*) AS c FROM issues WHERE status='returned'")->fetch_assoc()['c'];
$fineTotal = (float)$conn->query("SELECT COALESCE(SUM(fine),0) AS c FROM issues WHERE status='returned'")->fetch_assoc()['c'];

$pendingRequests = $conn->query("SELECT r.*, u.name AS user_name, u.email, b.title AS book_title, b.cover
                                 FROM borrow_requests r
                                 JOIN users u ON u.id=r.user_id
                                 JOIN books b ON b.id=r.book_id
                                 WHERE r.status='pending'
                                 ORDER BY r.requested_at DESC LIMIT 6");

$lowStock = $conn->query("SELECT b.*, c.name AS category_name
                          FROM books b LEFT JOIN categories c ON b.category_id=c.id
                          WHERE b.available_copies <= 1
                          ORDER BY b.available_copies ASC LIMIT 6");

$recentIssues = $conn->query("SELECT i.*, u.name AS user_name, b.title AS book_title
                              FROM issues i
                              JOIN users u ON u.id=i.user_id
                              JOIN books b ON b.id=i.book_id
                              ORDER BY i.issue_date DESC LIMIT 6");

$title = 'Admin Dashboard';
$active = 'dashboard';
include __DIR__ . '/../inc/header.php';
?>

<section class="bg-gradient-to-r from-brand-800 to-brand-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <h1 class="font-display text-3xl font-bold">Librarian Dashboard</h1>
        <p class="text-brand-100 mt-1">Good day, <?= e(current_user()['name']) ?>! Here is what is happening in the library today.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 mt-8">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php
        $cards = [
            ['label' => 'Total Books', 'value' => $totalBooks, 'icon' => '📚', 'href' => '/admin/books.php'],
            ['label' => 'Total Copies', 'value' => $totalCopies, 'icon' => '🗂️', 'href' => '/admin/books.php'],
            ['label' => 'Members', 'value' => $totalMembers, 'icon' => '👥', 'href' => '/admin/members.php'],
            ['label' => 'Pending Requests', 'value' => $pendingCount, 'icon' => '🕐', 'href' => '/admin/requests.php'],
            ['label' => 'Issued Now', 'value' => $issuedCount, 'icon' => '📦', 'href' => '/admin/issues.php'],
            ['label' => 'Returns', 'value' => $returnedCount, 'icon' => '↩️', 'href' => '/admin/issues.php'],
            ['label' => 'Fines Collected', 'value' => '$' . number_format($fineTotal, 2), 'icon' => '💰', 'href' => '/admin/issues.php'],
        ];
        foreach ($cards as $c): ?>
        <a href="<?= BASE_URL . $c['href'] ?>" class="bg-white rounded-2xl shadow-sm hover:shadow-lg border border-stone-100 p-5 transition fade-up">
            <div class="text-3xl mb-2"><?= $c['icon'] ?></div>
            <div class="text-2xl font-extrabold text-brand-700"><?= $c['value'] ?></div>
            <div class="text-xs font-semibold text-stone-500 mt-1"><?= $c['label'] ?></div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Two column: pending requests + low stock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <h2 class="font-display font-bold text-stone-800">Pending Requests</h2>
                <a href="<?= BASE_URL ?>/admin/requests.php" class="text-sm font-bold text-brand-700 hover:underline">View all</a>
            </div>
            <?php if ($pendingRequests && $pendingRequests->num_rows): ?>
                <div class="divide-y divide-stone-100">
                    <?php while ($r = $pendingRequests->fetch_assoc()): ?>
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-gold-100 text-gold-700 font-bold flex items-center justify-center text-lg">📕</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm text-stone-800 truncate"><?= e($r['book_title']) ?></p>
                            <p class="text-xs text-stone-500 truncate"><?= e($r['user_name']) ?> · <?= e(date('M j, g:i A', strtotime($r['requested_at']))) ?></p>
                        </div>
                        <a href="<?= BASE_URL ?>/admin/requests.php" class="text-xs font-bold text-brand-700 bg-brand-50 px-3 py-1.5 rounded-lg hover:bg-brand-100 transition">Review</a>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="px-5 py-8 text-center text-stone-500 text-sm">🎉 No pending requests. All caught up!</p>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <h2 class="font-display font-bold text-stone-800">Low Stock Alert</h2>
                <a href="<?= BASE_URL ?>/admin/books.php" class="text-sm font-bold text-brand-700 hover:underline">Manage books</a>
            </div>
            <?php if ($lowStock && $lowStock->num_rows): ?>
                <div class="divide-y divide-stone-100">
                    <?php while ($b = $lowStock->fetch_assoc()): ?>
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center text-lg">⚠️</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-sm text-stone-800 truncate"><?= e($b['title']) ?></p>
                            <p class="text-xs text-stone-500"><?= (int)$b['available_copies'] ?> of <?= (int)$b['total_copies'] ?> copies available</p>
                        </div>
                        <a href="<?= BASE_URL ?>/admin/book-form.php?id=<?= (int)$b['id'] ?>" class="text-xs font-bold text-gold-700 bg-gold-50 px-3 py-1.5 rounded-lg hover:bg-gold-100 transition">Restock</a>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="px-5 py-8 text-center text-stone-500 text-sm">✅ All books have plenty of copies.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent issues -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden mt-8">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
            <h2 class="font-display font-bold text-stone-800">Recent Borrowing Activity</h2>
            <a href="<?= BASE_URL ?>/admin/issues.php" class="text-sm font-bold text-brand-700 hover:underline">View all</a>
        </div>
        <?php if ($recentIssues && $recentIssues->num_rows): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-500">
                            <th class="px-5 py-3 font-bold">Book</th>
                            <th class="px-5 py-3 font-bold">Member</th>
                            <th class="px-5 py-3 font-bold">Issued</th>
                            <th class="px-5 py-3 font-bold">Due</th>
                            <th class="px-5 py-3 font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <?php while ($i = $recentIssues->fetch_assoc()): ?>
                        <tr class="hover:bg-cream">
                            <td class="px-5 py-3 font-semibold text-stone-800"><?= e($i['book_title']) ?></td>
                            <td class="px-5 py-3"><?= e($i['user_name']) ?></td>
                            <td class="px-5 py-3 text-stone-500"><?= e(date('M j, Y', strtotime($i['issue_date']))) ?></td>
                            <td class="px-5 py-3 text-stone-500"><?= e(date('M j, Y', strtotime($i['due_date']))) ?></td>
                            <td class="px-5 py-3">
                                <?php if ($i['status'] === 'issued'): ?>
                                    <span class="bg-gold-100 text-gold-800 text-xs font-bold px-3 py-1 rounded-full">Issued</span>
                                <?php else: ?>
                                    <span class="bg-brand-100 text-brand-800 text-xs font-bold px-3 py-1 rounded-full">Returned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="px-5 py-8 text-center text-stone-500 text-sm">No borrowing activity yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../inc/footer.php'; ?>
