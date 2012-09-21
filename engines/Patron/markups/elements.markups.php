<?php

/*
 * This file is part of the Patron package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Events;
use ICanBoogie\Modules\Pages\PageController;

use Brickrouge\Alert;
use Brickrouge\Pager;

class patron_elements_WdMarkups
{
	public static function pager(array $args, Patron\Engine $patron, $template)
	{
		extract($args);

		if (!$range)
		{
			if (isset($patron->context['range']))
			{
				$range = $patron->context['range'];
			}
		}

		if ($range)
		{
			$count = $range['count'];
			$limit = $range['limit'];
			$page = isset($range['page']) ? $range['page'] : 0;

			if (isset($range['with']))
			{
				$with = $range['with'];
			}
		}

		$pager = new Pager
		(
			'div', array
			(
				Pager::T_COUNT => $count,
				Pager::T_LIMIT => $limit,
				Pager::T_POSITION => $page,
				Pager::T_NO_ARROWS => $noarrows,
				Pager::T_WITH => $with
			)
		);

		return $template ? $patron($template, $pager) : (string) $pager;
	}

	/**
	 * Adds or return the CSS assets of the document.
	 *
	 * @param array $args
	 * @param Patron\Engine $engine
	 * @param mixed $template
	 *
	 * @return void|string
	 */
	static public function document_css(array $args, Patron\Engine $engine, $template)
	{
		global $core;

		if (isset($args['add']))
		{
			$file = $engine->get_file();

			\ICanBoogie\log(__FILE__ . '::' . __FUNCTION__ . '::file: \1', array($file));

			$core->document->css->add($args['add'], dirname($file));

			return;
		}

		$key = '<!-- document-css-placeholder-' . md5(uniqid()) . ' -->';

		Events::attach
		(
			'ICanBoogie\Modules\Pages\PageController::render', function(PageController\RenderEvent $event) use($engine, $template, $key)
			{
				#
				# The event is chained so that is gets executed once the event chain has been
				# processed.
				#

				$event->chain(function(PageController\RenderEvent $event) use($engine, $template, $key)
				{
					global $core;

					$document = $core->document;

					$html = $template ? $engine($template, $document->css) : (string) $document->css;

					$event->html = str_replace($key, $html, $event->html);
				});
			}
		);

		return PHP_EOL . $key;
	}

	static public function document_js(array $args, Patron\Engine $patron, $template)
	{
		global $document;

		return $template ? $patron($template, $document->js) : (string) $document->js;
	}
}