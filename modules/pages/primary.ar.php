<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord;

use ICanBoogie\Route;


/**
 * @property $parent Page Parent page of the page.
 */
class Page extends Node
{
	const PARENTID = 'parentid';
	const LOCATIONID = 'locationid';
	const PATTERN = 'pattern';
	const WEIGHT = 'weight';
	const TEMPLATE = 'template';
	const LABEL = 'label';
	const IS_NAVIGATION_EXCLUDED = 'is_navigation_excluded';

	public $parentid;
	public $locationid;
	public $pattern;
	public $weight;
	public $template;
	public $label;
	public $is_navigation_excluded;

	/**
	 * @var string Part of the URL captured by the pattern.
	 */
	public $url_part;

	/**
	 * @var array Variables captured from the URL using the pattern.
	 */
	public $url_variables = array();

	/**
	 * @var Node Node object currently acting as the body of the page.
	 */
	public $node;

	/**
	 * @var bool true if the page is cachable, false otherwise.
	 */
	public $cachable = true;

	/**
	 * The site the page belongs too.
	 *
	 * @var \ICanBoogie\ActiveRecord\Site
	 */
	public $site;

	/**
	 * Whether the page is accessible or not.
	 *
	 * @var bool
	 */
	public $is_accessible;

	/**
	 * Wheter the page is active or not.
	 *
	 * @var bool
	 */

	public $is_active;

	/**
	 * Whether the page is the home page of the site or not.
	 *
	 * @var bool
	 */
	public $is_home;

	/**
	 * Whether the page is in the navigation trail or not.
	 *
	 * @var bool
	 */
	public $is_trail;

	public function __construct($model='pages')
	{
		if (empty($this->language))
		{
			unset($this->language);
		}

		if (empty($this->label))
		{
			unset($this->label);
		}

		if (empty($this->template))
		{
			unset($this->template);
		}

		unset($this->site);
		unset($this->is_accessible);
		unset($this->is_active);
		unset($this->is_home);
		unset($this->is_trail);

		parent::__construct($model);
	}

	public function __sleep()
	{
		$keys = parent::__sleep();

		if (isset($this->language))
		{
			$keys['language'] = 'language';
		}

		if (isset($this->label))
		{
			$keys['label'] = 'label';
		}

		if (isset($this->template))
		{
			$keys['template'] = 'template';
		}

		return $keys;
	}

	protected function volatile_get_language()
	{
		return $this->siteid ? $this->site->language : null;
	}

	/**
	 * Returns the previous online sibling for the page.
	 *
	 * @return Page|false The previous sibling, or false if there is none.
	 *
	 * @see ICanBoogie\ActiveRecord.Node::get_previous()
	 */
	protected function get_previous()
	{
		return $this->_model
		->where('is_online = 1 AND nid != ? AND parentid = ? AND siteid = ? AND weight <= ?', $this->nid, $this->parentid, $this->siteid, $this->weight)
		->order('weight desc, created desc')->one;
	}

	/**
	 * Returns the next online sibling for the page.
	 *
	 * @return Page|false The next sibling, or false if there is none.
	 *
	 * @see ICanBoogie\ActiveRecord.Node::get_next()
	 */
	protected function get_next()
	{
		return $this->_model
		->where('is_online = 1 AND nid != ? AND parentid = ? AND siteid = ? AND weight >= ?', $this->nid, $this->parentid, $this->siteid, $this->weight)
		->order('weight, created')->one;
	}

	/**
	 * Returns the URL of the page.
	 *
	 * @return string
	 */
	protected function get_url()
	{
		global $core;

		if ($this->location)
		{
			return $this->location->url;
		}

		$url_pattern = $this->url_pattern;

		if ($this->is_home)
		{
			return $url_pattern;
		}

		$url = null;

		if (Route::is_pattern($url_pattern))
		{
			if ($this->url_variables)
			{
				$url = Route::format($url_pattern, $this->url_variables);

//				\ICanBoogie\log('URL %pattern rescued using URL variables', array('%pattern' => $pattern));
			}
			else
			{
				$page = isset($core->request->context->page) ? $core->request->context->page : null;

				if ($page && $page->url_variables)
				{
					$url = Route::format($url_pattern, $page->url_variables);

// 					\ICanBoogie\log("URL pattern %pattern was resolved using current page's variables", array('%pattern' => $pattern));
				}
				else
				{
					$url = '#url-pattern-could-not-be-resolved';
				}
			}
		}
		else
		{
			$url = $url_pattern;
		}

		return $url;
	}

