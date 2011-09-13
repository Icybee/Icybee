<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('ICanBoogie\DOCUMENT_ROOT'))
{
	exit('ICanBoogie\DOCUMENT_ROOT must be defined.');
}

$core->document = $document = new Icybee\Document();

require 'includes/route.php';

$document->css->add(BrickRouge\ASSETS . 'brickrouge.css', -250);
$document->css->add(Icybee\ASSETS . 'css/base.css', -200);
$document->css->add(Icybee\ASSETS . 'css/input.css', -190);

$document->js->add(Icybee\ASSETS . 'js/mootools-core.js', -200);
$document->js->add(Icybee\ASSETS . 'js/mootools-more.js', -200);
$document->js->add(ICanBoogie\ASSETS . 'icanboogie.js', -190);
$document->js->add(BrickRouge\ASSETS . 'brickrouge.js', -190);
$document->js->add(Icybee\ASSETS . 'js/widget.js', -185);
$document->js->add(Icybee\ASSETS . 'js/spinner.js', -180);
$document->js->add(Icybee\ASSETS . 'js/publisher.js', -180);

echo $document;

#
# statistics
#

$elapsed_time = microtime(true) - $wddebug_time_reference;

$queries_count = 0;
$queries_stats = array();

foreach ($core->connections as $id => $connection)
{
	$count = $connection->queries_count;
	$queries_count += $count;
	$queries_stats[] = $id . ': ' . $count;
}

echo PHP_EOL . PHP_EOL . '<!-- ' . t
(
	'icybee - time: :elapsed sec, memory usage: :memory-usage (peak: :memory-peak), queries: :queries-count (:queries-details)', array
	(
		':elapsed' => number_format($elapsed_time, 3, '.', ''),
		':memory-usage' => memory_get_usage(),
		':memory-peak' => memory_get_peak_usage(),
		':queries-count' => $queries_count,
		':queries-details' => implode(', ', $queries_stats)
	)
)

. ' -->';

exit;