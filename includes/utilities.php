<?php

function get_header_icon() {
	$fallback = VASU_THEME_STATIC_URL . '/img/ucfemb.png';
	$url = get_site_icon_url( 512, $fallback );

	return $url;
}