	/**
	 * Returns the absulte URL of the pages.
	 *
	 * @return string The absolute URL of the page.
	 *
	 * @see site_pages_view_WdHooks::get_absolute_url()
	 */
	protected function get_absolute_url()
	{
		$site = $this->site;

		return $site->url . substr($this->url, strlen($site->path));
	}

	public function translation($language=null)
	{
		$translation = parent::translation($language);

		if ($translation->nid != $this->nid && isset($this->url_variables))
		{
			$translation->url_variables = $this->url_variables;
		}

		return $translation;
	}

	protected function get_translations()
	{
		$translations = parent::get_translations();

		if (!$translations || empty($this->url_variables))
		{
			return $translations;
		}

		foreach ($translations as $translation)
		{
			$translation->url_variables = $this->url_variables;
		}

		return $translations;
	}

	// TODO-20100706: Shouldn't url_pattern be null if there was no pattern in the path ? We
	// wouldn't have to check for '<' to know if the URL has a pattern, on the other hand we would
	// have to do two pass each time we try to get the URL.

	protected function get_url_pattern()
	{
		global $core;

		$site = $this->site;

		if ($this->is_home)
		{
			return $site->path . '/';
		}

		$parent = $this->parent;
		$pattern = $this->pattern;

		$rc = ($parent ? $parent->url_pattern : $site->path . '/') . ($pattern ? $pattern : $this->slug);

		if ($this->has_child)
		{
			$rc .= '/';
		}
		else
		{
			$rc .= $this->extension;
		}

		return $rc;
	}

	protected function get_extension()
	{
		$template = $this->template;

		$pos = strrpos($template, '.');
	 	$extension = substr($template, $pos);

	 	return $extension;
	}

	/**
	 * Checks if the page record is the home page.
	 *
	 * A page is considered a home page when the page has no parent and its weight value is zero.
	 *
	 * @return bool true if the page record is the home page, false otherwise.
	 */
	protected function get_is_home()
	{
		return (!$this->parentid && $this->is_online && $this->weight == 0);
	}

	/**
	 * Checks if the page record is the active page.
	 *
	 * The global variable `page` must be defined in order to identify the active page.
	 *
	 * @return bool true if the page record is the active page, false otherwise.
	 */
	protected function get_is_active()
	{
		global $core;

		return $core->request->context->page->nid == $this->nid;
	}

