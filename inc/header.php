<?php
// =============================================
//  SHARED HEADER / NAVIGATION
//  Expects: $title, $active (optional)
// =============================================
require_once __DIR__ . '/../auth.php';

$user = current_user();
$flash = get_flash();
$active = $active ?? '';
$title = $title ?? 'Emerald Library';

$nav = [['key' => 'home', 'href' => BASE_URL . '/index.php', 'label' => 'Home']];
$nav[] = ['key' => 'books', 'href' => BASE_URL . '/books.php', 'label' => 'Browse Books'];

if (is_logged_in()) {
    if (is_admin()) {
        array_push($nav,
            ['key' => 'dashboard', 'href' => BASE_URL . '/admin/dashboard.php', 'label' => 'Dashboard'],
            ['key' => 'manage', 'href' => BASE_URL . '/admin/books.php', 'label' => 'Manage Books'],
            ['key' => 'requests', 'href' => BASE_URL . '/admin/requests.php', 'label' => 'Requests'],
            ['key' => 'issues', 'href' => BASE_URL . '/admin/issues.php', 'label' => 'Issues'],
            ['key' => 'members', 'href' => BASE_URL . '/admin/members.php', 'label' => 'Members'],
            ['key' => 'categories', 'href' => BASE_URL . '/admin/categories.php', 'label' => 'Categories']
        );
    } else {
        array_push($nav,
            ['key' => 'dashboard', 'href' => BASE_URL . '/user/dashboard.php', 'label' => 'Dashboard'],
            ['key' => 'mybooks', 'href' => BASE_URL . '/user/my-books.php', 'label' => 'My Books']
        );
    }
}

function nav_item($item, $active) {
    $on = $active === $item['key'];
    $cls = $on ? 'bg-brand-900/60 text-gold-300' : 'text-brand-50 hover:bg-brand-900/40 hover:text-white';
    return '<a href="' . e($item['href']) . '" class="px-3 py-2 rounded-lg text-sm font-semibold transition ' . $cls . '">' . e($item['label']) . '</a>';
}

$flashColors = [
    'success' => ['bg' => 'bg-brand-600', 'icon' => '✓'],
    'error'   => ['bg' => 'bg-rose-600',  'icon' => '✕'],
    'warning' => ['bg' => 'bg-amber-500', 'icon' => '!'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> | Emerald Library</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: {
  colors: {
    brand: {50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b'},
    gold:  {50:'#fffbeb',100:'#fef3c7',200:'#fde68a',300:'#fcd34d',400:'#fbbf24',500:'#f59e0b',600:'#d97706',700:'#b45309',800:'#92400e',900:'#78350f'},
    cream: '#faf6ef'
  },
  fontFamily: { display: ['Fraunces','Georgia','serif'] }
}}}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-cream text-stone-800 min-h-screen flex flex-col">

<header class="bg-brand-700 sticky top-0 z-40 shadow-lg">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-center justify-between h-16">
      <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-2 shrink-0">
        <span class="text-3xl drop-shadow">📚</span>
        <span class="font-display text-xl font-bold text-gold-300">Emerald Library</span>
      </a>

      <nav class="hidden lg:flex items-center gap-1">
        <?php foreach ($nav as $item) echo nav_item($item, $active); ?>
      </nav>

      <div class="hidden lg:flex items-center gap-2">
        <?php if (is_logged_in()): ?>
          <div class="flex items-center gap-2 bg-brand-800/60 rounded-full pl-1.5 pr-3 py-1">
            <span class="w-8 h-8 rounded-full bg-gold-400 text-brand-900 font-extrabold flex items-center justify-center"><?= e(strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
            <span class="text-sm font-semibold text-brand-50 max-w-[140px] truncate"><?= e($user['name']) ?></span>
          </div>
          <a href="<?= BASE_URL ?>/logout.php" class="bg-gold-500 hover:bg-gold-600 text-stone-900 text-sm font-bold px-4 py-2 rounded-full transition shadow">Logout</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/login.php" class="text-brand-50 hover:text-white text-sm font-semibold px-3 py-2">Sign In</a>
          <a href="<?= BASE_URL ?>/register.php" class="bg-gold-500 hover:bg-gold-600 text-stone-900 text-sm font-bold px-4 py-2 rounded-full transition shadow">Join Free</a>
        <?php endif; ?>
      </div>

      <button id="mobileMenuToggle" class="lg:hidden text-brand-50 text-2xl px-2" aria-label="Menu">☰</button>
    </div>
  </div>

  <div id="mobileMenu" class="hidden lg:hidden bg-brand-800 border-t border-brand-700">
    <div class="max-w-7xl mx-auto px-4 py-3 space-y-1">
      <?php foreach ($nav as $item) echo nav_item($item, $active); ?>
      <div class="pt-3 border-t border-brand-700 flex gap-2">
        <?php if (is_logged_in()): ?>
          <a href="<?= BASE_URL ?>/logout.php" class="flex-1 text-center bg-gold-500 hover:bg-gold-600 text-stone-900 font-bold py-2 rounded-lg text-sm">Logout</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/login.php" class="flex-1 text-center bg-brand-900/60 text-brand-50 font-bold py-2 rounded-lg text-sm">Sign In</a>
          <a href="<?= BASE_URL ?>/register.php" class="flex-1 text-center bg-gold-500 text-stone-900 font-bold py-2 rounded-lg text-sm">Join Free</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<?php if ($flash): $fc = $flashColors[$flash['type']] ?? $flashColors['success']; ?>
<div class="max-w-7xl mx-auto px-4 w-full">
  <div id="flashToast" class="mt-4 <?= $fc['bg'] ?> text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 toast">
    <span class="font-bold text-lg"><?= $fc['icon'] ?></span>
    <span class="font-semibold text-sm"><?= e($flash['message']) ?></span>
  </div>
</div>
<?php endif; ?>

<main class="flex-1">
