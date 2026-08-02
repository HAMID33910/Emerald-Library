<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/inc/book_card.php';

$totalBooks = (int)$conn->query("SELECT COUNT(*) AS c FROM books")->fetch_assoc()['c'];
$totalMembers = (int)$conn->query("SELECT COUNT(*) AS c FROM users WHERE role='user'")->fetch_assoc()['c'];
$totalIssued = (int)$conn->query("SELECT COUNT(*) AS c FROM issues WHERE status='issued'")->fetch_assoc()['c'];
$categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM books b WHERE b.category_id=c.id) AS book_count FROM categories c ORDER BY c.name");
$featured = $conn->query("SELECT b.*, c.name AS category_name FROM books b LEFT JOIN categories c ON b.category_id=c.id ORDER BY b.id DESC LIMIT 8");

$title = 'Home';
$active = 'home';
include __DIR__ . '/inc/header.php';
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-gold-600 text-white">
    <div class="absolute inset-0 bg-cover bg-center opacity-60" style="background-image:url('https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1920&q=80');" aria-hidden="true"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-brand-900/70 via-brand-800/60 to-gold-900/50" aria-hidden="true"></div>
    <div class="max-w-7xl mx-auto px-4 py-20 relative">
        <div class="max-w-2xl">
            <span class="inline-block bg-white/15 text-gold-200 text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4">Welcome to Emerald Library</span>
            <h1 class="font-display text-4xl md:text-5xl font-bold leading-tight mb-4">
                Discover your next<br>favourite book
            </h1>
            <p class="text-brand-50 text-lg mb-8">Browse our shelves, borrow with one click, and read entire books online — all from the comfort of your home.</p>
            <form method="get" action="<?= BASE_URL ?>/books.php" class="flex gap-2 max-w-lg">
                <input type="text" name="q" placeholder="Search by title or author…" class="flex-1 rounded-xl border-0 px-5 py-3 text-stone-800 shadow-lg focus:outline-none focus:ring-4 focus:ring-gold-300/60">
                <button type="submit" class="bg-gold-500 hover:bg-gold-400 text-stone-900 font-bold px-6 py-3 rounded-xl transition shadow-lg">Search</button>
            </form>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="max-w-7xl mx-auto px-4 -mt-8 relative z-10">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <?php
        $stats = [
            ['label' => 'Books in collection', 'value' => $totalBooks, 'icon' => '📖'],
            ['label' => 'Active members', 'value' => $totalMembers, 'icon' => '👥'],
            ['label' => 'Books currently issued', 'value' => $totalIssued, 'icon' => '📦'],
        ];
        foreach ($stats as $s): ?>
        <div class="bg-white rounded-2xl shadow-lg border border-stone-100 p-6 flex items-center gap-4 fade-up">
            <span class="text-4xl"><?= $s['icon'] ?></span>
            <div>
                <div class="text-3xl font-extrabold text-brand-700"><?= $s['value'] ?></div>
                <div class="text-sm text-stone-500 font-semibold"><?= $s['label'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CATEGORIES -->
<section class="max-w-7xl mx-auto px-4 mt-14">
    <div class="flex items-end justify-between mb-6">
        <h2 class="font-display text-2xl md:text-3xl font-bold text-stone-800">Browse by Category</h2>
        <a href="<?= BASE_URL ?>/books.php" class="text-brand-700 hover:text-brand-800 text-sm font-bold">View all →</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <?php if ($categories && $categories->num_rows): ?>
            <?php while ($cat = $categories->fetch_assoc()): ?>
            <a href="<?= BASE_URL ?>/books.php?cat=<?= (int)$cat['id'] ?>" class="bg-white hover:bg-brand-50 border border-stone-100 hover:border-brand-200 rounded-2xl p-5 text-center transition group">
                <div class="text-3xl mb-2">📂</div>
                <div class="font-bold text-stone-800 group-hover:text-brand-700"><?= e($cat['name']) ?></div>
                <div class="text-xs text-stone-500 mt-1"><?= (int)$cat['book_count'] ?> books</div>
            </a>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<!-- FEATURED BOOKS -->
<section class="max-w-7xl mx-auto px-4 mt-14">
    <div class="flex items-end justify-between mb-6">
        <h2 class="font-display text-2xl md:text-3xl font-bold text-stone-800">Newly Added</h2>
        <a href="<?= BASE_URL ?>/books.php" class="text-brand-700 hover:text-brand-800 text-sm font-bold">Browse the full shelf →</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if ($featured && $featured->num_rows): ?>
            <?php while ($book = $featured->fetch_assoc()) echo book_card($book); ?>
        <?php else: ?>
            <p class="col-span-full text-stone-500">No books yet.</p>
        <?php endif; ?>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="bg-brand-50/70 border-y border-brand-100 mt-16">
    <div class="max-w-7xl mx-auto px-4 py-14">
        <div class="text-center mb-10">
            <span class="text-3xl">💬</span>
            <h2 class="font-display text-2xl md:text-3xl font-bold text-stone-800 mt-2">What our readers say</h2>
            <p class="text-stone-500 mt-2">Real words from the people who call Emerald Library home.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            $testimonials = [
                ['quote' => 'I discovered my all-time favourite author here. The search is fast and borrowing a book takes seconds — I never want to use another library.', 'name' => 'Amara Wilson', 'role' => 'Student', 'initials' => 'AW'],
                ['quote' => 'Being able to read entire books online from home changed everything for me. The collection is surprisingly big for a community library.', 'name' => 'Dev Patel', 'role' => 'Teacher', 'initials' => 'DP'],
                ['quote' => 'As a busy parent I never have time to visit, but with the online requests and doorstep updates it feels like the library comes to me.', 'name' => 'Sofia Nguyen', 'role' => 'Parent', 'initials' => 'SN'],
            ];
            foreach ($testimonials as $t): ?>
            <div class="bg-white rounded-2xl shadow-lg border border-stone-100 p-6 flex flex-col fade-up">
                <div class="text-gold-500 text-lg tracking-widest mb-3">★★★★★</div>
                <p class="text-stone-600 italic leading-relaxed flex-1">“<?= e($t['quote']) ?>”</p>
                <div class="flex items-center gap-3 mt-5 pt-4 border-t border-stone-100">
                    <span class="w-10 h-10 rounded-full bg-brand-700 text-gold-300 font-extrabold flex items-center justify-center"><?= e($t['initials']) ?></span>
                    <div>
                        <div class="font-bold text-stone-800 text-sm"><?= e($t['name']) ?></div>
                        <div class="text-xs text-stone-500 font-semibold"><?= e($t['role']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/inc/footer.php'; ?>
