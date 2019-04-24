<?php
/**
 * The template for displaying openemm confirm (!ajax) responses.
 *
 * Themeable with ROOT/wp-content/themes/THEME/page--openemm.php
 *
 * @plugin OpenEMM
 * @since 1.1
 */

if ( empty($_SESSION['openemm']) ) {
    wp_redirect( home_url() );
    exit;
}

$message = $_SESSION['openemm'];
unset($_SESSION['openemm']);

get_header();
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">

            <article id="openemm" class="page type-page status-publish hentry entry">
                <header class="entry-header screen-reader-text">
                    <h1 class="entry-title">OpenEMM</h1>
                </header>

                <div class="entry-content message <?php echo $message['type']; ?>">
                    <p><?php echo $message['message']; ?></p>
                </div><!-- .entry-content -->
            </article>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php get_sidebar();
get_footer();
