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

use ICanBoogie\Events;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation;

use Brickrouge\Form;

class ImportOperation extends Operation
{
	protected $keys_translations = array();

	protected function get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_ADMINISTER
		)

		+ parent::get_controls();
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

		$save = Request::from
		(
			array
			(
				'path' => "/api/{$this->module}/save"
			)
		);

		$save_hash = spl_object_hash($save);

		#
		# override form
		#

		Events::attach('ICanBoogie\Operation::get_form', function(Operation\GetFormEvent $event, SaveOperation $operation) use($save_hash) {

			if (spl_object_hash($event->request) != $save_hash)
			{
				return;
			} 

			$event->form = new Form();

		});

		$siteid = $core->site_id;
		$keys = $core->models['nodes']->select('nid')->find_by_siteid($siteid)->all(\PDO::FETCH_COLUMN);

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
		$data = file_get_contents(\ICanBoogie\DOCUMENT_ROOT . 'export.json');
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

	protected function import(array $data, Request $request)
	{
		foreach ($data as $nid => $node)
		{
			$request->params = (array) $node;

			$response = $request->post();

			$this->keys_translations[$nid] = $response->rc['key'];
		}
	}
}