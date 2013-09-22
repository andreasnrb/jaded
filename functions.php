<?php

use CLMVC\Components\Posts\Post;
/**
 * Adds support for a custom header image.
 */
require get_template_directory() . '/inc/custom-header.php';

global $wp_query;
function get_post_classes() {
    return join( ' ', get_post_class() );
}

function get_post_entry_date($link = true, $test = '') {
    if ( has_post_format( array( 'chat', 'status' ) ) )
        $format_prefix = _x( '%1$s on %2$s', '1: post format name. 2: date', 'jaded' );
    else
        $format_prefix = '%2$s';

    if ($link)
        $date = sprintf( '<span class="date"><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a></span>',
            esc_url( get_permalink() ),
            esc_attr( sprintf( __( 'Permalink to %s', 'jaded' ), the_title_attribute( 'echo=0' ) ) ),
            esc_attr( get_the_date( 'c' ) ),
            esc_html( sprintf( $format_prefix, get_post_format_string( get_post_format() ), get_the_date() ) )
        );
    else
        $date = sprintf( '<time class="entry-date" datetime="%s">%s</time>',
            esc_attr( get_the_date( 'c' ) ),
            esc_html( sprintf( $format_prefix, get_post_format_string( get_post_format() ), get_the_date() ) )
        );

    return $date;
}

function get_body_classes($class = '') {
    return join( ' ', get_body_class( $class ) );
}


function jade_filter($current_post) {
    global $post;
    $post = $current_post;
    setup_postdata($post);
    $cl_post = new Post();
    return $cl_post;
}


function jaded_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'jaded' ), max( $paged, $page ) );

	return trim($title,' |');
}
add_filter( 'wp_title', 'jaded_wp_title', 10, 2 );


/**
 * Sets up theme defaults and registers the various WordPress features that
 * Jaded supports.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_editor_style() To add Visual Editor stylesheets.
 * @uses add_theme_support() To add support for automatic feed links, post
 * formats, and post thumbnails.
 * @uses register_nav_menu() To add support for a navigation menu.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since Jaded 0.1
 *
 * @return void
 */
function jaded_setup() {
    /*
     * Makes Jaded available for translation.
     *
     * Translations can be added to the /languages/ directory.
     * If you're building a theme based on Jaded, use a find and
     * replace to change 'jaded' to the name of your theme in all
     * template files.
     */
    load_theme_textdomain( 'jaded', get_template_directory() . '/languages' );

    /*
     * This theme styles the visual editor to resemble the theme style,
     * specifically font, colors, icons, and column width.
     */
    add_editor_style( array( 'assets/css/editor-style.css', 'fonts/genericons.css', jaded_fonts_url() ) );

    // Adds RSS feed links to <head> for posts and comments.
    add_theme_support( 'automatic-feed-links' );

    // Switches default core markup for search form, comment form, and comments
    // to output valid HTML5.
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

    /*
     * This theme supports all available post formats by default.
     * See http://codex.wordpress.org/Post_Formats
     */
    add_theme_support( 'post-formats', array(
        'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video'
    ) );

    // This theme uses wp_nav_menu() in one location.
    register_nav_menu( 'primary', __( 'Navigation Menu', 'jaded' ) );

    /*
     * This theme uses a custom image size for featured images, displayed on
     * "standard" posts and pages.
     */
    add_theme_support( 'post-thumbnails' );
    set_post_thumbnail_size( 604, 270, true );

    // This theme uses its own gallery styles.
    add_filter( 'use_default_gallery_style', '__return_false' );
}
add_action( 'after_setup_theme', 'jaded_setup' );

/**
 * Returns the Google font stylesheet URL, if available.
 *
 * The use of Source Sans Pro and Bitter by default is localized. For languages
 * that use characters not supported by the font, the font can be disabled.
 *
 * @since Jaded 0.1
 *
 * @return string Font stylesheet or empty string if disabled.
 */
