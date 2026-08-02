<?php
require_once __DIR__ . '/../auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $row = $conn->query("SELECT cover FROM books WHERE id=$id")->fetch_assoc();
    if ($row && !empty($row['cover'])) {
        $path = APP_ROOT . '/' . $row['cover'];
        if (is_file($path)) @unlink($path);
    }
    $del = $conn->prepare("DELETE FROM books WHERE id=?");
    $del->bind_param('i', $id);
    $del->execute();
    flash('Book deleted.');
    redirect('/admin/books.php');
}

$q = trim($_GET['q'] ?? '');
$where = '1=1';
$types = '';
$params = [];
if ($q !== '') {
    $where .= ' AND (b.title LIKE ? OR b.author LIKE ?)';
    $like = '%' . $q . '%';
    $types .= 'ss';
    $params[] = $like;
    $params[] = $like;
}

$sql = "SELECT b.*, c.name AS category_name FROM books b LEFT JOIN categories c ON b.category_id=c.id WHERE $where ORDER BY b.id DESC";
$stmt = $conn->prepare($sql);
if ($types !== '') $stmt->bind_param($types, ...$params);
$stmt->execute();
$books = $stmt->get_result();

$title = 'Manage Books';
$active = 'manage';
include __DIR__ . '/../inc/header.php';
?>

<section class="bg-gradient-to-r from-brand-800 to-brand-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">Manage Books</h1>
            <p class="text-brand-100 mt-1">Add new books, update copies, or remove titles from the shelf.</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/book-form.php" class="inline-flex items-center gap-2 bg-gold-500 hover:bg-gold-400 text-stone-900 font-bold px-5 py-3 rounded-xl transition shadow-lg">
            <span class="text-lg">＋</span> Add New Book
        </a>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 mt-8">
    <form method="get" action="<?= BASE_URL ?>/admin/books.php" class="flex gap-2 max-w-md mb-6">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search title or author…" class="flex-1 rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
        <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition">Search</button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-500">
                        <th class="px-5 py-3 font-bold">Book</th>
                        <th class="px-5 py-3 font-bold">Category</th>
                        <th class="px-5 py-3 font-bold">Copies</th>
                        <th class="px-5 py-3 font-bold">Available</th>
                        <th class="px-5 py-3 font-bold">Status</th>
                        <th class="px-5 py-3 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php if ($books && $books->num_rows): while ($b = $books->fetch_assoc()): ?>
                    <tr class="hover:bg-cream">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-14 rounded-lg bg-brand-700 text-white text-xs font-bold flex items-center justify-center shrink-0">
                                    <?= e(strtoupper(mb_substr($b['title'], 0, 2))) ?>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-800 truncate"><?= e($b['title']) ?></p>
                                    <p class="text-xs text-stone-500 truncate"><?= e($b['author']) ?><?= $b['isbn'] ? ' · ' . e($b['isbn']) : '' ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3"><span class="bg-brand-50 text-brand-700 text-xs font-bold px-2.5 py-1 rounded-full border border-brand-200"><?= e($b['category_name'] ?? 'General') ?></span></td>
                        <td class="px-5 py-3 font-semibold"><?= (int)$b['total_copies'] ?></td>
                        <td class="px-5 py-3 font-semibold"><?= (int)$b['available_copies'] ?></td>
                        <td class="px-5 py-3">
                            <?php if ((int)$b['available_copies'] > 0): ?>
                                <span class="bg-brand-100 text-brand-800 text-xs font-bold px-3 py-1 rounded-full">In stock</span>
                            <?php else: ?>
                                <span class="bg-rose-100 text-rose-700 text-xs font-bold px-3 py-1 rounded-full">Out of stock</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= BASE_URL ?>/user/read.php?id=<?= (int)$b['id'] ?>" class="text-xs font-bold text-stone-600 bg-stone-100 hover:bg-stone-200 px-3 py-1.5 rounded-lg transition" title="View">View</a>
                                <a href="<?= BASE_URL ?>/admin/book-form.php?id=<?= (int)$b['id'] ?>" class="text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition" title="Edit">Edit</a>
                                <form method="post" data-confirm="Delete this book permanently?" class="inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                    <button type="submit" class="text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" class="px-5 py-10 text-center text-stone-500">No books found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../inc/footer.php'; ?>
