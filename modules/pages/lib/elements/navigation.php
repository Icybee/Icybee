<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Pages;

use ICanBoogie\Event;

use Brickrouge\Element;

class NavigationElement extends Element // TODO-20120922: rewrite this element
{
	static protected function render_navigation_tree(array $tree, $depth=1)
	{
		$rc = '';

		foreach ($tree as $branch)
		{
			$record = $branch->record;
			$class = $record->css_class('-constructor -slug');

			$rc .=  $class ? '<li class="' . $class . '">' : '<li>';
			$rc .= '<a href="' . $record->url . '">' . $record->label . '</a>';

			if ($branch->children)
			{
				$rc .= static::render_navigation_tree($branch->children, $depth + 1);
			}

			$rc .= '</li>';
		}

		return '<ol class="' . ($depth == 1 ? 'nav' : 'dropdown-menu') . ' lv' . $depth . '">' . $rc . '</ol>';
	}

	static public function markup(array $args, \Patron\Engine $patron, $template)
	{
		global $core;

		$page = $core->request->context->page;
		$mode = $args['mode'];

		if ($mode == 'leaf')
		{
			$node = $page;

			while ($node)
			{
				if ($node->navigation_children)
				{
					break;
				}

				$node = $node->parent;
			}

			if (!$node)
			{
				return;
			}

			return $patron($template, $node);
		}


		$model = $core->models['pages'];















		$depth = $args['depth'];

		if ($args['from-level'])
		{
			$node = $page;
			$from_level = $args['from-level'];

			#
			# The current page level is smaller than the page level requested, the navigation is
			# canceled.
			#

			if ($node->depth < $from_level)
			{
				return;
			}

			while ($node->depth > $from_level)
			{
				$node = $node->parent;
			}

//			\ICanBoogie\log('from node: \1', array($node));

			$parentid = $node->nid;
		}
		else
		{
			$parentid = $args['parent'];

			if (is_object($parentid))
			{
				$parentid = $parentid->nid;
			}
			else
			{
				if ($parentid && !is_numeric($parentid))
				{
					$parent = $model->find_by_path($parentid);

					$parentid = $parent->nid;
				}
			}
		}

		$blueprint = $model->blueprint($page->siteid);
		$min_child = $args['min-child'];

		$subset = $blueprint->subset
		(
			$parentid, $depth === null ? null : $depth - 1, function($branch) use($min_child)
			{
				return (!$branch->is_online || $branch->is_navigation_excluded || $branch->pattern);
			}
		);

		$rc = null;
		$tree = $subset->tree;

		if ($tree)
		{
			$subset->populate();

			$rc = $template ? $patron($template, $tree) : static::render_navigation_tree($tree);
		}

		Event::fire
		(
			'alter.markup.navigation', array
			(
				'rc' => &$rc,
				'page' => $page,
				'blueprint' => $subset,
				'args' => $args
			)
		);

		return $rc;
	}
}