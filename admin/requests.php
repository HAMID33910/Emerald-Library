<?php
require_once __DIR__ . '/../auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reqId = (int)($_POST['req_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $req = $conn->query("SELECT * FROM borrow_requests WHERE id=$reqId")->fetch_assoc();
    if (!$req) {
        flash('Request not found.', 'error');
    } elseif ($req['status'] !== 'pending') {
        flash('This request has already been processed.', 'warning');
    } elseif ($action === 'approve') {
        $book = $conn->query("SELECT * FROM books WHERE id=" . (int)$req['book_id'])->fetch_assoc();
        if ($book && (int)$book['available_copies'] > 0) {
            $conn->begin_transaction();
            $issueDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime('+14 days'));
            $upd = $conn->prepare("UPDATE borrow_requests SET status='approved' WHERE id=?");
            $upd->bind_param('i', $reqId);
            $upd->execute();
            $ins = $conn->prepare("INSERT INTO issues (user_id, book_id, request_id, issue_date, due_date) VALUES (?,?,?,?,?)");
            $ins->bind_param('iiiss', $req['user_id'], $req['book_id'], $reqId, $issueDate, $dueDate);
            $ins->execute();
            $dec = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id=? AND available_copies > 0");
            $dec->bind_param('i', $req['book_id']);
            $dec->execute();
            $conn->commit();
            flash('Request approved — book issued to the member.');
        } else {
            $upd = $conn->prepare("UPDATE borrow_requests SET status='rejected' WHERE id=?");
            $upd->bind_param('i', $reqId);
            $upd->execute();
            flash('No copies available. Request rejected.', 'warning');
        }
    } elseif ($action === 'reject') {
        $upd = $conn->prepare("UPDATE borrow_requests SET status='rejected' WHERE id=?");
        $upd->bind_param('i', $reqId);
        $upd->execute();
        flash('Request rejected.');
    }
    redirect('/admin/requests.php');
}

$filter = $_GET['status'] ?? 'pending';
$allowed = ['all', 'pending', 'approved', 'rejected', 'returned'];
if (!in_array($filter, $allowed, true)) $filter = 'pending';

$where = $filter === 'all' ? '1=1' : "r.status='" . $conn->real_escape_string($filter) . "'";
$sql = "SELECT r.*, u.name AS user_name, u.email, b.title AS book_title, b.cover
        FROM borrow_requests r
        JOIN users u ON u.id=r.user_id
        JOIN books b ON b.id=r.book_id
        WHERE $where
        ORDER BY FIELD(r.status,'pending','approved','returned','rejected'), r.requested_at DESC";
$requests = $conn->query($sql);

$counts = [];
$res = $conn->query("SELECT status, COUNT(*) AS c FROM borrow_requests GROUP BY status");
while ($r = $res->fetch_assoc()) $counts[$r['status']] = (int)$r['c'];
$counts['all'] = array_sum($counts);

$title = 'Borrow Requests';
$active = 'requests';
include __DIR__ . '/../inc/header.php';

$statusBadge = [
    'pending' => ['bg-gold-100 text-gold-800', 'Pending'],
    'approved' => ['bg-brand-100 text-brand-800', 'Approved'],
    'rejected' => ['bg-rose-100 text-rose-700', 'Rejected'],
    'returned' => ['bg-stone-200 text-stone-600', 'Returned'],
];
$tabs = ['pending' => 'Pending', 'approved' => 'Approved', 'returned' => 'Returned', 'rejected' => 'Rejected', 'all' => 'All'];
?>

<section class="bg-gradient-to-r from-brand-800 to-brand-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <h1 class="font-display text-3xl font-bold">Borrow Requests</h1>
        <p class="text-brand-100 mt-1">Review member requests and approve them to issue the book.</p>
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
                        <th class="px-5 py-3 font-bold">Member</th>
                        <th class="px-5 py-3 font-bold">Book</th>
                        <th class="px-5 py-3 font-bold">Requested</th>
                        <th class="px-5 py-3 font-bold">Status</th>
                        <th class="px-5 py-3 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php if ($requests && $requests->num_rows): while ($r = $requests->fetch_assoc()): ?>
                    <tr class="hover:bg-cream">
                        <td class="px-5 py-3">
                            <p class="font-bold text-stone-800"><?= e($r['user_name']) ?></p>
                            <p class="text-xs text-stone-500"><?= e($r['email']) ?></p>
                        </td>
                        <td class="px-5 py-3 font-semibold text-stone-800"><?= e($r['book_title']) ?></td>
                        <td class="px-5 py-3 text-stone-500"><?= e(date('M j, Y g:i A', strtotime($r['requested_at']))) ?></td>
                        <td class="px-5 py-3">
                            <?php $b = $statusBadge[$r['status']] ?? ['bg-stone-100 text-stone-600', ucfirst($r['status'])]; ?>
                            <span class="<?= $b[0] ?> text-xs font-bold px-3 py-1 rounded-full"><?= $b[1] ?></span>
                        </td>
                        <td class="px-5 py-3">
                            <?php if ($r['status'] === 'pending'): ?>
                                <div class="flex items-center justify-end gap-2">
                                    <form method="post">
                                        <input type="hidden" name="req_id" value="<?= (int)$r['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 px-4 py-2 rounded-lg transition">✓ Approve</button>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="req_id" value="<?= (int)$r['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 px-4 py-2 rounded-lg transition">✕ Reject</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="text-xs text-stone-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="px-5 py-10 text-center text-stone-500">No requests in this view.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../inc/footer.php'; ?>
