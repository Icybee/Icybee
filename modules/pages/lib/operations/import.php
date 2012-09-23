<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Pages;

use ICanBoogie\HTTP\Request;

class ImportOperation extends \Icybee\Modules\Nodes\ImportOperation
{
	private $parentid = array();
	private $locationid = array();

	protected function parse_data(array $data)
	{
		foreach ($data as $nid => $obj)
		{
			if ($obj->parentid)
			{
				$this->parentid[$nid] = $obj->parentid;
			}

			unset($obj->parentid);

			if ($obj->locationid)
			{
				$this->locationid[$nid] = $obj->locationid;
			}

			unset($obj->locationid);

			if (empty($obj->contents))
			{
				\ICanBoogie\log("page $nid has no content");
			}
			else
			{
				$contents = (array) $obj->contents;
				$editors = (array) $obj->editors;

				foreach ($contents as $contentid => &$content)
				{
					if (($content{0} == '{' || $content{0} == '[') && $content{1} == '"')
					{
						$content = json_decode($content, true);
					}
				}

				foreach ($editors as $contentid => $editor_name)
				{
					if ($editor_name != 'widgets' || empty($contents[$contentid]))
					{
						continue;
					}

					$content = &$contents[$contentid];
					$content = array_combine($content, array_fill(0, count($content), 'on'));
				}

				$obj->contents = $contents;
				$obj->editors = $editors;
			}
		}

		return parent::parse_data($data);
	}

	protected function import(array $data, Request $save)
	{
		global $core;

		parent::import($data, $save);

		//var_dump($this->keys_translations, $this->locationid, $data);

		$update = $core->db->prepare('UPDATE {prefix}pages SET parentid = ?, locationid = ? WHERE nid = ?');

		$original_nodes_with_parentid = $this->parentid;
		$original_nodes_with_locationid = $this->locationid;

		foreach ($data as $nid => $node)
		{
			$parentid = 0;

			if (isset($original_nodes_with_parentid[$nid]))
			{
				$parentid = $this->keys_translations[$original_nodes_with_parentid[$nid]];
			}

			$locationid = 0;

			if (isset($original_nodes_with_locationid[$nid]))
			{
				$locationid = $this->keys_translations[$original_nodes_with_locationid[$nid]];
			}

			if ($parentid || $locationid)
			{
				$update->execute(array($parentid, $locationid, $this->keys_translations[$nid]));
			}
		}
	}
}