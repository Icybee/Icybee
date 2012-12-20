<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Operation\Module;

use ICanBoogie\Operation;

class Blocks extends Operation
{
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_AUTHENTICATION => true
		)

		+ parent::get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		if (!$this->request['name'])
		{
			$errors['name'] = t('Missing block name');

			return false;
		}

		return true;
	}

	protected function process()
	{
		global $core, $document;

		// TODO: add block access restriction

		$document = $core->document;

		if ($core->user_id && $core->user->language)
		{
			$core->locale = $core->user->language;
		}

		$request = $this->request;
		$name = $request['name'];
		$module = $core->modules[$request['module']];

		$block = $module->getBlock($name, $request->params);

		$this->response['assets'] = $document->assets;

		return (string) $block;
	}
}