<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Pages;

use ICanBoogie\Operation;

class TemplateEditorsOperation extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_CREATE
		)

		+ parent::__get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		return true;
	}

	/**
	 * Returns a sectionned form with the editors to use to edit the contents of a template.
	 *
	 * The function alters the operation object by adding the `template` property, which holds an
	 * array with the following keys:
	 *
	 * - `name`: The name of the template.
	 * - `description`: The description for the template.
	 * - `inherited`: Whether or not the template is inherited.
	 *
	 * The function also alters the operation object by adding the `assets` property, which holds
	 * an array with the following keys:
	 *
	 * - `css`: An array of CSS files URL.
	 * - `js`: An array of Javascript files URL.
	 *
	 * @return string The HTML code for the form.
	 */
	protected function process()
	{
		global $core;

		$request = $this->request;
		$template = $request['template'];
		$pageid = $request['pageid'];

		list($contents_tags, $template_info) = $this->module->get_contents_section($pageid, $template);

		$this->response['template'] = $template_info;

		$form = (string) new \Brickrouge\Form(array(\Brickrouge\Form::RENDERER => 'Simple') + $contents_tags);

		$this->response['assets'] = $core->document->assets;

		return $form;
	}
}