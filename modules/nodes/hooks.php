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
use ICanBoogie\I18n;
use ICanBoogie\Operation\BeforeProcessEvent;

use Brickrouge\A;
use Brickrouge\Element;

class Hooks
{
	/*
	 * Markups
	 */

	public static function markup_node_navigation(array $args, \WdPatron $patron, $template)
	{
		global $core;

		$core->document->css->add('public/page.css');

		$record = $patron->context['this'];

		$list = null;
		$cycle = null;

		$list_url = $record->url('list');

		if ($list_url)
		{
			$list = '<div class="list"><a href="' . \ICanBoogie\escape($list_url) . '">' . I18n\t('All records') . '</a></div>';
		}

		$next = null;
		$previous = null;
		$next_record = $record->next;
		$previous_record = $record->previous;

		if ($next_record)
		{
			$title = $next_record->title;

			$next = new A
			(
				\ICanBoogie\escape(\ICanBoogie\shorten($title, 48, 1)), $next_record->url, array
				(
					'class' => "next",
					'title' => I18n\t('Next: :title', array(':title' => $title))
				)
			);
		}

		if ($previous_record)
		{
			$title = $previous_record->title;

			$previous = new A
			(
				\ICanBoogie\escape(\ICanBoogie\shorten($title, 48, 1)), $previous_record->url, array
				(
					'class' => "previous",
					'title' => I18n\t('Previous: :title', array(':title' => $title))
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

	/*
	 * Events
	 */

	public static function on_modules_activate(Event $event)
	{
		\ICanBoogie\Modules\Nodes\Module::create_default_routes();
	}

	public static function on_modules_deactivate(Event $event)
	{
		\ICanBoogie\Modules\Nodes\Module::create_default_routes();
	}

	/**
	 * Checks if the role to be deleted is used or not.
	 *
	 * @param BeforeProcessEvent $event
	 * @param \ICanBoogie\Modules\Users\DeleteOperation $operation
	 */
	public static function before_delete_user(BeforeProcessEvent $event, \ICanBoogie\Modules\Users\DeleteOperation $operation)
	{
		global $core;

		$uid = $operation->key;
		$count = $core->models['nodes']->find_by_uid($uid)->count;

		if (!$count)
		{
			return;
		}

		$event->errors['uid'] = I18n\t('The user %name is used by :count nodes.', array('name' => $operation->record->name, ':count' => $count));
	}
}