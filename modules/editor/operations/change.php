<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Editor;

use ICanBoogie\Exception;
use ICanBoogie\Operation;

class Change extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_AUTHENTICATION => true
		)

		+ parent::__get_controls();
	}

	protected function validate()
	{
		$request = $this->request;

		if (!$this->key)
		{
			throw new Exception('Empty operation key (editor id)');
		}

		if (empty($request['selector_name']))
		{
			throw new Exception('Empty selector name');
		}

		if (empty($request['contents_name']))
		{
			throw new Exception('Empty contents_name');
		}

		if (!isset($request['contents']))
		{
			throw new Exception('Missing conents');
		}

		return true;
	}

	protected function process()
	{
		global $core;

		$request = $this->request;

		$editor = (string) new \WdMultiEditorElement
		(
			$this->key, array
			(
				\WdMultiEditorElement::T_SELECTOR_NAME => $request['selector_name'],

				'name' => $request['contents_name'],
				'value' => $request['contents']
			)
		);

		$this->response->assets = $core->document->get_assets();
		$this->terminus = true;

		return $editor;
	}
}