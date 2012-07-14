<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Pages;

use ICanBoogie\ActiveRecord\Page;

use Brickrouge\Element;

class NavigationBranchElement extends Element
{
	protected $page;

	public function __construct(Page $page, array $attributes=array())
	{
		$this->page = $page;

		parent::__construct
		(
			'div', $attributes + array
			(
				'class' => 'nav-branch'
			)
		);
	}

	protected function build_blueprint($page, $parent_id)
	{
		global $core;

		$trail = array();
		$p = $page;

		while ($p)
		{
			$trail[$p->nid] = $p->nid;

			$p = $p->parent;
		}

		$blueprint = $core->models['pages']->blueprint($this->page->siteid);

		$build_blueprint = function($parent_id, $max_depth) use (&$build_blueprint, $blueprint, $trail)
		{
			if (empty($blueprint->children[$parent_id]))
			{
				return;
			}

			$children = array();

			foreach ($blueprint->children[$parent_id] as $nid)
			{
				$page_blueprint = $blueprint->index[$nid];

				if (!$page_blueprint->is_online || $page_blueprint->is_navigation_excluded || $page_blueprint->pattern)
				{
					continue;
				}

				$page_blueprint = clone $page_blueprint;

				unset($page_blueprint->parent);
				$page_blueprint->children = array();

				if (isset($trail[$nid]) && $page_blueprint->depth < $max_depth)
				{
					$page_blueprint->children = $build_blueprint($nid, $max_depth);
				}

				$children[] = $page_blueprint;
			}

			return $children;
		};

		return $build_blueprint($parent_id, 2);
	}

	protected function render_inner_html()
	{
		global $core;

		$page = $this->page;
		$parent = $this->page;

		while ($parent->parent)
		{
			$parent = $parent->parent;
		}

		$parent_id = $parent->nid;

		#

		$tree_blueprint = $this->build_blueprint($page, $parent_id);

		$ids = array();

		$collect_ids = function(array $blueprint) use(&$collect_ids, &$ids)
		{
			foreach ($blueprint as $page_blueprint)
			{
				$ids[] = $page_blueprint->nid;

				if ($page_blueprint->children)
				{
					$collect_ids($page_blueprint->children);
				}
			}
		};

		$html = '<h5><a href="' . \Brickrouge\escape($parent->url) . '">' . \Brickrouge\escape($parent->label) . '</a></h5>';

		if ($tree_blueprint)
		{
			$collect_ids($tree_blueprint);

			$pages = $core->models['pages']->find($ids);
			$html .= $this->render_page_recursive($tree_blueprint, $pages, $parent->depth + 1, 0);
		}

		return $html;
	}

	protected function render_page_recursive(array $children, $pages, $depth, $relative_depth)
	{
		$html = '';

		foreach ($children as $blueprint_child)
		{
			$child = $pages[$blueprint_child->nid];

			$html .= '<li class="' . $child->css_class('active trail') . '"><a href="' . \Brickrouge\escape($child->url) . '">' . \Brickrouge\escape($child->label) . '</a>';

			if ($blueprint_child->children)
			{
				$html .= $this->render_page_recursive($blueprint_child->children, $pages, $depth + 1, $relative_depth + 1);
			}

			$html .= '</li>';
		}

		if ($html)
		{
			return <<<EOT
<ul class="nav nav-depth-$depth nav-relative-depth-$relative_depth">$html</ul>
EOT;
		}
	}
}