	/**
	 * Checks if the page record is in the active page trail.
	 *
	 * The global variable `page` must be defined in order to identifiy the active page.
	 *
	 * @return bool true if the page is in the active page trail, false otherwise.
	 */
	protected function get_is_trail()
	{
		global $core;

		$node = $core->request->context->page;

		while ($node)
		{
			if ($node->nid != $this->nid)
			{
				$node = $node->parent;

				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Returns the location target for the page record.
	 *
	 * @return ICanBoogie\ActiveRecord\Page|null The location target, or null if there is none.
	 */
	protected function get_location()
	{
		return $this->locationid ? $this->_model[$this->locationid] : null;
	}

	/**
	 * Returns the home page for the page record.
	 *
	 * @return ICanBoogie\ActiveRecord\Page
	 */
	protected function get_home()
	{
		return $this->_model->find_home($this->siteid);
	}

	/**
	 * Returns the parent of the page.
	 *
	 * @return ICanBoogie\ActiveRecord\Page|null The parent page or null is the page has no parent.
	 */
	protected function get_parent()
	{
		return $this->parentid ? $this->_model[$this->parentid] : null;
	}

	/**
	 * Return the online children page for this page.
	 *
	 * TODO-20100629: The `children` virtual property should return *all* the children for the page,
	 * we should create a `online_children` virtual property that returns only _online_ children,
	 * or maybe a `accessible_children` virtual property ?
	 */
	protected function get_children()
	{
		$blueprint = $this->_model->blueprint($this->siteid);
		$pages = $blueprint['pages'];

		if (!$pages[$this->nid]->children)
		{
			return array();
		}

		$ids = array();

		foreach ($pages[$this->nid]->children as $nid => $child)
		{
			if (!$child->is_online)
			{
				continue;
			}

			$ids[] = $nid;
		}

		return $this->_model->find($ids);
	}

	/**
	 * Returns the page's children that are online and part of the navigation.
	 *
	 * @return array[int]Page
	 */
	protected function get_navigation_children()
	{
		$index = $this->_model->blueprint($this->siteid)->index;

		if (!$index[$this->nid]->children)
		{
			return array();
		}

		$ids = array();

		foreach ($index[$this->nid]->children as $nid => $child)
		{
			if (!$child->is_online || $child->is_navigation_excluded || $child->pattern)
			{
				continue;
			}

			$ids[] = $nid;
		}

		if (!$ids)
		{
			return array();
		}

		return $this->_model->find($ids);
	}

	/**
	 * Checks if the page as at least one child.
	 *
	 * @return boolean
	 */
	protected function get_has_child()
	{
		return $this->_model->blueprint($this->siteid)->has_children($this->nid);
	}

	/**
	 * Returns the number of child of the page.
	 *
	 * @return int
	 */
	protected function get_children_count()
	{
		return $this->_model->blueprint($this->siteid)->children_count($this->nid);
	}

	/**
	 * Returns the label for the page.
	 *
	 * This function is only called if no label is defined, in which case the title of the page is
	 * returned instead.
	 */
	protected function get_label()
	{
		return $this->title;
	}

	/**
	 * Returns the depth level of this page in the navigation tree.
	 */
	protected function get_depth()
	{
		return $this->parent ? $this->parent->depth + 1 : 0;
	}

	/**
	 * Returns if the page is accessible or not in the navigation tree.
	 */

	protected function get_is_accessible()
	{
		global $core;

		if ($core->user->is_guest && $this->site->status != 1)
		{
			return false;
		}

		return ($this->parent && !$this->parent->is_accessible) ? false : $this->is_online;
	}

	protected function get_template()
	{
		if (isset($this->layout))
		{
			return $this->layout;
		}

		if ($this->is_home)
		{
			return 'home.html';
		}
		else if ($this->parent && !$this->parent->is_home)
		{
			return $this->parent->template;
		}

		return 'page.html';
	}

	/**
	 * Returns the contents of the page as an array.
	 *
	 * Keys of the array are the contentid, values are the contents objects.
	 *
	 * @return array[string]\ICanBoogie\ActiveRecord\Pages\Content
	 */
	protected function get_contents()
	{
		global $core;

		$entries = $core->models['pages/contents']->find_by_pageid($this->nid);
		$contents = array();

		foreach ($entries as $entry)
		{
			$contents[$entry->contentid] = $entry;
		}

		return $contents;
	}

	/**
	 * Returns the body of this page.
	 *
	 * The body is the page's contents object with the 'body' identifier.
	 *
	 * @return \ICanBoogie\ActiveRecord\Pages\Content
	 */
	protected function get_body()
	{
		$contents = $this->contents;

		return isset($contents['body']) ? $contents['body'] : null;
	}

	/**
	 * Replaces `type` value by "page" and `id` value by "page-id-<nid>".
	 *
	 * The following class names are added:
	 *
	 * - `slug`: "page-slug-<slug>"
	 * - `home`: true if the page is the home page.
	 * - `active`: true if the page is the active page.
	 * - `trail`: true if the page is in the breadcrumb trail.
	 * - `node-id`: "node-id-<nid>" if the page displays a node.
	 * - `node-constructor`: "node-constructor-<normalized_constructor>" if the page displays a node.
	 * - `template`: "template-<name>" the name of the page's template, without its extension.
	 *
	 * @see ICanBoogie\ActiveRecord.Node::get_css_class_names()
	 */
	protected function get_css_class_names()
	{
		$names = array_merge
		(
			parent::get_css_class_names(), array
			(
				'type' => 'page',
				'id' => 'page-id-' . $this->nid,
				'slug' => 'page-slug-'. $this->slug,
				'home' => ($this->home->nid == $this->nid),
				'active' => $this->is_active,
				'trail' => (!$this->is_active && $this->is_trail),
				'template' => 'template-' . preg_replace('#\.(html|php)$#', '', $this->template)
			)
		);

		if (isset($this->node))
		{
			$node = $this->node;

			$names['node-id'] = 'node-id-' . $node->nid;
			$names['node-constructor'] = 'node-constructor-' . \ICanBoogie\normalize($node->constructor);
		}

		return $names;
	}

	/**
	 * Return the description for the page.
	 */

	// TODO-20101115: these should be methods added by the "firstposition' module

	protected function get_description()
	{
		return $this->metas['description'];
	}

	protected function get_document_title()
	{
		return $this->metas['document_title'] ? $this->metas['document_title'] : $this->title;
	}
}