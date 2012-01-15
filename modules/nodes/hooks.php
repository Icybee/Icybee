<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Nodes;

use ICanBoogie\Event;

use BrickRouge\Element;

class Hooks
{
	public static function markup_node_navigation(array $args, \WdPatron $patron, $template)
	{
		global $document;

		$document->css->add('public/page.css');

		$record = $patron->context['this'];

		$list = null;
		$cycle = null;

		$list_url = $record->url('list');

		if ($list_url)
		{
			$list = '<div class="list"><a href="' . wd_entities($list_url) . '">' . t('All records') . '</a></div>';
		}

		$next = null;
		$previous = null;
		$next_record = $record->next;
		$previous_record = $record->previous;

		if ($next_record)
		{
			$title = $next_record->title;

			$next = new Element
			(
				'a', array
				(
					Element::INNER_HTML => wd_entities(wd_shorten($title, 48, 1)),

					'class' => "next",
					'href' => $next_record->url,
					'title' => t('Next: :title', array(':title' => $title))
				)
			);
		}

		if ($previous_record)
		{
			$title = $previous_record->title;

			$previous = new Element
			(
				'a', array
				(
					Element::INNER_HTML => wd_entities(wd_shorten($title, 48, 1)),

					'class' => "previous",
					'href' => $previous_record->url,
					'title' => t('Previous: :title', array(':title' => $title))
				)
			);
		}

		if ($next || $previous)
		{
			$cycle = '<div class="cycle">' . $next . ' ' . $previous . '</div>';
		}

		if ($list || $cycle)
		{
			return '<div class="node-navigation">' . $list . $cycle . '</div>';
		}
	}

	public static function on_modules_activate(Event $event)
	{
		\ICanBoogie\Modules\Nodes\Module::create_default_routes();
	}

	public static function on_modules_deactivate(Event $event)
	{
		\ICanBoogie\Modules\Nodes\Module::create_default_routes();
	}
}