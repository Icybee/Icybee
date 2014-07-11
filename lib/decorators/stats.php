<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use ICanBoogie\Debug;
use ICanBoogie\HTTP\Dispatcher;
use ICanBoogie\Operation;

/**
 * Decorates the specified component with various statistics.
 */
class StatsDecorator extends \Brickrouge\Decorator
{
	/**
	 * Adds statistic information about the response if it is of type "text/html" and the request
	 * is not XHR.
	 *
	 * @param Dispatcher\DispatchEvent $event
	 * @param Dispatcher $target
	 */
	static public function on_dispatcher_dispatch(Dispatcher\DispatchEvent $event, Dispatcher $target)
	{
		if ($event->request->is_xhr)
		{
			return;
		}

		#
		# We chain the event so that it is called after the event callbacks have been processed,
		# for instance a _cache_ callback that may cache the response.
		#

		$event->chain(function(Dispatcher\DispatchEvent $event, Dispatcher $target)
		{
			$response = $event->response;

			if (!$response || $response->body === null || $response instanceof Operation\Response
			|| $response->content_type->type != 'text/html')
			{
				return;
			}

			$response->body = new StatsDecorator($response->body);
		});
	}

	public function render()
	{
		global $core;

		$now = microtime(true);

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
				$queries_time += $note[1] - $note[0];
			}
		}

		$html = $this->component . PHP_EOL . '<!-- ' . \ICanBoogie\format
		(
			'Icybee – in :elapsed ms (core: :elapsed_core ms, db: :elapsed_queries ms), using :memory-usage (peak: :memory-peak), queries: :queries-count (:queries-details)', array
			(
				'elapsed' => number_format(($now - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2, '.', ''),
				'elapsed_core' => number_format(($_SERVER['ICANBOOGIE_READY_TIME_FLOAT'] - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2, '.', ''),
				'elapsed_queries' => number_format($queries_time * 1000, 2, '.', ''),
				'memory-usage' => number_format(memory_get_usage() / (1024 * 1024), 3) . 'Mb',
				'memory-peak' => number_format(memory_get_peak_usage() / (1024 * 1024), 3) . 'Mb',
				'queries-count' => $queries_count,
				'queries-details' => implode(', ', $queries_stats)
			)
		);

		if (Debug::is_dev() || $core->user->is_admin)
		{
			$html .= "\n\n" . $this->render_events();
			$html .= "\n\n" . $this->render_queries();
			$html .= $this->render_translations();
		}

		$html .= ' -->';

		return $html;
	}

	protected function render_events()
	{
		$events = \ICanBoogie\Event::$profiling['hooks'];

		$max_length_type = 0;
		$max_length_callback = 0;

		$time_total = 0;
		$time_by_type = array();

		$calls_total = 0;
		$calls_by_type = array();

		foreach ($events as $i => $event)
		{
			list(, $type, $callback, $time) = $event;

			if (!($callback instanceof \Closure))
			{
				continue;
			}

			$reflection = new \ReflectionFunction($callback);

			$callback = '(closure) ' . \ICanBoogie\strip_root($reflection->getFileName()) . '@' . $reflection->getStartLine();

			$events[$i][2] = $callback;
		}

		foreach ($events as $event)
		{
			list(, $type, $callback, $time) = $event;

			if (!is_string($callback))
			{
				$callback = implode('::', $callback);
			}

			$max_length_type = max($max_length_type, strlen($type));
			$max_length_callback = max($max_length_callback, strlen($callback));

			if (empty($time_by_type[$type]))
			{
				$time_by_type[$type] = 0;
			}

			$time_total += $time;
			$time_by_type[$type] += $time;

			if (empty($calls_by_type[$type]))
			{
				$calls_by_type[$type] = 0;
			}

			$calls_total++;
			$calls_by_type[$type]++;
		}

		$line_width = 4 + 2 + 8 + 1 + $max_length_type + 1 + $max_length_callback;

		$title = sprintf("Events: %d in %.3f ms", count($events), $time_total * 1000);

		$html = PHP_EOL;
		$html .= $title . PHP_EOL;
		$html .= str_repeat('—', strlen($title)) . PHP_EOL;

		foreach ($events as $i => $event)
		{
			list(, $type, $callback, $time) = $event;

			if ($callback instanceof \Closure)
			{
				$callback = 'Closure 0x' . spl_object_hash($callback);
			}
			else if (is_array($callback))
			{
				$callback = (is_string($callback[0]) ? $callback[0] : get_class($callback[0])) . '::' . $callback[1];
			}

			$html .= sprintf("%4d: %2.3f %-{$max_length_type}s %-{$max_length_callback}s", $i, $time * 1000, $type, $callback) . PHP_EOL;
		}

		#
		# unused events
		#

		$html .= "\n\nUnused events\n";
		$html .= "—————————————\n";

		$time_ref = $_SERVER['REQUEST_TIME_FLOAT'];

		foreach (\ICanBoogie\Event::$profiling['unused'] as $i => $trace)
		{
			list($time, $type) = $trace;

			$html .= sprintf("%4d: %9s %s\n", $i, sprintf("%5.3f", ($time - $time_ref) * 1000), $type);
		}

		return $html;
	}

	protected function render_queries()
	{
		global $core;

		$html = '';

		foreach ($core->connections as $id => $connection)
		{
			$traces = $connection->profiling;
			$total_time = 0;
			$lines = '';
			$line_width = 0;

			foreach ($traces as $i => $trace)
			{
				list($start, $finish, $query) = $trace;

				$total_time += $finish - $start;
				$line = sprintf("%4d: %9s %s\n", $i, sprintf("%5.3f", ($finish - $start) * 1000), $query);
				$line_width = max($line_width, strlen($line));
				$lines .= $line;
			}

			$header = sprintf("Queries to '%s': %d in %s ms", $id, count($traces), sprintf("%5.3f", ($total_time) * 1000));

			$html .= $header . PHP_EOL;
			$html .= str_repeat('—', strlen($header)) . PHP_EOL;
			$html .= $lines . PHP_EOL . PHP_EOL;
		}

		return $html;
	}

	protected function render_translations()
	{
		$html = '';

		foreach (\ICanBoogie\I18n\Translator::$missing as $str)
		{
			$html .= $str . PHP_EOL;
		}

		if (!$html)
		{
			return;
		}

		return "\n\nMissing translations\n————————————————————\n\n$html\n";
	}
}