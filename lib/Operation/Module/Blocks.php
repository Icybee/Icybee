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

use ICanboogie\ErrorCollection;
use ICanBoogie\I18n;
use ICanBoogie\Operation;

/**
 * @property-read \ICanBoogie\Application $app
 */
class Blocks extends Operation
{
	protected function get_controls()
	{
		return [

		self::CONTROL_AUTHENTICATION => true

		] + parent::get_controls();
	}

	/**
	 * @inheritdoc
	 */
	protected function validate(ErrorCollection $errors)
	{
		if (!$this->request['name'])
		{
			$errors->add('name', "Missing block name");
		}

		return $errors;
	}

	protected function process()
	{
		$app = $this->app;

		// TODO: add block access restriction

		$document = $app->document;

		if ($app->user_id && $app->user->language)
		{
			$app->locale = $app->user->language;
		}

		$request = $this->request;
		$name = $request['name'];
		$module = $app->modules[$request['module']];

		$block = $module->getBlock($name, $request->params);

		$this->response['assets'] = $document->assets;

		return (string) $block;
	}
}
