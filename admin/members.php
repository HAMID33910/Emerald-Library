<?php
require_once __DIR__ . '/../auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $uid = (int)($_POST['uid'] ?? 0);
    if ($uid === (int)$_SESSION['user_id']) {
        flash('You cannot delete your own account.', 'error');
    } else {
        $del = $conn->prepare("DELETE FROM users WHERE id=? AND role='user'");
        $del->bind_param('i', $uid);
        $del->execute();
        flash('Member removed.');
    }
    redirect('/admin/members.php');
}

$members = $conn->query("
    SELECT u.id, u.name, u.email, u.created_at,
           (SELECT COUNT(*) FROM issues i WHERE i.user_id=u.id AND i.status='issued') AS issued_now,
           (SELECT COUNT(*) FROM issues i WHERE i.user_id=u.id) AS total_borrowed,
           (SELECT COALESCE(SUM(i.fine),0) FROM issues i WHERE i.user_id=u.id AND i.status='returned') AS total_fines
    FROM users u
    WHERE u.role='user'
    ORDER BY u.created_at DESC");

$title = 'Members';
$active = 'members';
include __DIR__ . '/../inc/header.php';
?>

<section class="bg-gradient-to-r from-brand-800 to-brand-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <h1 class="font-display text-3xl font-bold">Library Members</h1>
        <p class="text-brand-100 mt-1">All registered readers and their borrowing history.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 mt-8">
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-500">
                        <th class="px-5 py-3 font-bold">Member</th>
                        <th class="px-5 py-3 font-bold">Joined</th>
                        <th class="px-5 py-3 font-bold">Books with now</th>
                        <th class="px-5 py-3 font-bold">Total borrowed</th>
                        <th class="px-5 py-3 font-bold">Total fines</th>
                        <th class="px-5 py-3 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php if ($members && $members->num_rows): while ($m = $members->fetch_assoc()): ?>
                    <tr class="hover:bg-cream">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-full bg-gold-400 text-brand-900 font-extrabold flex items-center justify-center shrink-0"><?= e(strtoupper(mb_substr($m['name'], 0, 1))) ?></span>
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-800 truncate"><?= e($m['name']) ?></p>
                                    <p class="text-xs text-stone-500 truncate"><?= e($m['email']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-stone-500"><?= e(date('M j, Y', strtotime($m['created_at']))) ?></td>
                        <td class="px-5 py-3">
                            <?php if ((int)$m['issued_now'] > 0): ?>
                                <span class="bg-gold-100 text-gold-800 text-xs font-bold px-3 py-1 rounded-full"><?= (int)$m['issued_now'] ?></span>
                            <?php else: ?>
                                <span class="text-stone-400">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 font-semibold"><?= (int)$m['total_borrowed'] ?></td>
                        <td class="px-5 py-3 font-semibold"><?= (float)$m['total_fines'] > 0 ? '$' . number_format($m['total_fines'], 2) : '—' ?></td>
                        <td class="px-5 py-3 text-right">
                            <form method="post" data-confirm="Remove this member? Their history will be deleted." class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="uid" value="<?= (int)$m['id'] ?>">
                                <button type="submit" class="text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 px-4 py-2 rounded-lg transition">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" class="px-5 py-10 text-center text-stone-500">No members yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../inc/footer.php'; ?>
