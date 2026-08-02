<?php
// =============================================
//  BOOK CARD PARTIAL
//  Expects $b: row (id,title,author,category_name,total_copies,available_copies,cover,description)
// =============================================
function book_card($b, $showActions = true) {
    $available = (int)$b['available_copies'];
    $hasCover = !empty($b['cover']) && file_exists(APP_ROOT . '/' . $b['cover']);
    $words = preg_split('/\s+/', trim((string)$b['title']));
    $initials = '';
    foreach ($words as $w) {
        if (mb_strlen($initials) < 2) $initials .= mb_substr($w, 0, 1);
    }
    $initials = strtoupper($initials ?: 'BK');
    $alt = ((int)$b['id'] % 3) === 0 ? 'book-cover-alt' : (((int)$b['id'] % 3) === 1 ? 'book-cover-alt2' : '');

    ob_start(); ?>
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl border border-stone-100 overflow-hidden transition flex flex-col">
        <a href="<?= BASE_URL ?>/user/read.php?id=<?= (int)$b['id'] ?>" class="block h-44 overflow-hidden">
            <?php if ($hasCover): ?>
                <img src="<?= e(BASE_URL . '/' . $b['cover']) ?>" alt="<?= e($b['title']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <div class="book-cover <?= $alt ?> w-full h-full text-white">
                    <span class="font-display text-4xl font-bold tracking-wide drop-shadow"><?= e($initials) ?></span>
                </div>
            <?php endif; ?>
        </a>
        <div class="p-4 flex flex-col flex-1">
            <div class="flex items-start justify-between gap-2 mb-1">
                <h3 class="font-display font-bold text-stone-800 leading-snug"><?= e($b['title']) ?></h3>
            </div>
            <p class="text-sm text-stone-500">by <?= e($b['author']) ?></p>
            <div class="flex items-center gap-2 mt-2">
                <span class="bg-brand-100 text-brand-800 text-xs font-bold px-2.5 py-0.5 rounded-full"><?= e($b['category_name'] ?? 'General') ?></span>
                <?php if ($available > 0): ?>
                    <span class="bg-brand-50 text-brand-700 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-brand-200"><?= $available ?> copy<?= $available > 1 ? 's' : '' ?> available</span>
                <?php else: ?>
                    <span class="bg-rose-50 text-rose-700 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-rose-200">Out of stock</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($b['description'])): ?>
                <p class="text-sm text-stone-600 mt-2 line-clamp-2 flex-1"><?= e(mb_strimwidth($b['description'], 0, 110, '…')) ?></p>
            <?php else: ?>
                <div class="flex-1"></div>
            <?php endif; ?>
            <?php if ($showActions): ?>
                <div class="flex gap-2 mt-4">
                    <a href="<?= BASE_URL ?>/user/read.php?id=<?= (int)$b['id'] ?>" class="flex-1 text-center bg-gold-400 hover:bg-gold-500 text-stone-900 text-sm font-bold py-2 rounded-xl transition">Read Online</a>
                    <?php if (is_logged_in()): ?>
                        <?php if ($available > 0): ?>
                            <form method="post" action="<?= BASE_URL ?>/books.php">
                                <input type="hidden" name="action" value="borrow">
                                <input type="hidden" name="book_id" value="<?= (int)$b['id'] ?>">
                                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">Borrow</button>
                            </form>
                        <?php else: ?>
                            <button disabled class="bg-stone-200 text-stone-400 text-sm font-bold px-4 py-2 rounded-xl cursor-not-allowed">Unavailable</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">Borrow</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
