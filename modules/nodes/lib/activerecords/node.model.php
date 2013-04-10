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

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Exception;

/**
 * Nodes model.
 */
class Model extends \Icybee\ActiveRecord\Model\Constructor
{
	/**
	 * If the {@link Node::$modified} property is not defined it is set to the current datetime.
	 *
	 * If the {@link Node::$slug} property is empty but the {@link Node::$title} property is
	 * defined its value is used.
	 *
	 * The {@link Node:$slug} property is always slugized.
	 */
	public function save(array $properties, $key=null, array $options=array())
	{
		global $core;

		if (!$key && !array_key_exists(Node::UID, $properties)) // TODO-20121004: move this to operation
		{
			$properties[Node::UID] = $core->user_id;
		}

		$properties += array
		(
			Node::MODIFIED => gmdate('Y-m-d H:i:s')
		);

		if (empty($properties[Node::SLUG]) && isset($properties[Node::TITLE]))
		{
			$properties[Node::SLUG] = $properties[Node::TITLE];
		}

		if (isset($properties[Node::SLUG]))
		{
			$properties[Node::SLUG] = \Icybee\slugize($properties[Node::SLUG]);
		}

		return parent::save($properties, $key, $options);
	}

	/**
	 * Makes sure the node to delete is not used as a native target by other nodes.
	 *
	 * @throws Exception if the node to delete is the native target of another node.
	 */
	public function delete($key)
	{
		$native_refs = $this->select('nid')->filter_by_nativeid($key)->all(\PDO::FETCH_COLUMN);

		if ($native_refs)
		{
			throw new Exception('Node record cannot be deleted because it is used as native source by the following records: \1', array(implode(', ', $native_refs)));
		}

		return parent::delete($key);
	}

	/**
	 * Alerts the query to match online records.
	 *
	 * @param Query $query
	 *
	 * @return Query
	 */
	protected function scope_online(Query $query)
	{
		return $query->filter_by_is_online(true);
	}

	/**
	 * Alerts the query to match offline records.
	 *
	 * @param Query $query
	 *
	 * @return Query
	 */
	protected function scope_offline(Query $query)
	{
		return $query->filter_by_is_online(false);
	}

	/**
	 * Alerts the query to match records visible on the current website.
	 *
	 * @param Query $query
	 *
	 * @return Query
	 */
	protected function scope_visible(Query $query)
	{
		global $core;

		return $query->online->similar_site->similar_language;
	}

	/**
	 * Alerts the query to match records of a similar site.
	 *
	 * A record is considered of a similar website when it doesn't belong to a website
	 * (`siteid = 0') or it matches the specified website.
	 *
	 * @param Query $query
	 * @param int $siteid The identifier of the website to match. If the identifier is `null` the
	 * current website identifier is used instead.
	 *
	 * @return Query
	 */
	protected function scope_similar_site(Query $query, $siteid=null)
	{
		global $core;

		return $query->where('siteid = 0 OR siteid = ?', $siteid !== null ? $siteid : $core->site->siteid);
	}

	/**
	 * Alerts the query to match recors of a similar language.
	 *
	 * A record is considered of a similar language when it doesn't have a language defined
	 * (`language` = "") or it matches the specified language.
	 *
	 * @param Query $query
	 * @param string $language The language to match. If the language is `null` the current
	 * language is used instead.
	 *
	 * @return Query
	 */
	protected function scope_similar_language(Query $query, $language=null)
	{
		global $core;

		return $query->where('language = "" OR language = ?', $language !== null ? $language : $core->site->language);
	}

	/**
	 * Finds the users the records belong to.
	 *
	 * The `user` property of the records is set to the user they belong to.
	 *
	 * @param array $records
	 *
	 * @return array
	 */
	public function including_user(array $records)
	{
		$keys = array();

		foreach ($records as $record)
		{
			$keys[$record->uid] = $record;
		}

		$users = ActiveRecord\get_model('users')->find_using_constructor(array_keys($keys));

		/* @var $user \Icybee\Modules\Users\User */

		foreach ($users as $key => $user)
		{
			$keys[$key]->user = $user;
		}

		return $records;
	}
}