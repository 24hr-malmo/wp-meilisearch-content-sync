<?php

/**
 * 24hr Meilisearch Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       24hr Meilisearch Plugin
 * Plugin URI:        https://24hr.se
 * Description:       Meilisearch plugin helper
 * Version:           0.0.3
 * Requires at least: 5.2
 * Requires PHP:      7.3
 * Author:            Richard Sweeney
 * Text Domain:       meilisearch-24hr
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

 require __DIR__.'/vendor/autoload.php';

 $meilisearch = new Meilisearch24hr\Meilisearch(
    getenv('CONTENT_DRAFT_URL') ?? 'http://api/content-draft',
    getenv('CONTENT_LIVE_URL') ?? 'http://api/content-live',
    getenv('MEILISEARCH_HOST') ?? 'http://meilisearch:7700',
    getenv('API_TOKEN')
);

$meilisearch->init();
