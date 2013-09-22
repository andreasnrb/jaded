<?php

use CLMVC\Components\Posts\Post;

global $wp_query;
function get_post_classes() {
    return join( ' ', get_post_class() );
}

function get_the_permalink() {
    return esc_url( apply_filters( 'the_permalink', get_permalink() ) );
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