<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\ActiveRecord;
use ICanBoogie\Event;
use BrickRouge\Element;

class site_pages_WdMarkups extends patron_markups_WdHooks
{
	static protected $module;

	static protected function module($name='pages')
	{
		if (!self::$module)
		{
			global $core;

			self::$module = $core->modules[$name];
		}

		return self::$module;
	}

	static protected function model($name='pages')
	{
		return parent::model($name);
	}

	static public function content(array $args, WdPatron $patron, $template)
	{
		global $page;

		$render = $args['render'];

		if ($render == 'none')
		{
			return;
		}

		$pageid = $page->nid;
		$contentid = $args['id'];
		$contents = array_key_exists($contentid, $page->contents) ? $page->contents[$contentid] : null;

		if (!$contents && !empty($args['inherit']))
		{
//			wd_log('Contents %id is not defined for page %title, but is inherited, searching for heritage...', array('%id' => $contentid, '%title' => $page->title));

			$node = $page->parent;

			while ($node)
			{
				$node_contents = $node->contents;

				if (empty($node_contents[$contentid]))
				{
					$node = $node->parent;

					continue;
				}

				$contents = $node_contents[$contentid];

				break;
			}

			#
			# maybe the home page define the contents, but because the home page is not the parent
			# of pages on single language sites, we have to check it now.
			#

			if (!$contents)
			{
				$node_contents = $page->home->contents;

//				wd_log('... try with home page %title', array('%title' => $page->title));

				if (isset($node_contents[$contentid]))
				{
					$contents = $node_contents[$contentid];
				}
			}

//			wd_log('... and found: \1', array($contents));
		}

		$editor = null;
		$rendered = null;

		if ($contents)
		{
			$editor = $contents->editor;
			$rendered = $contents->render();
		}

		if (!$rendered)
		{
			return;
		}

		$rc = $template ? $patron($template, $rendered) : $rendered;

		if (!$rc)
		{
			return;
		}

		if (preg_match('#\.html$#', $page->template) && empty($args['no-wrapper']))
		{
			$rc = '<div id="content-' . $contentid . '" class="editor-' . wd_normalize($editor) . '">' . $rc . '</div>';
		}

		$rc = self::handle_external_anchors($rc);

		return $rc;
	}

	/**
	 * Adds a blank target to external href.
	 *
	 * @param string $html
	 */
	static protected function handle_external_anchors($html)
	{
		return preg_replace_callback('#<a\s+[^>]+>#', array(__CLASS__, 'handle_external_anchors_callback'), $html);
	}

	static public function handle_external_anchors_callback($matches)
	{
		$str = array_shift($matches);

		preg_match_all('#([a-zA-Z0-9\-]+)\="([^"]+)#', $str, $matches, 0, PREG_SET_ORDER);

		if (empty($matches[1]))
		{
			return $str;
		}

		$attributes = array_combine($matches[1], $matches[2]);

		if (isset($attributes['href']))
		{
			if (preg_match('#^http(s)?://#', $attributes['href'], $m))
			{
				$attributes['target'] = '_blank';
			}
		}

		$str = '<a';

		foreach ($attributes as $attribute => $value)
		{
			$str .= ' ' . $attribute . '="' . $value . '"';
		}

		$str .= '>';

		return $str;
	}

	static public function sitemap(array $args, WdPatron $patron, $template)
	{
		$parentid = $args['parent'];

		if ($parentid && is_string($parentid))
		{
			$parentid = $parentid ? self::resolveParent($parentid) : true;
		}

		if ($parentid === null)
		{
			$parentid = 0;
		}

		$maxnest = $args['nest'];

		return self::sitemap_callback($parentid, $maxnest);
	}

	static protected function sitemap_callback($parentid, $maxnest=false, $level=1)
	{
		$parent = null;

		if (is_object($parentid))
		{
			$parent = $parentid;
			$parentid = $parent->nid;
		}

		$children = self::model()->where('is_online = 1 AND parentid = ? AND pattern = ""', $parentid)->order('weight, created')->all;

		if (!$children)
		{
			return;
		}

		$rc = '';
		$pad = str_repeat("\t", $level + 1);

		foreach ($children as $child)
		{
			if ($parent)
			{
				$child->parent = $parent;
			}

			$rc .= $pad . '<li><a href="' . $child->url . '">' . $child->label . '</a>' . PHP_EOL;

			if ($maxnest === false || $level < $maxnest)
			{
				$rc .= self::sitemap_callback($child, $maxnest, $level + 1);
			}

			$rc .= $pad . '</li>' . PHP_EOL;
		}

		$rc = str_repeat("\t", $level) . '<ul class="level' . $level . '">' . PHP_EOL . $rc . str_repeat("\t", $level) . '</ul>';

		return $rc;
	}

