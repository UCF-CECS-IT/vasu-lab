<?php

/**
 * Remove paragraph tag from excerpts and body
 **/
remove_filter('the_excerpt', 'wpautop');
remove_filter ('the_content', 'wpautop');

/**
 * Kill attachment pages, author pages, daily archive pages, search, and feeds.
 *
 * http://betterwp.net/wordpress-tips/disable-some-wordpress-pages/
 **/
function ucfwp_kill_unused_templates()
{
	global $wp_query, $post;

	if (is_author() || is_attachment() || is_date() || is_search() || is_feed() || is_comment_feed()) {
		wp_redirect(home_url());
		exit();
	}
}

add_action('template_redirect', 'ucfwp_kill_unused_templates');

/**
 * Modifies attachment links to point directly to individual files instead of
 * single attachment views.
 *
 * Takes effect only when the `ucfwp_kill_unused_templates` hook is registered,
 * and/or if the `ucfwp_enable_attachment_link_rewrites` hook has been passed a
 * custom value.
 *
 * @since 0.6.0
 * @author Jo Dickson
 * @param string $link Existing URL to attachment page
 * @param int $post_id Attachment post ID
 * @return string Modified attachment URL
 */
function ucfwp_modify_attachment_links($link, $post_id)
{
	$do_rewrites = has_action('template_redirect', 'ucfwp_kill_unused_templates') !== false ? true : false;
	// Let child themes/plugins override this behavior:
	if (has_filter('ucfwp_enable_attachment_link_rewrites') !== false) {
		$do_rewrites = filter_var(apply_filters('ucfwp_enable_attachment_link_rewrites', $do_rewrites), FILTER_VALIDATE_BOOLEAN);
	}

	if ($do_rewrites) {
		$attachment_url = wp_get_attachment_url($post_id);
		if ($attachment_url) {
			$link = $attachment_url;
		}
	}

	return $link;
}

add_filter('attachment_link', 'ucfwp_modify_attachment_links', 20, 2);

/**
 * An opinionated set of overrides for this theme that disables comments,
 * trackbacks, and pingbacks sitewide, and hides references to comments in the
 * WordPress admin to reduce clutter.
 *
 * @since 0.3.0
 * @author Jo Dickson
 */
function ucfwp_kill_comments()
{
	// Remove the X-Pingback HTTP header, if present.
	add_filter('wp_headers', function ($headers) {
		if (isset($headers['X-Pingback'])) {
			unset($headers['X-Pingback']);
		}
		return $headers;
	});

	// Remove native post type support for comments and trackbacks on all
	// public-facing post types.
	// NOTE: If an existing post already has comments posted to it, they'll
	// still be viewable in the Comments metabox when editing the post.
	$post_types = get_post_types(array('public' => true), 'names');
	foreach ($post_types as $pt) {
		if (post_type_supports($pt, 'comments')) {
			remove_post_type_support($pt, 'comments');
		}
		if (post_type_supports($pt, 'trackbacks')) {
			remove_post_type_support($pt, 'trackbacks');
		}
	}

	// Disable comments and pingbacks on new posts (these are the primary
	// default discussion settings under Settings > Discussion)
	add_filter('option_default_pingback_flag', '__return_zero');
	add_filter('option_default_ping_status', '__return_zero');
	add_filter('option_default_comment_status', '__return_zero');

	// Close ability to add new comments and pingbacks on existing posts.
	add_filter('comments_open', '__return_false');
	add_filter('pings_open', '__return_false');

	// Remove admin bar link for comments
	add_action('wp_before_admin_bar_render', function () {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('comments');
	});

	// Remove Comments and Settings > Discussion links from the admin menu.
	// NOTE: Both of these admin views are still accessible if requested
	// directly.
	add_action('admin_menu', function () {
		remove_menu_page('edit-comments.php');
		remove_submenu_page('options-general.php', 'options-discussion.php');
	});

	// Remove the recent comments box from the admin dashboard.
	add_action('wp_dashboard_setup', function () {
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	});

	// Hide comment count and other inline references to comments in the
	// admin dashboard and user profile view.
	$admin_css = '';
	ob_start();
	?>
	<style>
		#dashboard_right_now .comment-count,
		#dashboard_right_now .comment-mod-count,
		#latest-comments,
		#welcome-panel .welcome-comments,
		.user-comment-shortcuts-wrap {
			display: none !important;
		}
	</style>
	<?php
		$admin_css = ob_get_clean();
		add_action('admin_print_styles-index.php', function () use ($admin_css) {
			echo $admin_css;
		});
		add_action('admin_print_styles-profile.php', function () use ($admin_css) {
			echo $admin_css;
		});
}

add_action('init', 'ucfwp_kill_comments');

/**
 * Enqueue front-end css and js.
 **/
function ucfwp_enqueue_frontend_assets()
{
	$theme = wp_get_theme('vasu-lab');
	$theme_version = ($theme instanceof WP_Theme) ? $theme->get('Version') : false;

	// Register main theme stylesheet
	wp_enqueue_style('style', VASU_THEME_CSS_URL . '/style.min.css', null, $theme_version);

	wp_enqueue_script('wp-a11y');
	wp_enqueue_script('tether', 'https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js', null, null, true);
	wp_enqueue_script('script', VASU_THEME_JS_URL . '/script.min.js', array('jquery', 'tether'), $theme_version, true);

	// Add localized script variables to the document
	$site_url = parse_url(get_site_url());
	wp_localize_script('script', 'UCFWP', array(
		'domain' => $site_url['host']
	));
}

add_action('wp_enqueue_scripts', 'ucfwp_enqueue_frontend_assets');

/**
 * De-register and re-register a newer version of jquery early in the
 * document head.
 *
 * @author Jo Dickson
 * @since 0.2.5
 */
function ucfwp_enqueue_jquery()
{
	// Deregister jquery and re-register newer version in the document head.
	wp_deregister_script('jquery');
	wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js', null, null, false);
	wp_enqueue_script('jquery');
}

add_action('wp_enqueue_scripts', 'ucfwp_enqueue_jquery', 1);

/**
 * Meta tags to insert into the document head.
 **/
function ucfwp_add_meta_tags()
{
	?>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<?php
		$gw_verify = get_theme_mod('gw_verify');
		if ($gw_verify) :
			?>
		<meta name="google-site-verification" content="<?php echo htmlentities($gw_verify); ?>">
	<?php endif; ?>
<?php
}

add_action('wp_head', 'ucfwp_add_meta_tags', 1);

function ucfwp_add_favicon_default()
{
		if ( !has_site_icon() ):
	?>
	<link rel="shortcut icon" href="<?php echo THEME_URL . '/favicon.ico'; ?>" />
	<?php
		endif;
}

/**
 * Removed unneeded meta tags generated by WordPress.
 * Some of these may already be handled by security plugins.
 **/
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
add_filter('emoji_svg_url', '__return_false');

// Registers custom menu position
register_nav_menu( 'header-menu', __( 'Header Menu' ) );
