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

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\I18n;
use ICanBoogie\Event;

class Node extends ActiveRecord
{
	const NID = 'nid';
	const UID = 'uid';
	const SITEID = 'siteid';
	const TITLE = 'title';
	const SLUG = 'slug';
	const CONSTRUCTOR = 'constructor';
	const CREATED = 'created';
	const MODIFIED = 'modified';
	const IS_ONLINE = 'is_online';
	const LANGUAGE = 'language';
	const NATIVEID = 'nativeid';

	public $nid;
	public $uid;
	public $siteid;
	public $title;
	public $slug;
	public $constructor;
	public $created;
	public $modified;
	public $is_online;
	public $language;
	public $nativeid;

	/**
	 * Creates a Node instance.
	 *
	 * The `slug` property is unset if it is empty but the `title` property is defined. The slug
	 * will be created on the fly when accessed throught the `slug` property.
	 */
	public function __construct($model)
	{
		if (!$this->slug && $this->title)
		{
			unset($this->slug);
		}

		parent::__construct($model);
	}

	protected function __get_slug()
	{
		return wd_normalize($this->title);
	}

	/**
	 * Return the previous online sibling for the node.
	 *
	 * @return Node|bool The previous sibling for the node or false if there is none.
	 */
	protected function __get_previous()
	{
		return $this->_model->own->visible->where('nid != ? AND created <= ?', $this->nid, $this->created)->order('created DESC')->one;
	}

	/**
	* Return the next online sibling for the node.
	*
	* @return Node|bool The next sibling for the node or false if there is none.
	*/
	protected function __get_next()
	{
		return $this->_model->own->visible->where('nid != ? AND created > ?', $this->nid, $this->created)->order('created')->one;
	}

	/**
	 * Return the user object for the owner of the node.
	 *
	 * @return object The user object for the owner of the node.
	 */
	protected function __get_user()
	{
		global $core;

		return $core->models['users'][$this->uid];
	}

	private static $translations_keys;

	protected function __get_translations_keys()
	{
		global $core;

		$native_language = $this->siteid ? $this->site->native->language : I18n::$native;

		if (!self::$translations_keys)
		{
			$groups = $core->models['nodes']->select('nativeid, nid, language')->where('nativeid != 0')->order('language')->all(\PDO::FETCH_GROUP | \PDO::FETCH_NUM);
			$keys = array();

			foreach ($groups as $native_id => $group)
			{
				foreach ($group as $row)
				{
					list($nativeid, $tlanguage) = $row;

					$keys[$native_id][$nativeid] = $tlanguage;
				}
			}

			foreach ($keys as $native_id => $translations)
			{
				$all = array($native_id => $native_language) + $translations;

				foreach ($translations as $nativeid => $tlanguage)
				{
					$keys[$nativeid] = $all;
					unset($keys[$nativeid][$nativeid]);
				}
			}

			self::$translations_keys = $keys;
		}

		$nid = $this->nid;

		return isset(self::$translations_keys[$nid]) ? self::$translations_keys[$nid] : null;
	}

	/**
	 * Returns the translation in the specified language for the record, or the record itself if no
	 * translation can be found.
	 *
	 * @param string $language The language for the translation. If the language is empty, the
	 * current language (as defined by the I18n class) is used.
	 *
	 * @return Node The translation for the record, or the record itself if
	 * no translation could be found.
	 */
	public function translation($language=null)
	{
		global $core;

		if (!$language)
		{
			$language = $core->language;
		}

		$translations = $this->translations_keys;

		if ($translations)
		{
			$translations = array_flip($translations);

			if (isset($translations[$language]))
			{
				return $this->_model->find($translations[$language]);
			}
		}

		return $this;
	}

	protected function __get_translation()
	{
		return $this->translation();
	}

	protected function __get_translations()
	{
		$translations = $this->translations_keys;

		if (!$translations)
		{
			return;
		}

		return $this->_model->find(array_keys($translations));
	}

	/**
	 *
	 * Return the native node for this translated node.
	 */
	protected function __get_native()
	{
		return $this->nativeid ? $this->_model[$this->nativeid] : $this;
	}

	protected function __get_css_class()
	{
		$initial = $this->initial_css_class;

		Event::fire('get_css_class', array('rc' => &$initial), $this);

		return implode(' ', $initial);
	}

	protected function __get_initial_css_class()
	{
		return array
		(
			'type' => 'node',
			'id' => 'node-' . $this->nid,
			'slug' => 'node-slug-' . $this->slug,
			'constructor' => 'constructor-' . wd_normalize($this->constructor)
		);
	}
}