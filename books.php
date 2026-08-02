<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/inc/book_card.php';

// ---- Handle borrow request ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'borrow') {
    require_login();
    $bookId = (int)($_POST['book_id'] ?? 0);
    $uid = (int)$_SESSION['user_id'];

    $book = $conn->query("SELECT * FROM books WHERE id=$bookId")->fetch_assoc();
    if (!$book) {
        flash('Book not found.', 'error');
    } elseif ((int)$book['available_copies'] <= 0) {
        flash('Sorry, this book is currently out of stock.', 'warning');
    } else {
        $exists = $conn->prepare("SELECT id FROM borrow_requests WHERE user_id=? AND book_id=? AND status IN ('pending','approved')");
        $exists->bind_param('ii', $uid, $bookId);
        $exists->execute();
        $exists->store_result();
        if ($exists->num_rows > 0) {
            flash('You already have a pending request for this book.', 'warning');
        } else {
            $ins = $conn->prepare("INSERT INTO borrow_requests (user_id, book_id, status) VALUES (?,?, 'pending')");
            $ins->bind_param('ii', $uid, $bookId);
            $ins->execute();
            flash('Borrow request sent! The librarian will review it shortly.', 'success');
        }
        $exists->close();
    }
}

// ---- Filters ----
$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = ['1=1'];
$params = [];
$types = '';

if ($q !== '') {
    $where[] = '(b.title LIKE ? OR b.author LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like; $params[] = $like;
    $types .= 'ss';
}
if ($cat > 0) {
    $where[] = 'b.category_id = ?';
    $params[] = $cat;
    $types .= 'i';
}
$whereSql = implode(' AND ', $where);

$countStmt = $conn->prepare("SELECT COUNT(*) AS c FROM books b WHERE $whereSql");
if ($types !== '') $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalBooks = (int)$countStmt->get_result()->fetch_assoc()['c'];
$countStmt->close();

$totalPages = max(1, (int)ceil($totalBooks / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "SELECT b.*, c.name AS category_name
        FROM books b LEFT JOIN categories c ON b.category_id=c.id
        WHERE $whereSql
        ORDER BY b.title ASC
        LIMIT $perPage OFFSET $offset";
$stmt = $conn->prepare($sql);
if ($types !== '') $stmt->bind_param($types, ...$params);
$stmt->execute();
$books = $stmt->get_result();

$cats = $conn->query("SELECT * FROM categories ORDER BY name");

$title = 'Browse Books';
$active = 'books';
include __DIR__ . '/inc/header.php';
?>

<section class="bg-gradient-to-r from-brand-800 to-brand-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <h1 class="font-display text-3xl font-bold">Browse the Library</h1>
        <p class="text-brand-100 mt-1">Find your next great read and borrow it in one click.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 mt-8">
    <!-- Filter bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 flex flex-col md:flex-row gap-3 items-center mb-6">
        <form method="get" action="<?= BASE_URL ?>/books.php" class="flex gap-2 flex-1 w-full md:w-auto">
            <?php if ($cat > 0): ?><input type="hidden" name="cat" value="<?= $cat ?>"><?php endif; ?>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search title or author…" class="flex-1 rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition">Search</button>
        </form>
        <form method="get" action="<?= BASE_URL ?>/books.php" class="w-full md:w-56">
            <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
            <select name="cat" onchange="this.form.submit()" class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
                <option value="0">All categories</option>
                <?php while ($c = $cats->fetch_assoc()): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>

    <!-- Results -->
    <?php if ($books && $books->num_rows): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php while ($book = $books->fetch_assoc()) echo book_card($book); ?>
        </div>
    <?php else: ?>
        <div class="bg-white border border-dashed border-stone-300 rounded-2xl py-16 text-center">
            <div class="text-5xl mb-3">🔍</div>
            <p class="text-stone-500 font-semibold">No books match your search.</p>
            <a href="<?= BASE_URL ?>/books.php" class="inline-block mt-3 text-brand-700 font-bold text-sm hover:underline">Clear filters</a>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-2 mt-10">
        <?php
        $qs = 'q=' . urlencode($q) . '&cat=' . $cat;
        if ($page > 1): ?>
            <a href="?<?= $qs ?>&page=<?= $page - 1 ?>" class="px-4 py-2 rounded-xl bg-white border border-stone-200 text-sm font-bold text-stone-700 hover:bg-brand-50 transition">‹ Prev</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?<?= $qs ?>&page=<?= $i ?>" class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold transition <?= $i === $page ? 'bg-brand-600 text-white' : 'bg-white border border-stone-200 text-stone-700 hover:bg-brand-50' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?<?= $qs ?>&page=<?= $page + 1 ?>" class="px-4 py-2 rounded-xl bg-white border border-stone-200 text-sm font-bold text-stone-700 hover:bg-brand-50 transition">Next ›</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/inc/footer.php'; ?>
