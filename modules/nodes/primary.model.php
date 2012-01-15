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
use ICanBoogie\Exception;

class Model extends \Icybee\ActiveRecord\Model\Constructor
{
	public function save(array $properties, $key=null, array $options=array())
	{
		global $core;

		if (!$key && !array_key_exists(Node::UID, $properties))
		{
			$properties[Node::UID] = $core->user_id;
		}

		$properties += array
		(
			Node::MODIFIED => date('Y-m-d H:i:s')
		);

		if (empty($properties[Node::SLUG]) && isset($properties[Node::TITLE]))
		{
			$properties[Node::SLUG] = $properties[Node::TITLE];
		}

		if (isset($properties[Node::SLUG]))
		{
			$properties[Node::SLUG] = wd_slugize($properties[Node::SLUG]);
		}

		return parent::save($properties, $key, $options);
	}

	/**
	 * Makes sure the node to delete is not used as a native target by other nodes.
	 *
	 * @see WdDatabaseTable::delete()
	 * @throws Exception if the node to delete is the native target of another node.
	 */
	public function delete($key)
	{
		$native_refs = $this->select('nid')->find_by_nativeid($key)->all(\PDO::FETCH_COLUMN);

		if ($native_refs)
		{
			throw new Exception('Node record cannot be deleted because it is used as native source by the following records: \1', array(implode(', ', $native_refs)));
		}

		return parent::delete($key);
	}

	protected function scope_online(Query $query)
	{
		return $query->where('is_online = 1');
	}

	protected function scope_offline(Query $query)
	{
		return $query->where('is_online = 0');
	}

	protected function scope_visible(Query $query)
	{
		global $core;

		return $query->where('is_online = 1 AND (siteid = 0 OR siteid = ?) AND (language = "" OR language = ?)', $core->site->siteid, $core->site->language);
	}

	protected function scope_similar_site(Query $query, $siteid=null)
	{
		global $core;

		return $query->where('siteid = 0 OR siteid = ?', $siteid !== null ? $siteid : $core->site->siteid);
	}

	protected function scope_similar_language(Query $query, $language=null)
	{
		global $core;

		return $query->where('language = 0 OR language = ?', $language !== null ? $language : $core->site->language);
	}

	public function parseConditions(array $conditions)
	{
		$where = array();
		$args = array();

		foreach ($conditions as $identifier => $value)
		{
			switch ($identifier)
			{
				case 'nid':
				{
					$where[] = '`nid` = ?';
					$args[] = $value;
				}
				break;

				case 'constructor':
				{
					$where[] = '`constructor` = ?';
					$args[] = $value;
				}
				break;

				case 'slug':
				case 'title':
				{
					$where[] = '(slug = ? OR title = ?)';
					$args[] = $value;
					$args[] = $value;
				}
				break;

				case 'language':
				{
					$where[] = '(language = "" OR language = ?)';
					$args[] = $value;
				}
				break;

				case 'is_online':
				{
					$where[] = 'is_online = ?';
					$args[] = $value;
				}
				break;
			}
		}

		return array($where, $args);
	}
}