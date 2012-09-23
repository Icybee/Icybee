<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Nodes;

use ICanBoogie\I18n;
use ICanBoogie\Event;

class Node extends \ICanBoogie\ActiveRecord
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

	/**
	 * Node key.
	 *
	 * @var int
	 */
	public $nid;

	/**
	 * Identifier of the owner of the node.
	 *
	 * @var int
	 */
	public $uid;

	/**
	 * Identifier of the site the node belongs to.
	 *
	 * The property is empty of the node is not bound to a website.
	 *
	 * @var int
	 */
	public $siteid;

	/**
	 * Title of the node.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Slug of the node.
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * Constructor of the node.
	 *
	 * @var string
	 */
	public $constructor;

	/**
	 * Date the node was created.
	 *
	 * @var string
	 */
	public $created;

	/**
	 * Date the node was modified.
	 *
	 * @var string
	 */
	public $modified;

	/**
	 * Whether the node is online or not.
	 *
	 * @var bool
	 */
	public $is_online;

	/**
	 * Language of the node.
	 *
	 * The property is empty of the node is not bound to a language.
	 *
	 * @var string
	 */
	public $language;

	/**
	 * Identifier of the node this node is translating.
	 *
	 * The property is empty if the node is not translating another node.
	 *
	 * @var int
	 */
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

	/**
	 * Adds the {@link slug} property if it is defined.
	 *
	 * @see ICanBoogie.ActiveRecord::__sleep()
	 */
	public function __sleep()
	{
		$keys = parent::__sleep();

		if (isset($this->slug))
		{
			$keys['slug'] = 'slug';
		}

		return $keys;
	}

	public function __get($property)
	{
		$value = parent::__get($property);

		if ($property === 'css_class_names')
		{
			new Node\AlterCSSClassNamesEvent($this, array('names' => &$value));
		}

		return $value;
	}

	protected function get_slug()
	{
		return \ICanBoogie\normalize($this->title);
	}

	/**
	 * Return the previous online sibling for the node.
	 *
	 * @return Node|bool The previous sibling for the node or false if there is none.
	 */
	protected function get_previous()
	{
		return $this->_model->own->visible->where('nid != ? AND created <= ?', $this->nid, $this->created)->order('created DESC')->one;
	}

	/**
	* Return the next online sibling for the node.
	*
	* @return Node|bool The next sibling for the node or false if there is none.
	*/
	protected function get_next()
	{
		return $this->_model->own->visible->where('nid != ? AND created > ?', $this->nid, $this->created)->order('created')->one;
	}

	/**
	 * Return the user object for the owner of the node.
	 *
	 * @return object The user object for the owner of the node.
	 */
	protected function get_user()
	{
		global $core;

		return $core->models['users'][$this->uid];
	}

	private static $translations_keys;

	protected function get_translations_keys()
	{
		global $core;

		//$native_language = $this->siteid ? $this->site->native->language : I18n::$native;
		$native_language = $this->siteid ? $this->site->native->language : $core->language; // FIXME-20120720

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

	protected function get_translation()
	{
		return $this->translation();
	}

	protected function get_translations()
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
	protected function get_native()
	{
		return $this->nativeid ? $this->_model[$this->nativeid] : $this;
	}

	/**
	 * Returns the CSS class of the node.
	 *
	 * @return string
	 */
	protected function get_css_class()
	{
		return $this->css_class();
	}

	/**
	 * Returns the CSS class names of the node.
	 *
	 * @return array[string]mixed
	 */
	protected function get_css_class_names()
	{
		return array
		(
			'type' => 'node',
			'id' => 'node-' . $this->nid,
			'slug' => 'node-slug-' . $this->slug,
			'constructor' => 'constructor-' . \ICanBoogie\normalize($this->constructor)
		);
	}

	/**
	 * Return the CSS class of the node.
	 *
	 * @param string|array $modifiers CSS class names modifiers
	 *
	 * @return string
	 */
	public function css_class($modifiers=null)
	{
		return \Icybee\render_css_class($this->css_class_names, $modifiers);
	}
}

namespace Icybee\Modules\Nodes\Node;

/**
 * Event class for the `Icybee\Modules\Nodes\Node::alter_css_class_names` event.
 */
class AlterCSSClassNamesEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the class names to alter.
	 *
	 * @var array[string]mixed
	 */
	public $names;

	/**
	 * The event is constructed with the type `alter_css_class_names`.
	 *
	 * @param \Icybee\Modules\Nodes\Node $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Modules\Nodes\Node $target, array $properties)
	{
		parent::__construct($target, 'alter_css_class_names', $properties);
	}
}