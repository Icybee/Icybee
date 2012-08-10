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
use Brickrouge\Element;

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

	static public function content(array $args, Patron\Engine $patron, $template)
	{
		global $core;

		$render = $args['render'];

		if ($render == 'none')
		{
			return;
		}

		$page = $core->request->context->page;
		$pageid = $page->nid;
		$contentid = $args['id'];
		$contents = array_key_exists($contentid, $page->contents) ? $page->contents[$contentid] : null;

		if (!$contents && !empty($args['inherit']))
		{
//			\ICanBoogie\log('Contents %id is not defined for page %title, but is inherited, searching for heritage...', array('%id' => $contentid, '%title' => $page->title));

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

//				\ICanBoogie\log('... try with home page %title', array('%title' => $page->title));

				if (isset($node_contents[$contentid]))
				{
					$contents = $node_contents[$contentid];
				}
			}

//			\ICanBoogie\log('... and found: \1', array($contents));
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

		$element = new Element
		(
			'div', array
			(
				'id' => 'content-' . $contentid,
				'class' => 'editor-' . \ICanBoogie\normalize($editor)
			)
		);

		if (version_compare(PHP_VERSION, '5.3.4', '>='))
		{
			$patron->context['self']['element'] = $element;
		}
		else // COMPAT
		{
			$self = $patron->context['self'];
			$self['element'] = $element;
			$patron->context['self'] = $self;
		}

		$rc = $template ? $patron($template, $rendered) : $rendered;

		if (!$rc)
		{
			return;
		}

		if (preg_match('#\.html$#', $page->template) && empty($args['no-wrapper']))
		{
			$element[Element::INNER_HTML] = $rc;

			$rc = $element;

// 			$rc = '<div id="content-' . $contentid . '" class="editor-' . \ICanBoogie\normalize($editor) . '">' . $rc . '</div>';
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

	static public function sitemap(array $args, Patron\Engine $patron, $template)
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
//		\ICanBoogie\log('resolve parentid: \1', array($parentid));

		if (!is_numeric($parentid))
		{
			$parent = self::model()->find_by_path($parentid);

			if (!$parent)
			{
				return null;
			}

			$parentid = $parent->nid;
		}

		return $parentid;
	}
}

class site_pages_languages_WdMarkup extends patron_WdMarkup
{
	public function __invoke(array $args, Patron\Engine $patron, $template)
	{
		global $core;

		$page = $core->request->context->page;
		$source = $page->node ?: $page;
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
				'translations_by_languages' => &$translations_by_language
			),

			$page
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
				'class' => "btn language--$language" . ($language == $page_language ? ' active' : ''),
				'render' => $language == $page_language ? '<strong>' . $language . '</strong>' : '<a href="' . $translation->url . '">' . $language . '</a>',
				'node' => $translation
			);
		}

		Event::fire
		(
			'alter.page.languages', array
			(
				'languages' => &$languages
			),

			$page
		);

		/*
		$rc = '<ul>';

		foreach ($languages as $language)
		{
			$rc .= '<li class="' . $language['class'] . '">' . $language['render'] . '</li>';
		}

		$rc .= '</ul>';
		*/

		$rc = '<div class="btn-group i18n-languages">';

		foreach ($languages as $language => $options)
		{
			$rc .= '<a class="' . $options['class'] . '" href="' . $options['node']->url . '">' . $language . '</a>';
		}

		$rc .= '</div>';

		return $rc;
	}
}

class site_pages_navigation_WdMarkup extends patron_WdMarkup
{
	public function __invoke(array $args, Patron\Engine $patron, $template)
	{
		global $core;

		$page = $core->request->context->page;
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
					$parent = $this->model->find_by_path($parentid);

					$parentid = $parent->nid;
				}
			}
		}

		$min_child = $args['min-child'];

		$blueprint = $this->model->blueprint($page->siteid);

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
			$navigation_builder = function(array $tree, $depth=1) use(&$navigation_builder)
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
						$rc .= $navigation_builder($branch->children, $depth + 1);
					}

					$rc .= '</li>';
				}

				return '<ol class="' . ($depth == 1 ? 'nav' : 'dropdown-menu') . ' lv' . $depth . '">' . $rc . '</ol>';
			};

			$subset->populate();

			$rc = $template ? $patron($template, $tree) : $navigation_builder($tree);
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

class site_pages_sitemap_WdMarkup extends patron_WdMarkup
{
	public function __invoke(array $args, Patron\Engine $patron, $template)
	{
		global $core;

		$this->model = $core->models['pages'];

		$blueprint = $this->model->blueprint($core->site_id);
		$subset = $blueprint->subset
		(
			null, null, function($branch)
			{
				return ($branch->pattern || !$branch->is_online);
			}
		);

		$subset->populate();

		return self::build($subset->tree);
	}

	static protected function build($branches, $depth=false, $min_child=false, $level=1)
	{
		$rc = '';

		foreach ($branches as $branch)
		{
			if ($level == 1 && ($min_child !== false && (count($branch->children) < $min_child)))
			{
				continue;
			}

			$class = '';

			if ($branch->children)
			{
				$class .= 'has-children';
			}

			$record = $branch->record;

			if (!empty($record->is_active))
			{
				if ($class)
				{
					$class .= ' ';
				}

				$class .= 'active';
			}

			$class .= ' nid-' . $record->nid;

			$rc .=  $class ? '<li class="' . trim($class) . '">' : '<li>';
			$rc .= '<a href="' . $record->url . '">' . \ICanBoogie\escape($record->label) . '</a>';

			if (($depth === false || $level < $depth) && $branch->children)
			{
				$rc .= self::build($branch->children, $depth, $min_child, $level + 1);
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