<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Editor;

use ICanBoogie\Errors;
use ICanBoogie\Exception;
use ICanBoogie\I18n\FormattedString;
use ICanBoogie\Operation;

/**
 * Changes multieditor editor.
 */
class ChangeOperation extends Operation
{
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_AUTHENTICATION => true
		)

		+ parent::get_controls();
	}

	protected function validate(Errors $errors)
	{
		$request = $this->request;

		if (!$this->key)
		{
			$errors['editor_id'] = new FormattedString('The %property is required.', array('property' => 'editor_id'));
		}

		if (empty($request['selector_name']))
		{
			$errors['selector_name'] = new FormattedString('The %property is required.', array('property' => 'selector_name'));
		}

		if (empty($request['contents_name']))
		{
			$errors['contents_name'] = new FormattedString('The %property is required.', array('property' => 'contents_name'));
		}

		return !$errors->count();
	}

	protected function process()
	{
		global $core;

		$request = $this->request;

		$editor = (string) new MultiEditorElement
		(
			$this->key, array
			(
				MultiEditorElement::SELECTOR_NAME => $request['selector_name'],

				'name' => $request['contents_name'],
				'value' => $request['contents']
			)
		);

		$this->response['assets'] = $core->document->assets;

		return $editor;
	}
}