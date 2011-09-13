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

use ICanBoogie\Module;
use ICanBoogie\Operation;

class TemplateEditors extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_PERMISSION => Module::PERMISSION_CREATE
		)

		+ parent::__get_controls();
	}

	protected function validate()
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

		$params = $this->params;

		$template = isset($params['template']) ? $params['template'] : null;
		$pageid = isset($params['pageid']) ? $params['pageid'] : null;

		list($contents_tags, $template_info) = $this->module->get_contents_section($pageid, $template);

		$this->response->template = $template_info;

		$form = (string) new \WdSectionedForm($contents_tags);

		$this->response->assets = $core->document->get_assets();

		return $form;
	}
}