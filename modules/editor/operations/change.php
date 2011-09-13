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
		$params = $this->params;

		if (empty($this->key))
		{
			throw new Exception('Empty operation key (editor id)');
		}

		if (empty($params['selector_name']))
		{
			throw new Exception('Empty selector name');
		}

		if (empty($params['contents_name']))
		{
			throw new Exception('Empty contents_name');
		}

		if (!isset($params['contents']))
		{
			throw new Exception('Missing conents');
		}

		return true;
	}

	protected function process()
	{
		global $core;

		$params = $this->params;

		$editor = (string) new \WdMultiEditorElement
		(
			$this->key, array
			(
				\WdMultiEditorElement::T_SELECTOR_NAME => $params['selector_name'],

				'name' => $params['contents_name'],
				'value' => $params['contents']
			)
		);

		$this->response->assets = $core->document->get_assets();
		$this->terminus = true;

		return $editor;
	}
}