<?php $menu = wp_nav_menu( array(
	'container'       => 'div',
	'container_class' => '',
	'link_before' 	  => '<div class="button">',
	'link_after'	  => '</div>',
	'items_wrap'	  => '%3$s',
	'container_id'    => 'header-menu',
	'depth'           => 2,
	'echo'            => false,
	'fallback_cb'     => 'bs4Navwalker::fallback',
	'menu_class'      => '',
	'theme_location'  => 'header-menu',
	'walker'          => new bs4Navwalker()
) );

$headerImg = get_field()

?>

<!DOCTYPE html>
<html lang="en-us">
	<head>
		<?php wp_head(); ?>
		<title><?php echo get_bloginfo( 'name' ); ?></title>
	</head>
	<body class="">
		<div id="ucfhb"></div>
		<div id="contain">
			<div class="pb">
				<!-- Title Block -->
				<div id="title">
					<img src="img/ucfemb.png" id="ucfemb">
					<br>
					<a href="?" id="titletext"><?php echo get_bloginfo( 'name' ); ?></a>
				</div>

				<!-- Menu column -->
				<div id="nav">
					<?php echo $menu; ?>
				</div>

				<!-- Body block -->
				<div id="main">



