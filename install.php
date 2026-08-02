<?php
// =============================================
//  INSTALLER - creates the database, tables & seed data
//  Open once in your browser, then delete this file.
// =============================================

$host = 'localhost';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die('MySQL connection failed: ' . htmlspecialchars($conn->connect_error));
}

$sqlFile = __DIR__ . '/database/library.sql';
$sql = file_get_contents($sqlFile);
if ($sql === false) {
    die('Could not read database/library.sql');
}

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
}

$error = $conn->error;
$conn->close();

$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Install Emerald Library</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: { colors: {
  brand:{50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b'},
  gold:{50:'#fffbeb',100:'#fef3c7',200:'#fde68a',300:'#fcd34d',400:'#fbbf24',500:'#f59e0b',600:'#d97706',700:'#b45309',800:'#92400e',900:'#78350f'},
  cream:'#faf6ef'
}}}}
</script>
</head>
<body class="bg-cream text-stone-800 flex items-center justify-center min-h-screen">
  <div class="bg-white rounded-2xl shadow-xl border border-brand-100 p-8 max-w-lg w-full mx-4">
    <div class="flex items-center gap-3 mb-4">
      <span class="text-3xl">📚</span>
      <h1 class="text-2xl font-bold text-brand-800">Emerald Library Installer</h1>
    </div>

    <?php if ($error === ''): ?>
      <div class="bg-brand-50 border border-brand-200 text-brand-800 rounded-xl p-4 mb-4">
        <p class="font-semibold">✅ Installation successful!</p>
        <p class="text-sm mt-1">The database was created and seeded with sample data.</p>
      </div>
      <ul class="text-sm space-y-2 mb-5 text-stone-700">
        <li>👤 <b>Admin:</b> admin@library.com &nbsp;/&nbsp; admin123</li>
        <li>👤 <b>User:</b> user@library.com &nbsp;/&nbsp; user123</li>
      </ul>
      <div class="flex gap-3">
        <a href="index.php" class="flex-1 text-center bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl transition">Go to Home</a>
        <a href="login.php" class="flex-1 text-center bg-gold-500 hover:bg-gold-600 text-stone-900 font-bold py-3 rounded-xl transition">Log In</a>
      </div>
      <p class="text-xs text-stone-500 mt-4">For security, please delete <code class="bg-stone-100 px-1 rounded">install.php</code> after setup.</p>
    <?php else: ?>
      <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-4">
        <p class="font-semibold">❌ Something went wrong</p>
        <pre class="text-xs mt-2 whitespace-pre-wrap"><?= e($error) ?></pre>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
