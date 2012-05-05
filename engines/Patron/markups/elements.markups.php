<?php

/*
 * This file is part of the Patron package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Brickrouge\Alert;
use Brickrouge\Pager;

class patron_elements_WdMarkups
{
	public static function pager(array $args, WdPatron $patron, $template)
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

	static public function document_css(array $args, WdPatron $patron, $template)
	{
		global $document;

		if (isset($args['add']))
		{
			$file = $patron->get_file();

			\ICanBoogie\log(__FILE__ . '::' . __FUNCTION__ . '::file: \1', array($file));

			$document->css->add($args['add'], dirname($file));

			return;
		}

		return $template ? $patron($template, $document->css) : (string) $document->css;
	}

	static public function document_js(array $args, WdPatron $patron, $template)
	{
		global $document;

		return $template ? $patron($template, $document->js) : (string) $document->js;
	}
}