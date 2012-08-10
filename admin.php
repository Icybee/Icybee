<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

global $document;

$time = microtime(true);

if (!defined('ICanBoogie\DOCUMENT_ROOT'))
{
	exit('ICanBoogie\DOCUMENT_ROOT must be defined.');
}

$core->document = $document = new Icybee\Document();

require 'includes/route.php';

$document->css->add(Brickrouge\ASSETS . 'brickrouge.css', -250);
$document->css->add(Icybee\ASSETS . 'icybee.css', -240);
$document->css->add(Icybee\ASSETS . 'admin.css', -200);
$document->css->add(Icybee\ASSETS . 'admin-more.css', -200);

$document->js->add(Icybee\ASSETS . 'mootools-core.js', -200);
$document->js->add(Icybee\ASSETS . 'mootools-more.js', -200);
$document->js->add(ICanBoogie\ASSETS . 'icanboogie.js', -190);
$document->js->add(Brickrouge\ASSETS . 'brickrouge.js', -190);
$document->js->add(Icybee\ASSETS . 'admin.js', -180);
$document->js->add(Icybee\ASSETS . 'js/publisher.js', -180);

$html = (string) $document;

#
# statistics
#

$time_end = microtime(true);

$queries_count = 0;
$queries_time = 0;
$queries_stats = array();

foreach ($core->connections as $id => $connection)
{
	$count = $connection->queries_count;
	$queries_count += $count;
	$queries_stats[] = $id . ': ' . $count;

	foreach ($connection->profiling as $note)
	{
		$queries_time += $note[0];
	}
}

$html .= PHP_EOL . PHP_EOL . '<!-- ' . \ICanBoogie\format
(
	'icybee v:version - in :elapsed ms (core: :elapsed_core ms, db: :elapsed_queries ms), using :memory-usage (peak: :memory-peak), queries: :queries-count (:queries-details)', array
	(
		'version' => \Icybee\VERSION,
		'elapsed' => number_format(($time_end - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2, '.', ''),
		'elapsed_core' => number_format(($time - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2, '.', ''),
		'elapsed_queries' => number_format($queries_time * 1000, 2, '.', ''),
		'memory-usage' => number_format(memory_get_usage() / (1024 * 1024), 3) . 'Mb',
		'memory-peak' => number_format(memory_get_peak_usage() / (1024 * 1024), 3) . 'Mb',
		'queries-count' => $queries_count,
		'queries-details' => implode(', ', $queries_stats)
	)
)

. ' -->';

// var_dump(\ICanBoogie\I18n\Translator::$missing);

return $html;