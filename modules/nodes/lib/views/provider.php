<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Nodes;

use ICanBoogie\ActiveRecord\Node;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Event;
use ICanBoogie\Exception;

class ViewProvider extends \Icybee\Modules\Views\ActiveRecordProvider
{
	/**
	 * @throws a HTTPException with code 404 if no record matching the conditions could be found
	 * and the view is of type "view".
	 *
	 * @throws a HTTPException with code 401 if the record is offline and user don't have access
	 * permission and the view is of type "view".
	 *
	 * @see BriskView.Provider::__invoke()
	 */
	public function __invoke()
	{
		global $core;

		$rc = parent::__invoke();

		if ($rc instanceof Node)
		{
			if (!$rc)
			{
				throw new Exception\HTTP('The requested record was not found.', array(), 404);
			}

			if (!$rc->is_online)
			{
				if (!$core->user->has_permission(\ICanBoogie\Module::PERMISSION_ACCESS, $rc->constructor))
				{
					throw new Exception\HTTP('The requested record requires authentication.', array(), 401);
				}

				$rc->title .= ' ✎';
			}

			$page = isset($core->request->context->page) ? $core->request->context->page : null;

			if ($page)
			{
				$page->title = $rc->title;

				if ($this->view->type == 'view')
				{
					$page->node = $rc;
				}
			}

			$this->fire_load($rc);
		}

		return $rc;
	}

	/**
	 * Returns the conditions unaltered.
	 *
	 * @see Icybee\Views.Provider::alter_conditions()
	 */
	protected function alter_conditions(array $conditions)
	{
		return $conditions;
	}

	/**
	 * Alters the query to search for records from the same constructor, a similar site and a
	 * similar language.
	 *
	 * The method also alters the query if the `nid` or `slug` conditions are defined.
	 *
	 * Finaly if the return type is RETURN_MANY the query is altered to search for online nodes
	 * only.
	 *
	 * @see BriskView.ActiveRecordProvider::alter_query()
	 */
	protected function alter_query(Query $query, array $conditions)
	{
		$query->own->similar_site->similar_language;

		if (isset($conditions['nid']))
		{
			$query->where('nid = ?', $conditions['nid']);
		}
		else if (isset($conditions['slug']))
		{
			$query->where('slug = ?', $conditions['slug']);
		}

		if ($this->returns == self::RETURNS_MANY)
		{
			$query->where('is_online = 1');
		}

		return parent::alter_query($query, $conditions)->order('created DESC');
	}

	/**
	 * Returns the rendering context unaltered.
	 *
	 * @see Icybee\Views.Provider::alter_context()
	 */
	protected function alter_context(\BlueTihi\Context $context, Query $query, array $conditions)
	{
		return $context;
	}

	/**
	 * @return ActiveRecord|array[ActiveRecord]|null If the view's type is "view" the method returns an
	 * ActiveRecord, or null if no record matching the conditions could be found, otherwise the
	 * method returns an array of ActiveRecord.
	 *
	 * @see BriskView.ActiveRecordProvider::extract_result()
	 */
	protected function extract_result(Query $query)
	{
		if ($this->returns == self::RETURNS_ONE)
		{
			return $query->one;
		}

		return parent::extract_result($query);
	}

	protected function fire_load($rc)
	{
		Event::fire('nodes_load', array('nodes' => is_array($rc) ? $rc : array($rc)), $this); // FIXME: $patron was used instead of $this
	}
}