<?php

	include_once('Pinterest.class.php');

	$pinterest = new Pinterest();

	$pinterest->scrapeUser("username");

	$pinterestCovers = $pinterest->getCovers();

	$pinterestThumbs = $pinterest->getThumbs();

	$pinterestLinks = $pinterest->getLinks();

?>