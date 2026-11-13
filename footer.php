</main>

<?php if (!is_front_page()): ?>
    <footer class="w-full mt-12 py-6">
        <p class="w-full text-center!">&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. all rights reserved.</p>
    </footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>

</html>