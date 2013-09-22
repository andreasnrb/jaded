<?php
global $wp_query;
$engine = \CLMVC\Controllers\Render\RenderingEngines::getEngine('jade', get_stylesheet_directory() . '/views');

$scope = array(
        'title' => wp_title(' | ', false),
        'site_title' => get_bloginfo('name'),
        'description' => get_bloginfo( 'description', 'display' ));

switch(true) {
    case is_page():
        $file = 'pages/single';
        break;
    case is_single():
        $posts = $wp_query->get_posts();
        global $post;
        $post = null;
        if ($posts)
            $post = array_shift($posts);
        $file = 'posts/single';
        setup_postdata($post);
        $scope['post'] = $post;
        break;
    case is_404():
        $file = '404';
        break;
    default:
        $file='posts/home';
        $scope['posts'] =$wp_query->get_posts();
}

echo $engine->render($file, $scope);