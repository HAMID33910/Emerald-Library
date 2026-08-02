<?php
require_once __DIR__ . '/../auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'return') {
    $issueId = (int)($_POST['issue_id'] ?? 0);
    $issue = $conn->query("SELECT * FROM issues WHERE id=$issueId")->fetch_assoc();
    if (!$issue) {
        flash('Issue record not found.', 'error');
    } elseif ($issue['status'] === 'returned') {
        flash('This book was already returned.', 'warning');
    } else {
        $today = new DateTime();
        $due = new DateTime($issue['due_date']);
        $fine = 0.00;
        if ($today > $due) {
            $days = (int)$today->diff($due)->days;
            $fine = $days * daily_fine();
        }
        $conn->begin_transaction();
        $upd = $conn->prepare("UPDATE issues SET status='returned', return_date=?, fine=? WHERE id=?");
        $returnDate = $today->format('Y-m-d');
        $upd->bind_param('sdi', $returnDate, $fine, $issueId);
        $upd->execute();
        $inc = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id=?");
        $inc->bind_param('i', $issue['book_id']);
        $inc->execute();
        if (!empty($issue['request_id'])) {
            $req = $conn->prepare("UPDATE borrow_requests SET status='returned' WHERE id=?");
            $req->bind_param('i', $issue['request_id']);
            $req->execute();
        }
        $conn->commit();

        if ($fine > 0) {
            flash("Book returned. Late fee of $" . number_format($fine, 2) . " recorded.", 'warning');
        } else {
            flash('Book returned successfully.');
        }
    }
    redirect('/admin/issues.php');
}

$filter = $_GET['status'] ?? 'issued';
$where = in_array($filter, ['issued', 'returned'], true) ? "i.status='$filter'" : '1=1';

$sql = "SELECT i.*, u.name AS user_name, u.email, b.title AS book_title
        FROM issues i
        JOIN users u ON u.id=i.user_id
        JOIN books b ON b.id=i.book_id
        WHERE $where
        ORDER BY FIELD(i.status,'issued','returned'), i.due_date ASC";
$issues = $conn->query($sql);

$counts = [];
$res = $conn->query("SELECT status, COUNT(*) AS c FROM issues GROUP BY status");
while ($r = $res->fetch_assoc()) $counts[$r['status']] = (int)$r['c'];
$counts['all'] = array_sum($counts);

$title = 'Issues & Returns';
$active = 'issues';
include __DIR__ . '/../inc/header.php';
$tabs = ['issued' => 'Issued Now', 'returned' => 'Returned', 'all' => 'All'];
$today = new DateTime();
?>

<section class="bg-gradient-to-r from-brand-800 to-brand-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <h1 class="font-display text-3xl font-bold">Issues &amp; Returns</h1>
        <p class="text-brand-100 mt-1">Track borrowed books, due dates and late fees.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 mt-8">
    <div class="flex flex-wrap gap-2 mb-6">
        <?php foreach ($tabs as $key => $label): ?>
            <a href="?status=<?= $key ?>" class="px-4 py-2 rounded-xl text-sm font-bold transition <?= $filter === $key ? 'bg-brand-600 text-white shadow' : 'bg-white border border-stone-200 text-stone-600 hover:bg-brand-50' ?>">
                <?= $label ?> <span class="opacity-70">(<?= $counts[$key] ?? 0 ?>)</span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-500">
                        <th class="px-5 py-3 font-bold">Book</th>
                        <th class="px-5 py-3 font-bold">Member</th>
                        <th class="px-5 py-3 font-bold">Issued</th>
                        <th class="px-5 py-3 font-bold">Due</th>
                        <th class="px-5 py-3 font-bold">Returned</th>
                        <th class="px-5 py-3 font-bold">Fine</th>
                        <th class="px-5 py-3 font-bold">Status</th>
                        <th class="px-5 py-3 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php if ($issues && $issues->num_rows): while ($i = $issues->fetch_assoc()): 
                        $dueDate = new DateTime($i['due_date']);
                        $overdue = $i['status'] === 'issued' && $today > $dueDate;
                        $daysLate = $overdue ? (int)$today->diff($dueDate)->days : 0;
                    ?>
                    <tr class="hover:bg-cream <?= $overdue ? 'bg-rose-50/50' : '' ?>">
                        <td class="px-5 py-3 font-bold text-stone-800"><?= e($i['book_title']) ?></td>
                        <td class="px-5 py-3">
                            <p class="font-semibold text-stone-700"><?= e($i['user_name']) ?></p>
                            <p class="text-xs text-stone-500"><?= e($i['email']) ?></p>
                        </td>
                        <td class="px-5 py-3 text-stone-500"><?= e(date('M j, Y', strtotime($i['issue_date']))) ?></td>
                        <td class="px-5 py-3">
                            <?= e(date('M j, Y', strtotime($i['due_date']))) ?>
                            <?php if ($overdue): ?>
                                <span class="block text-xs font-bold text-rose-600 mt-1">⏰ <?= $daysLate ?> day<?= $daysLate > 1 ? 's' : '' ?> late</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-stone-500"><?= $i['return_date'] ? e(date('M j, Y', strtotime($i['return_date']))) : '—' ?></td>
                        <td class="px-5 py-3 font-semibold"><?= (float)$i['fine'] > 0 ? '$' . number_format($i['fine'], 2) : '—' ?></td>
                        <td class="px-5 py-3">
                            <?php if ($i['status'] === 'issued'): ?>
                                <span class="<?= $overdue ? 'bg-rose-100 text-rose-700' : 'bg-gold-100 text-gold-800' ?> text-xs font-bold px-3 py-1 rounded-full"><?= $overdue ? 'Overdue' : 'Issued' ?></span>
                            <?php else: ?>
                                <span class="bg-brand-100 text-brand-800 text-xs font-bold px-3 py-1 rounded-full">Returned</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <?php if ($i['status'] === 'issued'): ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="return">
                                    <input type="hidden" name="issue_id" value="<?= (int)$i['id'] ?>">
                                    <button type="submit" class="text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 px-4 py-2 rounded-lg transition">↩ Return</button>
                                </form>
                            <?php else: ?>
                                <span class="text-xs text-stone-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="8" class="px-5 py-10 text-center text-stone-500">No records in this view.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../inc/footer.php'; ?>
