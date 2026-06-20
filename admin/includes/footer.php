  </main><!-- /main -->

  <footer class="border-t border-gray-100 px-6 py-3 text-center text-xs text-gray-400">
    VK Tent Services Admin &mdash; Developed by
    <a href="https://samastam.com" target="_blank" class="text-amber hover:underline">Samastam Technologies Pvt. Ltd.</a>
  </footer>

</div><!-- /main wrapper -->

<script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
<script>
function toggleSidebar() {
  const sidebar  = document.getElementById('sidebar');
  const overlay  = document.getElementById('sidebar-overlay');
  const isOpen   = !sidebar.classList.contains('-translate-x-full');
  sidebar.classList.toggle('-translate-x-full', isOpen);
  overlay.classList.toggle('hidden', isOpen);
}
</script>
</body>
</html>
