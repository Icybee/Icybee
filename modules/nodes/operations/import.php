<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Nodes;

use ICanBoogie\Module;
use ICanBoogie\Operation;

class Import extends Operation
{
	protected $keys_translations = array();

	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::__get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		return true;
	}

	protected function process()
	{
		global $core;

		$data = $this->preparse_data();
		$data = $this->parse_data($data);

		$save = Operation::decode("/api/{$this->module}/save");

		#
		# disable form control.
		#

		$save->controls;
		$save->controls[self::CONTROL_FORM] = false;

		$siteid = $core->site_id;

		$keys = $core->models['nodes']->select('nid')->find_by_siteid($siteid)->all(PDO::FETCH_COLUMN);

		if ($keys)
		{
			$core->models['nodes']->where(array('nid' => $keys))->delete();
			$core->models['pages']->where(array('nid' => $keys))->delete();
			$core->models['pages/contents']->where(array('pageid' => $keys))->delete();
		}

		$this->import($data, $save);
	}

	protected function preparse_data()
	{
		$data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/export.json');
		$data = json_decode($data);

		return (array) $data->rc;
	}

	protected function parse_data(array $data)
	{
		global $core;

		$site = $core->site;
		$siteid = $site->siteid;
		$language = $site->language;

		foreach ($data as $node)
		{
			$node->siteid = $siteid;
			$node->nativeid = 0;
			$node->language = $language;
		}

		return $data;
	}

	protected function import(array $data, Operation\ActiveRecord\Save $save)
	{
		foreach ($data as $nid => $node)
		{
			$save->params = (array) $node;

			$rc = $save();

			$this->keys_translations[$nid] = $rc['key'];
		}
	}
}