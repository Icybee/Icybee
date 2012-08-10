<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Exception;
use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\Event;
use ICanBoogie\Module;

class system_nodes_view_WdMarkup extends patron_WdMarkup
{
	/**
	 * Publish a template binded with the entry defined by the `select` parameter.
	 *
	 * If the entry failed to be loaded, a HTTPException is thrown with a 404 code.
	 *
	 * If the entry is offline and the user has no permission to access it, a HTTPException is
	 * thrown with the 401 code.
	 *
	 * If the entry is offline and the user has permission to acces it, the title of the entry is
	 * marked with '=!='.
	 *
	 * @param array $args
	 * @param Patron\Engine $patron
	 * @param unknown_type $template
	 */

	public function __invoke(array $args, Patron\Engine $patron, $template)
	{
		global $core;

		$args += array
		(
			'constructor' => 'nodes'
		);

		$this->constructor = $args['constructor'];
		$this->model = $core->models[$this->constructor];

//		var_dump($this->constructor, $args);

		/*
		if (isset($args['constructor']))
		{
			if (!is_array($args['select']))
			{
				if (is_numeric($args['select']))
				{
					$args['select'] = array
					(
						'nid' => $args['select']
					);
				}
				else
				{
					$args['select'] = array
					(
						'slug' => $args['select']
					);
				}
			}

			$args['select']['constructor'] = $args['constructor'];
		}
		*/

		#
		# are we in a view ?
		#

		$page = $core->request->context->page;
		$body = $page->body;
		$is_view = ($body instanceof ICanBoogie\ActiveRecord\Pages\Content && $body->editor == 'view' && preg_match('#/view$#', $body->content));
		$exception_class = $is_view ? 'ICanBoogie\HTTPException' : 'ICanBoogie\Exception';

		if (empty($args['select']))
		{
			return;
		}

		$entry = $this->load($args['select']);

		if (!$entry)
		{
			throw new $exception_class
			(
				'The requested entry was not found: !select', array
				(
					'!select' => $args['select']
				),

				404
			);
		}
		else if (!$entry->is_online)
		{
			if (!$core->user->has_permission(Module::PERMISSION_ACCESS, $entry->constructor))
			{
				throw new $exception_class
				(
					'The requested entry %uri requires authentication.', array
					(
						'%uri' => $entry->constructor . '/' . $entry->nid
					),

					401
				);
			}

			$entry->title .= ' ✎';
		}

		Event::fire
		(
			'nodes_load', array
			(
				'nodes' => array($entry)
			),

			$patron
		);

		$rc = $this->publish($patron, $template, $entry);

		#
		# set page node
		#

		if ($is_view && $body->content == $entry->constructor . '/view')
		{
			$page->node = $entry;
			$page->title = $entry->title;
		}

		return $rc;
	}

	protected function load($select)
	{
		$nid = $this->nid_from_select($select);

		if (!$nid)
		{
			throw new Exception('Unable to find a node matching the following parameters: \1', array($select));
		}

		return $this->model[$nid];
	}

	protected function parse_conditions($conditions)
	{
		if (is_numeric($conditions))
		{
			return array
			(
				array('`nid` = ?'),
				array($conditions)
			);
		}
		else if (is_string($conditions))
		{
			global $core;

			$site = $core->site;

			return array
			(
				array
				(
					'(`slug` = ? OR `title` = ?)',
					'(`language` = ? OR `language` = "")',
					'(siteid = ? OR siteid = 0)'
				),

				array
				(
					$conditions, $conditions,
					$site->$language,
					$site->siteid
				)
			);
		}

		// TODO-20100630: The whole point of the inherited markups is to get rid of the
		// Model::parseConditions() method.

		return $this->model->parseConditions($conditions);
	}

	protected function nid_from_select($select)
	{
		if (is_numeric($select))
		{
			return $select;
		}
		else if (is_string($select))
		{
			global $core;

			$page = $core->request->context->page;

			return $this->model->select('nid')
			->where('(slug = ? OR title = ?) AND (siteid = ? OR siteid = 0) AND (language = ? OR language = "")', $select, $select, $page->siteid, $page->site->language)
			->order('language DESC')
			->rc;
		}
		else if (isset($select[Node::NID]))
		{
			return $select[Node::NID];
		}

		list($conditions, $args) = $this->parse_conditions($select);

//		\ICanBoogie\log(__FILE__ . ':: nid from: (\3) \1\2', array($conditions, $args, get_class($this)));

		return $this->model->select('nid')->where(implode(' AND ', $conditions), $args)->order('created DESC')->rc;
	}
}

