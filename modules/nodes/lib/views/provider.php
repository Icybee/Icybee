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

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\HTTP\HTTPError;
use ICanBoogie\HTTP\NotFound;

class ViewProvider extends \Icybee\Modules\Views\ActiveRecordProvider
{
	/**
	 * @throws a HTTPException with code 404 if no record matching the conditions could be found
	 * and the view is of type "view".
	 *
	 * @throws a HTTPException with code 401 if the record is offline and user don't have access
	 * permission and the view is of type "view".
	 */
	public function __invoke()
	{
		global $core;

		$rc = parent::__invoke();

		if ($rc instanceof Node)
		{
			if (!$rc)
			{
				throw new NotFound('The requested record was not found.');
			}

			if (!$rc->is_online)
			{
				if (!$core->user->has_permission(\ICanBoogie\Module::PERMISSION_ACCESS, $rc->constructor))
				{
					throw new HTTPError('The requested record requires authentication.', 401);
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
		}

		return $rc;
	}

	/**
	 * Returns the conditions unaltered.
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
	 */
	protected function alter_query(Query $query, array $conditions)
	{
		$query->own->similar_site->similar_language;

		if (isset($conditions['nid']))
		{
			$query->filter_by_nid($conditions['nid']);
		}
		else if (isset($conditions['slug']))
		{
			$query->filter_by_slug($conditions['slug']);
		}

		if ($this->returns == self::RETURNS_MANY)
		{
			$query->filter_by_is_online(true);
		}

		return parent::alter_query($query, $conditions)->order('created DESC');
	}

	/**
	 * Returns the rendering context unaltered.
	 */
	protected function alter_context(\BlueTihi\Context $context, Query $query, array $conditions)
	{
		return $context;
	}
}