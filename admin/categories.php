<?php
require_once __DIR__ . '/../auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name === '') {
            flash('Category name is required.', 'error');
        } else {
            $check = $conn->prepare("SELECT id FROM categories WHERE name=?");
            $check->bind_param('s', $name);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                flash('That category already exists.', 'warning');
            } else {
                $ins = $conn->prepare("INSERT INTO categories (name, description) VALUES (?,?)");
                $ins->bind_param('ss', $name, $desc);
                $ins->execute();
                flash('Category added.');
            }
            $check->close();
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name === '') {
            flash('Category name is required.', 'error');
        } else {
            $upd = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
            $upd->bind_param('ssi', $name, $desc, $id);
            $upd->execute();
            flash('Category updated.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $count = $conn->query("SELECT COUNT(*) AS c FROM books WHERE category_id=$id")->fetch_assoc()['c'];
        if ((int)$count > 0) {
            flash('Cannot delete — ' . $count . ' book(s) use this category. Reassign them first.', 'warning');
        } else {
            $del = $conn->prepare("DELETE FROM categories WHERE id=?");
            $del->bind_param('i', $id);
            $del->execute();
            flash('Category deleted.');
        }
    }
    redirect('/admin/categories.php');
}

$editing = null;
if (isset($_GET['edit'])) {
    $editing = $conn->query("SELECT * FROM categories WHERE id=" . (int)$_GET['edit'])->fetch_assoc();
}

$categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM books b WHERE b.category_id=c.id) AS book_count FROM categories c ORDER BY c.name");

$title = 'Categories';
$active = 'categories';
include __DIR__ . '/../inc/header.php';
?>

<section class="bg-gradient-to-r from-brand-800 to-brand-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <h1 class="font-display text-3xl font-bold">Book Categories</h1>
        <p class="text-brand-100 mt-1">Organise the collection into tidy shelves.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-6 h-fit lg:sticky lg:top-24">
        <h2 class="font-display font-bold text-stone-800 text-lg mb-4"><?= $editing ? 'Edit Category' : 'Add Category' ?></h2>
        <form method="post" class="space-y-4">
            <?php if ($editing): ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="add">
            <?php endif; ?>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Name *</label>
                <input type="text" name="name" required value="<?= e($editing['name'] ?? '') ?>" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400" placeholder="e.g. Science Fiction">
            </div>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"><?= e($editing['description'] ?? '') ?></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl transition"><?= $editing ? 'Save Changes' : 'Add Category' ?></button>
                <?php if ($editing): ?>
                    <a href="<?= BASE_URL ?>/admin/categories.php" class="bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold px-5 py-3 rounded-xl transition">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- List -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden h-fit">
        <div class="divide-y divide-stone-100">
            <?php if ($categories && $categories->num_rows): while ($c = $categories->fetch_assoc()): ?>
            <div class="px-5 py-4 flex items-center gap-4">
                <span class="w-11 h-11 rounded-xl bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-xl">📂</span>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-stone-800"><?= e($c['name']) ?></p>
                    <p class="text-xs text-stone-500 truncate"><?= e($c['description'] ?: 'No description') ?> · <?= (int)$c['book_count'] ?> books</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="?edit=<?= (int)$c['id'] ?>" class="text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition">Edit</a>
                    <form method="post" data-confirm="Delete this category?">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <button type="submit" class="text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition">Delete</button>
                    </form>
                </div>
            </div>
            <?php endwhile; else: ?>
            <p class="px-5 py-10 text-center text-stone-500">No categories yet.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../inc/footer.php'; ?>
