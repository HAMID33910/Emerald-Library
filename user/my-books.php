<?php
require_once __DIR__ . '/../auth.php';
require_login();
$uid = (int)$_SESSION['user_id'];

$issues = $conn->query("SELECT i.*, b.title, b.author FROM issues i JOIN books b ON b.id=i.book_id WHERE i.user_id=$uid ORDER BY FIELD(i.status,'issued','returned'), i.issue_date DESC");
$requests = $conn->query("SELECT r.*, b.title, b.author FROM borrow_requests r JOIN books b ON b.id=r.book_id WHERE r.user_id=$uid ORDER BY r.requested_at DESC");

$statusBadge = [
    'pending' => ['bg-gold-100 text-gold-800', 'Pending'],
    'approved' => ['bg-brand-100 text-brand-800', 'Approved'],
    'rejected' => ['bg-rose-100 text-rose-700', 'Rejected'],
    'returned' => ['bg-stone-200 text-stone-600', 'Returned'],
    'cancelled' => ['bg-stone-200 text-stone-600', 'Cancelled'],
];
$today = new DateTime();

$title = 'My Books';
$active = 'mybooks';
include __DIR__ . '/../inc/header.php';
?>

<section class="bg-gradient-to-r from-brand-800 to-brand-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <h1 class="font-display text-3xl font-bold">My Books</h1>
        <p class="text-brand-100 mt-1">Everything you have borrowed and requested.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 mt-8 space-y-8">
    <!-- Borrowing history -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-stone-100">
            <h2 class="font-display font-bold text-stone-800">📖 Borrowing History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-500">
                        <th class="px-5 py-3 font-bold">Book</th>
                        <th class="px-5 py-3 font-bold">Issued</th>
                        <th class="px-5 py-3 font-bold">Due</th>
                        <th class="px-5 py-3 font-bold">Returned</th>
                        <th class="px-5 py-3 font-bold">Fine</th>
                        <th class="px-5 py-3 font-bold">Status</th>
                        <th class="px-5 py-3 font-bold text-right">Read</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php if ($issues && $issues->num_rows): while ($i = $issues->fetch_assoc()):
                        $due = new DateTime($i['due_date']);
                        $overdue = $i['status'] === 'issued' && $today > $due;
                    ?>
                    <tr class="hover:bg-cream">
                        <td class="px-5 py-3">
                            <p class="font-bold text-stone-800"><?= e($i['title']) ?></p>
                            <p class="text-xs text-stone-500">by <?= e($i['author']) ?></p>
                        </td>
                        <td class="px-5 py-3 text-stone-500"><?= e(date('M j, Y', strtotime($i['issue_date']))) ?></td>
                        <td class="px-5 py-3 <?= $overdue ? 'text-rose-600 font-bold' : 'text-stone-500' ?>"><?= e(date('M j, Y', strtotime($i['due_date']))) ?></td>
                        <td class="px-5 py-3 text-stone-500"><?= $i['return_date'] ? e(date('M j, Y', strtotime($i['return_date']))) : '—' ?></td>
                        <td class="px-5 py-3 font-semibold"><?= (float)$i['fine'] > 0 ? '$' . number_format($i['fine'], 2) : '—' ?></td>
                        <td class="px-5 py-3">
                            <?php if ($i['status'] === 'issued'): ?>
                                <span class="<?= $overdue ? 'bg-rose-100 text-rose-700' : 'bg-gold-100 text-gold-800' ?> text-xs font-bold px-3 py-1 rounded-full"><?= $overdue ? 'Overdue' : 'With you' ?></span>
                            <?php else: ?>
                                <span class="bg-brand-100 text-brand-800 text-xs font-bold px-3 py-1 rounded-full">Returned</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="<?= BASE_URL ?>/user/read.php?id=<?= (int)$i['book_id'] ?>" class="text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition">Read</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" class="px-5 py-10 text-center text-stone-500">No borrowing history yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Requests -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-stone-100">
            <h2 class="font-display font-bold text-stone-800">🕐 Borrow Requests</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-500">
                        <th class="px-5 py-3 font-bold">Book</th>
                        <th class="px-5 py-3 font-bold">Requested</th>
                        <th class="px-5 py-3 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php if ($requests && $requests->num_rows): while ($r = $requests->fetch_assoc()): ?>
                    <tr class="hover:bg-cream">
                        <td class="px-5 py-3">
                            <p class="font-bold text-stone-800"><?= e($r['title']) ?></p>
                            <p class="text-xs text-stone-500">by <?= e($r['author']) ?></p>
                        </td>
                        <td class="px-5 py-3 text-stone-500"><?= e(date('M j, Y g:i A', strtotime($r['requested_at']))) ?></td>
                        <td class="px-5 py-3">
                            <?php $b = $statusBadge[$r['status']] ?? ['bg-stone-100 text-stone-600', ucfirst($r['status'])]; ?>
                            <span class="<?= $b[0] ?> text-xs font-bold px-3 py-1 rounded-full"><?= $b[1] ?></span>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="3" class="px-5 py-10 text-center text-stone-500">No requests yet. <a href="<?= BASE_URL ?>/books.php" class="text-brand-700 font-bold hover:underline">Find a book</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../inc/footer.php'; ?>