	static protected function resolveParent($parentid)
	{
//		wd_log('resolve parentid: \1', array($parentid));

		if (!is_numeric($parentid))
		{
			$parent = self::model()->loadByPath($parentid);

			if (!$parent)
			{
				return null;
			}

			$parentid = $parent->nid;
		}

		return $parentid;
	}

	static public function call_view(array $args, WdPatron $patron, $template)
	{
		// TODO-20101216: The view should handle parsing template or not

		return $render = view_WdEditorElement::render($args['name'], $patron, $template);

		return $template ? $patron($template, $render) : $render;
	}
}

class site_pages_languages_WdMarkup extends patron_WdMarkup
{
	public function __invoke(array $args, WdPatron $patron, $template)
	{
		global $core, $page;

		$source = isset($page->node) ? $page->node : $page;
		$translations = $source->translations;
		$translations_by_language = array();

		if ($translations)
		{
			$translations[$source->nid] = $source;
			$translations_by_language = array_flip($core->models['sites']->select('language')->where('status = 1')->order('weight, siteid')->all(PDO::FETCH_COLUMN));

			if ($source instanceof ActiveRecord\Page)
			{
				foreach ($translations as $translation)
				{
					if (!$translation->is_accessible)
					{
						continue;
					}

					$translations_by_language[$translation->language] = $translation;
				}
			}
			else // nodes
			{
				foreach ($translations as $translation)
				{
					if (!$translation->is_online)
					{
						continue;
					}

					$translations_by_language[$translation->language] = $translation;
				}
			}

			foreach ($translations_by_language as $language => $translation)
			{
				if (is_object($translation))
				{
					continue;
				}

				unset($translations_by_language[$language]);
			}
		}

		if (!$translations_by_language)
		{
			$translations_by_language = array
			(
				($source->language ? $source->language : $page->language) => $source
			);
		}

		Event::fire
		(
			'alter.page.languages:before', array
			(
				'target' => $page,
				'translations_by_languages' => &$translations_by_language
			)
		);

		if ($template)
		{
			return $patron($template, $translations_by_language);
		}

		$page_language = $page->language;
		$languages = array();

		foreach ($translations_by_language as $language => $translation)
		{
			$languages[$language] = array
			(
				'class' => $language . ($language == $page_language ? ' active' : ''),
				'render' => $language == $page_language ? '<strong>' . $language . '</strong>' : '<a href="' . $translation->url . '">' . $language . '</a>',
				'node' => $translation
			);
		}

		Event::fire
		(
			'alter.page.languages', array
			(
				'target' => $page,
				'languages' => &$languages
			)
		);

		$rc = '<ul>';

		foreach ($languages as $language)
		{
			$rc .= '<li class="' . $language['class'] . '">' . $language['render'] . '</li>';
		}

		$rc .= '</ul>';

		return $rc;
	}
}

