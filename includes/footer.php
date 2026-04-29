<footer>
  <div class="footer-bottom">
    <span>© <?php echo date('Y'); ?> Monvesto</span>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.faq-q').forEach(function(q) {
    q.addEventListener('click', function() {
      this.closest('.faq-item').classList.toggle('open');
    });
  });
});
</script>