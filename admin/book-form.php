<?php
require_once __DIR__ . '/../auth.php';
require_admin();

$id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
$edit = $id > 0;
$book = null;
if ($edit) {
    $book = $conn->query("SELECT * FROM books WHERE id=$id")->fetch_assoc();
    if (!$book) {
        flash('Book not found.', 'error');
        redirect('/admin/books.php');
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $totalCopies = max(1, (int)($_POST['total_copies'] ?? 1));
    $availableCopies = max(0, (int)($_POST['available_copies'] ?? $totalCopies));
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $author === '') {
        flash('Title and author are required.', 'error');
    } else {
        if ($availableCopies > $totalCopies) $availableCopies = $totalCopies;

        $coverPath = $book['cover'] ?? null;

        // Handle cover upload
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            $mime = mime_content_type($_FILES['cover']['tmp_name']);
            $ext = $allowed[$mime] ?? null;
            $size = (int)$_FILES['cover']['size'];

            if (!$ext) {
                flash('Cover must be a JPG, PNG, GIF or WEBP image.', 'error');
            } elseif ($size > 3 * 1024 * 1024) {
                flash('Cover image must be under 3MB.', 'error');
            } else {
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
                $fname = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($_FILES['cover']['tmp_name'], UPLOAD_DIR . '/' . $fname)) {
                    if ($book && !empty($book['cover'])) {
                        $old = APP_ROOT . '/' . $book['cover'];
                        if (is_file($old)) @unlink($old);
                    }
                    $coverPath = 'uploads/' . $fname;
                } else {
                    flash('Could not upload the cover image.', 'error');
                }
            }
        }

        if ($edit) {
            $stmt = $conn->prepare("UPDATE books SET title=?, author=?, isbn=?, category_id=?, total_copies=?, available_copies=?, cover=?, description=?, content=? WHERE id=?");
            $stmt->bind_param('ssssiiisss', $title, $author, $isbn ?: null, $categoryId ?: null, $totalCopies, $availableCopies, $coverPath, $description, $content, $id);
            $stmt->execute();
            flash('Book updated successfully!');
        } else {
            $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, category_id, total_copies, available_copies, cover, description, content) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssssiiisss', $title, $author, $isbn ?: null, $categoryId ?: null, $totalCopies, $availableCopies, $coverPath, $description, $content);
            $stmt->execute();
            flash('Book added successfully!');
        }
        redirect('/admin/books.php');
    }
}

$title = $edit ? 'Edit Book' : 'Add Book';
$active = 'manage';
include __DIR__ . '/../inc/header.php';
?>

<section class="bg-gradient-to-r from-brand-800 to-brand-600 text-white">
    <div class="max-w-4xl mx-auto px-4 py-10">
        <h1 class="font-display text-3xl font-bold"><?= $edit ? 'Edit Book' : 'Add New Book' ?></h1>
        <p class="text-brand-100 mt-1"><?= $edit ? 'Update the details for "' . e($book['title']) . '".' : 'Fill in the details to add a book to the collection.' ?></p>
    </div>
</section>

<section class="max-w-4xl mx-auto px-4 mt-8">
    <form method="post" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-6 md:p-8">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-stone-700 mb-1">Title *</label>
                <input type="text" name="title" required value="<?= e($_POST['title'] ?? ($book['title'] ?? '')) ?>" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Author *</label>
                <input type="text" name="author" required value="<?= e($_POST['author'] ?? ($book['author'] ?? '')) ?>" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">ISBN</label>
                <input type="text" name="isbn" value="<?= e($_POST['isbn'] ?? ($book['isbn'] ?? '')) ?>" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Category</label>
                <select name="category_id" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
                    <option value="0">— None —</option>
                    <?php while ($c = $categories->fetch_assoc()): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ((int)($_POST['category_id'] ?? ($book['category_id'] ?? 0))) === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Total copies *</label>
                <input type="number" name="total_copies" min="1" required value="<?= e($_POST['total_copies'] ?? ($book['total_copies'] ?? 1)) ?>" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Available copies</label>
                <input type="number" name="available_copies" min="0" value="<?= e($_POST['available_copies'] ?? ($book['available_copies'] ?? '')) ?>" placeholder="Same as total" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>
            <div>
                <label class="block text-sm font-bold text-stone-700 mb-1">Cover image</label>
                <input type="file" name="cover" accept="image/*" class="w-full text-sm text-stone-500 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-600 file:text-white file:font-bold file:px-4 file:py-2.5 hover:file:bg-brand-700 transition">
                <?php if ($edit && !empty($book['cover'])): ?>
                    <p class="text-xs text-stone-500 mt-2">Current cover: <?= e(basename($book['cover'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-5">
            <label class="block text-sm font-bold text-stone-700 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"><?= e($_POST['description'] ?? ($book['description'] ?? '')) ?></textarea>
        </div>

        <div class="mt-5">
            <label class="block text-sm font-bold text-stone-700 mb-1">Full text (for online reading)</label>
            <textarea name="content" rows="10" placeholder="Paste the full text of the book here. Separate paragraphs with a blank line." class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-400"><?= e($_POST['content'] ?? ($book['content'] ?? '')) ?></textarea>
            <p class="text-xs text-stone-500 mt-1">Readers will see this text page by page in the online reader.</p>
        </div>

        <div class="flex gap-3 mt-8">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold px-8 py-3 rounded-xl transition"><?= $edit ? 'Save Changes' : 'Add Book' ?></button>
            <a href="<?= BASE_URL ?>/admin/books.php" class="bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold px-8 py-3 rounded-xl transition">Cancel</a>
        </div>
    </form>
</section>

<?php include __DIR__ . '/../inc/footer.php'; ?>