class system_nodes_list_WdMarkup extends patron_WdMarkup
{
	/*
	protected $constructor = 'nodes';
	protected $invoked_constructor;
	*/

	public function __invoke(array $args, Patron\Engine $patron, $template)
	{
		global $core;
		/*
		$this->invoked_constructor = null;

		if (isset($args['constructor']))
		{
			$this->invoked_constructor = $args['constructor'];
		}
		*/

		$args += array
		(
			'constructor' => 'nodes'
		);

		$this->constructor = $args['constructor'];
		$this->model = $core->models[$this->constructor];

		$select = isset($args['select']) ? $args['select'] : array();
		$order = isset($args['order']) ? $args['order'] : 'created DESC';
		$range = $this->get_range($select, $args);

		$entries = $this->loadRange($select, $range, $order);

		if (!$entries)
		{
			return;
		}

		if (version_compare(PHP_VERSION, '5.3.4', '>='))
		{
			$patron->context['self']['range'] = $range;
		}
		else // COMPAT
		{
			$self = $patron->context['self'];
			$self['range'] = $range;
			$patron->context['self'] = $self;
		}

// 		$patron->context['self']['range'] = $range;

		return $this->publish($patron, $template, $entries);
	}

	/*
	protected function get_model()
	{
		global $core;

		return $core->models[$this->invoked_constructor ? $this->invoked_constructor : $this->constructor];
	}
	*/

	protected function get_range($select, array $args)
	{
		// TODO-20100817: move this to invoke, and maybe create a parse_select function ?

		$limit = isset($args['limit']) ? $args['limit'] : null;

		if ($limit === null)
		{
			$limit = $this->get_limit();
		}

		$rc = array
		(
			'count' => null,
			'limit' => $limit
		);

		if (!empty($select['page']))
		{
			//$page = isset($select['page']) ? $select['page'] : (isset($args['page']) ? $args['page'] : 0);

			$rc['page'] = $select['page'];
		}
		else if (!empty($args['page']))
		{
			$rc['page'] = $args['page'];
		}
		else if (isset($args['offset']))
		{
			$rc['offset'] = $args['offset'];
		}

		return $rc;
	}

	protected function get_limit($which='list', $default=10)
	{
		global $core;

		$constructor = /*$this->invoked_constructor ? $this->invoked_constructor :*/ $this->constructor;

		return $core->site->metas->get(strtr($constructor, '.', '_') . '.limits.' . $which, $default);
	}

	protected function loadRange($select, &$range, $order='created desc')
	{
		list($conditions, $args) = $this->parse_conditions($select);

		$model = $this->model;

		/*
		if ($this->invoked_constructor)
		{
			global $core;

			$model = $core->models[$this->invoked_constructor];
		}
		*/

		$arq = $model->where(implode(' AND ', $conditions), $args);

		$range['count'] = $arq->count;

		$offset = 0;
		$limit = $range['limit'];

		if (isset($range['page']))
		{
			$offset = $range['page'] * $limit;
		}
		else if (isset($range['offset']))
		{
			$offset = $range['offset'];
		}

		return $arq->order("$order, title")->limit($offset, $limit)->all;
	}

	protected function parse_conditions($select)
	{
		global $core;

		$constructor = /*$this->invoked_constructor ? $this->invoked_constructor :*/ $this->constructor;

		$conditions = array();
		$args = array();

		if (is_array($select))
		{
			foreach ($select as $identifier => $value)
			{
				switch ($identifier)
				{
					case 'categoryslug':
					{
						$ids = $core->models['terms/nodes']
						->select('nid')
						->joins(':vocabulary')
						->joins('INNER JOIN {prefix}vocabulary__scopes scope USING(vid)')
						->where('termslug = ? AND scope.constructor = ?', $value, $constructor)
						->all(PDO::FETCH_COLUMN);

						if (!$ids)
						{
							throw new Exception('There is no entry in the %category category', array('%category' => $value));
						}

						$conditions[] = 'nid IN(' . implode(',', $ids) . ')';
					}
					break;
				}
			}
		}

		#
		#
		#

		$conditions['is_online'] = 'is_online = 1';

		$conditions['language'] = '(language = "" OR language = :language)';
		$args['language'] = $core->language;

		$conditions['constructor'] = 'constructor = :constructor';
		$args['constructor'] = $constructor;

		return array($conditions, $args);
	}
}