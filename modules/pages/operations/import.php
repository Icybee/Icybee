<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Pages;

use ICanBoogie\Operation;

class Import extends \ICanBoogie\Operation\Nodes\Import
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
				wd_log("page $nid has no content");
			}
			else
			{
				$contents = array();
				$data_contents = $obj->contents;

				foreach ($data_contents as $contentid => $content)
				{
					$c = $content->content;

					if (($c{0} == '{' || $c{0} == '[') && $c{1} == '"')
					{
						$c = json_decode($c, true);
					}

					if ($content->editor == 'widgets')
					{
						$c = array_combine($c, array_fill(0, count($c), 'on'));
					}

					$contents[$contentid] = array
					(
						'editor' => $content->editor,
						'contents' => $c
					);
				}

				$obj->contents = $contents;
			}
		}

		return parent::parse_data($data);
	}

	protected function import(array $data, Operation\Nodes\Save $save)
	{
		global $core;

		parent::import($data, $save);

		//var_dump($this->keys_translations, $this->locationid, $data);

		$update = $core->db->prepare('UPDATE {prefix}site_pages SET parentid = ?, locationid = ? WHERE nid = ?');

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