function jaded_fonts_url() {
    $fonts_url = '';

    /* Translators: If there are characters in your language that are not
     * supported by Source Sans Pro, translate this to 'off'. Do not translate
     * into your own language.
     */
    $source_sans_pro = _x( 'on', 'Source Sans Pro font: on or off', 'jaded' );

    /* Translators: If there are characters in your language that are not
     * supported by Bitter, translate this to 'off'. Do not translate into your
     * own language.
     */
    $bitter = _x( 'on', 'Bitter font: on or off', 'jaded' );

    if ( 'off' !== $source_sans_pro || 'off' !== $bitter ) {
        $font_families = array();

        if ( 'off' !== $source_sans_pro )
            $font_families[] = 'Source Sans Pro:300,400,700,300italic,400italic,700italic';

        if ( 'off' !== $bitter )
            $font_families[] = 'Bitter:400,700';

        $query_args = array(
            'family' => urlencode( implode( '|', $font_families ) ),
            'subset' => urlencode( 'latin,latin-ext' ),
        );
        $fonts_url = add_query_arg( $query_args, "//fonts.googleapis.com/css" );
    }

    return $fonts_url;
}

/**
 * Enqueues scripts and styles for front end.
 *
 * @since Jaded 0.1
 *
 * @return void
 */
function jaded_scripts_styles() {
    // Adds JavaScript to pages with the comment form to support sites with
    // threaded comments (when in use).
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
        wp_enqueue_script( 'comment-reply' );

    // Adds Masonry to handle vertical alignment of footer widgets.
    if ( is_active_sidebar( 'sidebar-1' ) )
        wp_enqueue_script( 'jquery-masonry' );

    // Loads JavaScript file with functionality specific to Jaded.
    wp_enqueue_script( 'jaded-script', get_template_directory_uri() . '/assets/js/functions.js', array( 'jquery' ), '2013-07-18', true );

    // Add Open Sans and Bitter fonts, used in the main stylesheet.
    wp_enqueue_style( 'jaded-fonts', jaded_fonts_url(), array(), null );

    // Add Genericons font, used in the main stylesheet.
    wp_enqueue_style( 'genericons', get_template_directory_uri() . '/assets/fonts/genericons.css', array(), '2.09' );

    // Loads our main stylesheet.
    wp_enqueue_style( 'jaded-style', get_stylesheet_uri(), array(), '2013-07-18' );

    // Loads the Internet Explorer specific stylesheet.
    wp_enqueue_style( 'jaded-ie', get_template_directory_uri() . '/assets/css/ie.css', array( 'jaded-style' ), '2013-07-18' );
    wp_style_add_data( 'jaded-ie', 'conditional', 'lt IE 9' );
}
add_action( 'wp_enqueue_scripts', 'jaded_scripts_styles' );

/**
 * Registers two widget areas.
 *
 * @since Jaded 0.1
 *
 * @return void
 */
function jaded_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Main Widget Area', 'jaded' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Appears in the footer section of the site.', 'jaded' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Secondary Widget Area', 'jaded' ),
        'id'            => 'sidebar-2',
        'description'   => __( 'Appears on posts and pages in the sidebar.', 'jaded' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'jaded_widgets_init' );



/**
 * Add postMessage support for site title and description for the Customizer.
 *
 * @since Jaded 0.1
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 * @return void
 */
function jaded_customize_register( $wp_customize ) {
    $wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
    $wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
    $wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
}
add_action( 'customize_register', 'jaded_customize_register' );

/**
 * Binds JavaScript handlers to make Customizer preview reload changes
 * asynchronously.
 *
 * @since Jaded 0.1
 */
function jaded_customize_preview_js() {
    wp_enqueue_script( 'jaded-customizer', get_template_directory_uri() . '/assets/js/theme-customizer.js', array( 'customize-preview' ), '20130226', true );
}
add_action( 'customize_preview_init', 'jaded_customize_preview_js' );