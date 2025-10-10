<?php
/**
 * Theme footer template.
 *
 * @package Tailwind_ACF
 */
?>
</main>

<footer class="site-footer border-t border-slate-200/60 bg-white/80 backdrop-blur">
	<div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-6 py-8 text-center text-sm text-slate-500 sm:flex-row sm:px-8 lg:px-12">
		<span>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'menu_class'     => 'flex flex-wrap justify-center gap-4',
						'fallback_cb'    => false,
						'container'      => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
