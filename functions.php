<?php
/**
 * Roots includes
 *
 * The $roots_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 * @link https://github.com/roots/roots/pull/1042
 */
$roots_includes = array(
  'lib/utils.php',           // Utility functions
  'lib/init.php',            // Initial theme setup and constants
  'lib/wrapper.php',         // Theme wrapper class
  'lib/sidebar.php',         // Sidebar class
  'lib/config.php',          // Configuration
  'lib/activation.php',      // Theme activation
  'lib/titles.php',          // Page titles
  'lib/nav.php',             // Custom nav modifications
  'lib/gallery.php',         // Custom [gallery] modifications
  'lib/comments.php',        // Custom comments modifications
  'lib/scripts.php',         // Scripts and stylesheets
  'lib/extras.php',          // Custom functions
);

foreach ($roots_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'roots'), $file), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);



/*========================================================================*
  Some Custom Stuff 
 *========================================================================*/


/*
 * => Debug
 * --------------------------------------*/
if ( ! function_exists( 'skyhook_debug' ) ) {
  function skyhook_debug( $message ) {
    if ( WP_DEBUG === true ) {
      if ( is_array( $message ) || is_object( $message ) ) {
        error_log( print_r( $message, true ) );
      } else {
        error_log( $message );
      }
    }
  }
}


/*
 * => Turn off default image links
 * --------------------------------------*/
function imagelink_setup() {
  $image_set = get_option( 'image_default_link_type' );
  if ($image_set !== 'none') {
    update_option('image_default_link_type', 'none');
  }
}
add_action('admin_init', 'imagelink_setup', 10);


/*
 * => Custom Login
 * --------------------------------------*/
function custom_login_logo() { ?>
  <style type="text/css">
      body.login div#login h1 a {
        background-image: url(<?php echo $logo_url; ?>);
        background-size: 100%;
        width: 145px;
      }
      .login form .input, .login input[type="text"] {
        background: #fff;
      }
  </style>
<?php 
} 
add_action( 'login_enqueue_scripts', 'custom_login_logo' );

// Add logo link
function my_login_logo_url() {
    return get_bloginfo( 'url' );
} add_filter( 'login_headerurl', 'my_login_logo_url' );

// Add logo link title
function my_login_logo_url_title() {
    return get_bloginfo( 'name' );
} add_filter( 'login_headertitle', 'my_login_logo_url_title' );


/*
 * => Hide certain menu items
 * --------------------------------------*/
function remove_menu_items() {
  // provide a list of usernames who can access special admin menu items
  $admins = array( 
    'skyhook',
    'bigwilliam',
    'cobbman'
  );

  // get the current user
  $current_user = wp_get_current_user();

  // match and remove if needed
  if( !in_array( $current_user->user_login, $admins ) ) {
    remove_menu_page('edit.php?post_type=acf'); // Remove ACF Menu
    remove_menu_page('acf-options'); // Remove ACF Options
    remove_menu_page('wptouch-admin-touchboard'); // WP Touch Mobile Theme Plugin
  }
}
add_action( 'admin_menu', 'remove_menu_items', 999 );


/*
 * => Change 'Posts' to 'Blog'
 * --------------------------------------*/
function custom_menu_names() {
  global $menu;
  $menu[5][0] = 'Blog';
}
add_action( 'admin_menu', 'custom_menu_names', 999 );


/*
 * => Change Admin Bar
 * --------------------------------------*/
function change_admin_bar() {

  // Global
  global $wp_admin_bar;

  // Remove Links
  $wp_admin_bar->remove_menu( 'new-link'        );
  $wp_admin_bar->remove_menu( 'new-media'       );
  $wp_admin_bar->remove_menu( 'new-user'        );
  $wp_admin_bar->remove_menu( 'comments'        );
  $wp_admin_bar->remove_menu( 'about'           );
  $wp_admin_bar->remove_menu( 'wporg'           );
  $wp_admin_bar->remove_menu( 'documentation'   );
  $wp_admin_bar->remove_menu( 'support-forums'  );
  $wp_admin_bar->remove_menu( 'feedback'        );

  // Add site link
  $wp_admin_bar->add_menu( array(
    'parent' => 'wp-logo',
    'id'     => 'Skyhook Marketing-site',
    'title'  => __( 'Skyhook Marketing', 'roots' ),
    'href'   => 'http://Skyhook Marketing.com'
  ) );

  // Add support link
  $wp_admin_bar->add_menu( array(
    'parent' => 'wp-logo',
    'id'     => 'Skyhook Marketing-support',
    'title'  => __( 'Skyhook Marketing Support', 'roots' ),
    'href'   => 'http://SkyhookMarketing.com/contact'
  ) );
}
add_action( 'wp_before_admin_bar_render', 'change_admin_bar' );


/*
 * => Replace footer text
 * --------------------------------------*/
function replace_footer_text () {
  printf( 
    '<span id="footer-thankyou">%s <a href="%s" target="_blank">%s</a>.</span>',
     __( 'Website proudly created by', 'roots' ), 
     'http://SkyhookMarketing.com/',
     __( 'Skyhook Marketing', 'roots' ) 
  );
}
add_filter( 'admin_footer_text', 'replace_footer_text' );

/*
 * => Nice Search Queries
 * -----------------------------------------*/
/**
 * Redirects search results from /?s=query to /search/query/, converts %20 to +
 *
 * @link http://txfx.net/wordpress-plugins/nice-search/
 * 
 */
if ( !function_exists('soil_nice_search_redirect') ) {
  function soil_nice_search_redirect() {
    global $wp_rewrite;
    if (!isset($wp_rewrite) || !is_object($wp_rewrite) || !$wp_rewrite->using_permalinks()) {
      return;
    }

    $search_base = $wp_rewrite->search_base;
    if (is_search() && !is_admin() && strpos($_SERVER['REQUEST_URI'], "/{$search_base}/") === false) {
      wp_redirect(home_url("/{$search_base}/" . urlencode(get_query_var('s'))));
      exit();
    }
  }
  add_action('template_redirect', 'soil_nice_search_redirect');
}

