</main>

<footer class="mt-16 bg-brand-900 text-brand-100">
  <div class="max-w-7xl mx-auto px-4 py-12 grid gap-10 md:grid-cols-3">
    <div>
      <div class="flex items-center gap-2 mb-3">
        <span class="text-3xl">📚</span>
        <span class="font-display text-xl font-bold text-gold-300">Emerald Library</span>
      </div>
      <p class="text-sm text-brand-200 leading-relaxed">A cozy community library where every book finds a reader and every reader finds a book.</p>
    </div>
    <div>
      <h4 class="font-display font-bold text-gold-300 mb-3">Quick Links</h4>
      <ul class="space-y-2 text-sm">
        <li><a href="<?= BASE_URL ?>/index.php" class="hover:text-gold-300 transition">Home</a></li>
        <li><a href="<?= BASE_URL ?>/books.php" class="hover:text-gold-300 transition">Browse Books</a></li>
        <?php if (is_logged_in()): ?>
          <li><a href="<?= BASE_URL ?>/user/my-books.php" class="hover:text-gold-300 transition">My Books</a></li>
        <?php else: ?>
          <li><a href="<?= BASE_URL ?>/register.php" class="hover:text-gold-300 transition">Become a Member</a></li>
        <?php endif; ?>
      </ul>
    </div>
    <div>
      <h4 class="font-display font-bold text-gold-300 mb-3">Library Hours</h4>
      <ul class="space-y-2 text-sm text-brand-200">
        <li>Mon – Fri: 9:00 AM – 8:00 PM</li>
        <li>Saturday: 10:00 AM – 6:00 PM</li>
        <li>Sunday: Closed</li>
      </ul>
    </div>
  </div>
  <div class="border-t border-brand-800 py-4 text-center text-xs text-brand-300">
    © <?= date('Y') ?> Emerald Library · Crafted with ♥ for book lovers
  </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