class site_pages_navigation_WdMarkup extends patron_WdMarkup
{
	public function __invoke(array $args, WdPatron $patron, $template)
	{
		global $core, $page;

		$this->model = $core->models['pages'];

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

//			wd_log('from node: \1', array($node));

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
					$parent = $this->model->loadByPath($parentid);

					$parentid = $parent->nid;
				}
			}
		}

		$entries = $this->model->loadAllNested($page->siteid, $parentid, $depth);

		$rc = null;

		if ($entries)
		{
			/* TODO-20110701: OBSOLETE
			#
			# set active pages
			#

			$node = $page;

			while ($node)
			{
				$node->is_active = true;
				$node = $node->parent;
			}
			*/

	//		wd_log_time('navigation start');

			$entries = self::navigation_filter($entries);

			$rc = $template ? $patron($template, $entries) : self::navigation_builder($entries, $depth, $args['min-child']);
		}

		Event::fire
		(
			'alter.markup.navigation', array
			(
				'rc' => &$rc,
				'page' => $page,
				'entries' => $entries,
				'args' => $args
			)
		);

		return $rc;
	}

	static protected function navigation_filter($entries)
	{
		$filtered = array();

		foreach ($entries as $entry)
		{
			if ($entry->pattern || !$entry->is_online || $entry->is_navigation_excluded)
			{
				continue;
			}

			//$entry->is_active = !empty($entry->is_active); TODO-20110701: OBSOLETE
			$entry->navigation_children = isset($entry->children) ? self::navigation_filter($entry->children) : array();

			$filtered[] = $entry;
		}

		return $filtered;
	}

	static protected function navigation_builder($entries, $depth, $min_child, $level=1)
	{
		$rc = '';

		foreach ($entries as $entry)
		{
			if ($level == 1 && ($min_child !== false && (count($entry->navigation_children) < $min_child)))
			{
				continue;
			}

			$class = $entry->css_class;

			if ($entry->navigation_children)
			{
				$class .= ' has-children';
			}

			$rc .=  $class ? '<li class="' . $class . '">' : '<li>';
			$rc .= '<a href="' . $entry->url . '">' . $entry->label . '</a>';

			if ($level < $depth && $entry->navigation_children)
			{
				$rc .= self::navigation_builder($entry->navigation_children, $depth, $min_child, $level + 1);
			}

			$rc .= '</li>';
		}

		if (!$rc)
		{
			return;
		}

		return '<ol class="lv' . $level . '">' . $rc . '</ol>';
	}


	public static function navigation_leaf(array $args, WdPatron $patron, $template)
	{
		global $page;

		$node = $page;

		while ($node)
		{
			if ($node->navigation_children)
			{
				break;
			}

			$node = $node->parent;
		}

		Event::fire
		(
			'render.markup.navigation_leaf:before', array
			(
				'node' => &$node,
				'template' => &$template
			)
		);

		$rc = null;

		if ($node)
		{
			$rc = $patron($template, $node);
		}

		Event::fire
		(
			'render.markup.navigation_leaf', array
			(
				'node' => &$node,
				'rc' => &$rc,
				'template' => &$template
			)
		);

		return $rc;
	}


	/*
	static public function navigation_leaf(array $args, WdPatron $patron, $template)
	{
		global $core, $page;

		$level = $args['level'];
		$depth = $args['depth'];

		$start_page = $page;

		while ($start_page && $start_page->depth > $level)
		{
			$start_page = $page->parent;
		}

		$records = $core->models['pages']->loadAllNested($page->siteid, $start_page->nid, $depth);

		if (!$records)
		{
			return;
		}

		$records = self::navigation_filter($records);

		if (!$records)
		{
			return;
		}

		$menu = self::navigation_builder($records, $depth, false);
		$link = wd_entities($start_page->url);
		$label = wd_entities($start_page->label);

		return <<<EOT

<h5><a href="$link">$label</h5>
$menu
EOT;
	}
	*/
}

class site_pages_sitemap_WdMarkup extends patron_WdMarkup
{
	public function __invoke(array $args, WdPatron $patron, $template)
	{
		global $core, $page;

		$this->model = $core->models['pages'];

		$entries = $this->model->loadAllNested($page->siteid);

		if (!$entries)
		{
			return;
		}

		$entries = self::filter($entries);

		return self::build($entries);
	}

	static protected function filter($entries)
	{
		$filtered = array();

		foreach ($entries as $entry)
		{
			if ($entry->pattern || !$entry->is_online)
			{
				continue;
			}

			$entry->is_active = !empty($entry->is_active);
			$entry->children = isset($entry->children) ? self::filter($entry->children) : array();

			$filtered[] = $entry;
		}

		return $filtered;
	}

	static protected function build($entries, $depth=false, $min_child=false, $level=1)
	{
		$rc = '';

		foreach ($entries as $entry)
		{
			if ($level == 1 && ($min_child !== false && (count($entry->children) < $min_child)))
			{
				continue;
			}

			$class = '';

			if ($entry->children)
			{
				$class .= 'has-children';
			}

			if (!empty($entry->is_active))
			{
				if ($class)
				{
					$class .= ' ';
				}

				$class .= 'active';
			}

			$rc .=  $class ? '<li class="' . $class . '">' : '<li>';
			$rc .= '<a href="' . $entry->url . '">' . $entry->label . '</a>';

			if (($depth === false || $level < $depth) && $entry->children)
			{
				$rc .= self::build($entry->children, $depth, $min_child, $level + 1);
			}

			$rc .= '</li>';
		}

		if (!$rc)
		{
			return;
		}

		return '<ol class="lv' . $level . '">' . $rc . '</ol>';
	}
}