<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Contents;

class Content extends \Icybee\Modules\Nodes\Node
{
	const SUBTITLE = 'subtitle';
	const BODY = 'body';
	const EXCERPT = 'excerpt';
	const DATE = 'date';
	const EDITOR = 'editor';
	const IS_HOME_EXCLUDED = 'is_home_excluded';

	/**
	 * Subtitle.
	 *
	 * @var string
	 */
	public $subtitle;

	/**
	 * Body of the content.
	 *
	 * The body needs to be rendered by its editor in order to obtain the real body.
	 *
	 * @var string
	 */
	public $body;

	/**
	 * An excerpt of the body.
	 *
	 * @var string
	 */
	public $excerpt;

	/**
	 * Date of the content.
	 *
	 * @var string
	 */
	public $date;

	/**
	 * The identifier of the editor that was used to edit the body.
	 *
	 * @var string
	 */
	public $editor;

	/**
	 * `true` if the content should not appear on the "home" view.
	 *
	 * @var bool
	 */
	public $is_home_excluded;

	/**
	 * The {@link $excerpt} property is unset if it is empty, so that it is created from the body
	 * when read for the first time.
	 *
	 * @param Model $model
	 */
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

	private $rendered_body;

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

		$rendered_body = $this->rendered_body;

		if ($rendered_body)
		{
			return $rendered_body;
		}

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

				$nid = $this->nid;
				$modified = $this->modified;
				$cached = self::$cache_model->select('body')->filter_by_nid_and_timestamp($nid, $modified)->rc;

				if ($cached)
				{
					return $cached;
				}

				if ($this->editor)
				{
					$rendered_body = $this->render_body();
				}

				if ($rendered_body && $rendered_body != $body)
				{
					self::$cache_model->save
					(
						array
						(
							'nid' => $nid,
							'timestamp' => $modified,
							'body' => $rendered_body
						),

						null, array('on duplicate' => true)
					);
				}
			}
			else if ($this->editor)
			{
				$rendered_body = $this->render_body();
			}
		}
		catch (\Exception $e)
		{
			$rendered_body = $e->getMessage();
		}

		$this->rendered_body = $rendered_body;

		return $rendered_body;
	}

	/**
	 * Renders the body using the associated editor.
	 *
	 * @return string
	 */
	private function render_body()
	{
		$body = $this->body;

		if (!$this->editor)
		{
			return $body;
		}

		$editor = \ICanBoogie\Core::get()->editors[$this->editor];

		return (string) $editor->render($editor->unserialize($body));
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
	 * @see \Icybee\Modules\Nodes\Node::get_previous()
	 */
	protected function get_previous()
	{
		$ids = $this->_model->select('nid')->order('date, created, nid')->own->visible->all(\PDO::FETCH_COLUMN);
		$key = array_search($this->nid, $ids);

		return $key ? $this->_model[$ids[$key - 1]] : null;
	}

	/**
	 * Overrides the method to support the `date` property.
	 *
	 * @see \Icybee\Modules\Nodes\Node::get_next()
	 */
	protected function get_next()
	{
		$ids = $this->_model->select('nid')->order('date, created, nid')->own->visible->all(\PDO::FETCH_COLUMN);
		$key = array_search($this->nid, $ids);

		return $key < count($ids) - 1 ? $this->_model[$ids[$key + 1]] : null;
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