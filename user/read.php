<?php
require_once __DIR__ . '/../auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$book = $conn->query("SELECT b.*, c.name AS category_name FROM books b LEFT JOIN categories c ON b.category_id=c.id WHERE b.id=$id")->fetch_assoc();
if (!$book) {
    flash('Book not found.', 'error');
    redirect('/books.php');
}

$paragraphs = array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', trim((string)$book['content']))), 'strlen'));
$perPage = 6;
$totalPages = max(1, (int)ceil(count($paragraphs) / $perPage));
$page = max(1, (int)($_GET['page'] ?? 1));
$page = min($page, $totalPages);

$start = ($page - 1) * $perPage;
$currentParagraphs = array_slice($paragraphs, $start, $perPage);

$hasCover = !empty($book['cover']) && file_exists(APP_ROOT . '/' . $book['cover']);

$title = $book['title'];
$active = '';
include __DIR__ . '/../inc/header.php';
?>

<section class="max-w-4xl mx-auto px-4 py-8">
    <!-- Reader top bar -->
    <div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
        <a href="javascript:history.back()" class="text-sm font-bold text-stone-600 hover:text-brand-700 transition">← Back</a>
        <a href="<?= BASE_URL ?>/books.php" class="text-sm font-bold text-brand-700 hover:underline">Browse all books</a>
    </div>

    <!-- Book header -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-6 flex flex-col sm:flex-row gap-5 mb-6">
        <div class="w-28 h-40 rounded-xl overflow-hidden shrink-0">
            <?php if ($hasCover): ?>
                <img src="<?= e(BASE_URL . '/' . $book['cover']) ?>" alt="<?= e($book['title']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <div class="book-cover w-full h-full text-white">
                    <span class="font-display text-3xl font-bold"><?= e(strtoupper(mb_substr($book['title'], 0, 2))) ?></span>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex-1 min-w-0">
            <h1 class="font-display text-2xl md:text-3xl font-bold text-stone-800"><?= e($book['title']) ?></h1>
            <p class="text-stone-500 mt-1">by <span class="font-semibold text-stone-700"><?= e($book['author']) ?></span></p>
            <div class="flex flex-wrap gap-2 mt-3">
                <span class="bg-brand-100 text-brand-800 text-xs font-bold px-3 py-1 rounded-full"><?= e($book['category_name'] ?? 'General') ?></span>
                <?php if (!empty($book['isbn'])): ?><span class="bg-stone-100 text-stone-600 text-xs font-semibold px-3 py-1 rounded-full">ISBN <?= e($book['isbn']) ?></span><?php endif; ?>
                <?php if (!empty($book['description'])): ?>
                    <span class="bg-gold-100 text-gold-800 text-xs font-semibold px-3 py-1 rounded-full">Online reader</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($book['description'])): ?>
                <p class="text-sm text-stone-600 mt-3 leading-relaxed"><?= e($book['description']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (count($paragraphs) === 0): ?>
        <div class="bg-white rounded-2xl border border-dashed border-stone-300 py-16 text-center">
            <div class="text-5xl mb-3">📄</div>
            <p class="text-stone-500 font-semibold">Full text for this book is not available yet.</p>
        </div>
    <?php else: ?>
        <!-- Reading progress -->
        <div class="mb-4">
            <div class="flex items-center justify-between text-xs font-bold text-stone-500 mb-1">
                <span>Page <?= $page ?> of <?= $totalPages ?></span>
                <span><?= round(($page / $totalPages) * 100) ?>% read</span>
            </div>
            <div class="h-2 bg-stone-200 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-brand-500 to-gold-400 rounded-full transition-all duration-300" style="width: <?= ($page / $totalPages) * 100 ?>%"></div>
            </div>
        </div>

        <!-- Reader sheet -->
        <div class="reader-sheet rounded-2xl px-6 py-10 md:px-14 md:py-14">
            <div class="reader-prose" style="max-width: 65ch; margin: 0 auto;">
                <?php foreach ($currentParagraphs as $p): ?>
                    <p><?= nl2br(e($p)) ?></p>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Reader navigation -->
        <div class="flex items-center justify-between mt-6">
            <?php if ($page > 1): ?>
                <a href="?id=<?= $id ?>&page=<?= $page - 1 ?>" class="bg-white border border-stone-200 hover:border-brand-300 hover:bg-brand-50 text-stone-700 font-bold px-6 py-3 rounded-xl transition">← Previous</a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
            <span class="text-sm font-bold text-stone-500">Page <?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?id=<?= $id ?>&page=<?= $page + 1 ?>" class="bg-brand-600 hover:bg-brand-700 text-white font-bold px-6 py-3 rounded-xl transition">Next →</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/books.php" class="bg-gold-400 hover:bg-gold-500 text-stone-900 font-bold px-6 py-3 rounded-xl transition">Done 🎉</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../inc/footer.php'; ?>
