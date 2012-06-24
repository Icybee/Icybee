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

use ICanBoogie\Exception;

class Content extends Node
{
	const SUBTITLE = 'subtitle';
	const BODY = 'body';
	const EXCERPT = 'excerpt';
	const DATE = 'date';
	const EDITOR = 'editor';
	const IS_HOME_EXCLUDED = 'is_home_excluded';

	public $subtitle;
	public $body;
	public $excerpt;
	public $date;
	public $editor;
	public $is_home_excluded;

	public function __construct(Model $model)
	{
		parent::__construct($model);

		if (empty($this->excerpt))
		{
			unset($this->excerpt);
		}
	}

	/**
	 * @var bool|null true is the cache should be used, false if the cache should not be used, and
	 * null if we don't yet know if the cache should be used or not.
	 */
	private static $use_cache;
	private static $cache_model;

	/**
	 * Renders the body of the activerecord into a string.
	 *
	 * The body is rendered using the editor that was used to edit the content.
	 *
	 * A cache maybe used to store et retrieve the rendered content.
	 *
	 * @return string The rendered body.
	 */
	public function __toString()
	{
		global $core;

		if (self::$use_cache === null)
		{
			self::$use_cache = !empty($core->registry['contents.cache_rendered_body']);
		}

		$rendered_body = $body = $this->body;

		try
		{
			if (self::$use_cache)
			{
				if (self::$cache_model === null)
				{
					self::$cache_model = $core->models['contents/cache'];
				}

				$modified = $this->modified;
				$cached = self::$cache_model->select('body')->where('nid = ? AND timestamp = ?', $this->nid, $modified)->rc;

				if ($cached)
				{
					return $cached;
				}

				if ($this->editor)
				{
					$rendered_body = call_user_func($this->editor . '_WdEditorElement::render', $body);
				}

				if ($rendered_body && $rendered_body != $body)
				{
					self::$cache_model->save
					(
						array
						(
							'nid' => $this->nid,
							'timestamp' => $modified,
							'body' => $rendered_body
						),

						null, array('on duplicate' => true)
					);
				}
			}
			else if ($this->editor)
			{
				$rendered_body = call_user_func($this->editor . '_WdEditorElement::render', $body);
			}
		}
		catch (\Exception $e)
		{
			$rendered_body = $e->getMessage();

			echo $rendered_body;
		}

		return $rendered_body;
	}

	/**
	 * Returns the year of the contents' date.
	 *
	 * @return string
	 */
	protected function get_year()
	{
		return substr($this->date, 0, 4);
	}

	/**
	 * Returns the month of the contents' date.
	 *
	 * @return string
	 */
	protected function get_month()
	{
		return substr($this->date, 5, 2);
	}

	/**
	* Returns the day of the contents' date.
	*
	* @return string
	*/
	protected function get_day()
	{
		return substr($this->date, 8, 2);
	}

	/**
	 * Overrides the method to support the `date` property.
	 *
	 * @see ActiveRecord\Node::get_previous()
	 */
	protected function get_previous()
	{
		return $this->_model->own->visible->where('nid != ? AND date <= ?', $this->nid, $this->date)->order('date DESC, created DESC, nid DESC')->one;
	}

	/**
	 * Overrides the method to support the `date` property.
	 *
	 * @see ActiveRecord\Node::get_next()
	 */
	protected function get_next()
	{
		return $this->_model->own->visible->where('nid != ? AND date > ?', $this->nid, $this->date)->order('date, created, nid')->one;
	}

	protected function get_excerpt()
	{
		return \ICanBoogie\excerpt((string) $this);
	}

	public function excerpt($limit=55)
	{
		return isset($this->excerpt) ? \ICanBoogie\excerpt($this->excerpt, $limit) : \ICanBoogie\excerpt((string) $this, $limit);
	}
}