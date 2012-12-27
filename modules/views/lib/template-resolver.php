<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Views;

/**
 * @property-read array $templates Possible templates.
 */
class TemplateResolver extends \ICanBoogie\Object
{
	/**
	 * View identifier.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Views type, one of "home", "list", "view"...
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Identifier of the module for which a view is created.
	 *
	 * @var string
	 */
	protected $module_id;

	public function __construct($id, $type, $module_id)
	{
		$this->id = $id;
		$this->type = $type;
		$this->module_id = $module_id;
	}

	protected function get_templates()
	{
		global $core;

		$id = $this->id;
		$type = $this->type;
		$templates = array();

		$templates_base = array();

		$parts = explode('/', $id);
		$module_id = array_shift($parts);
		$type = array_pop($parts);

		while (count($parts))
		{
			$templates_base[] = implode('--', $parts) . '--' . $type;

			array_pop($parts);
		}

		$templates_base[] = $type;

		$templates_base = array_unique($templates_base);

		$descriptors = $core->modules->descriptors;
		$descriptor = $descriptors[$this->module_id];

		while ($descriptor)
		{
			foreach ($templates_base as $template)
			{
				$pathname = \ICanBoogie\DOCUMENT_ROOT . 'protected/all/templates/views/' . \ICanBoogie\normalize($descriptor[Module::T_ID]) . '--' . $template;
				$templates[] = $pathname;

				$pathname = $descriptor[Module::T_PATH] . 'views/' . $template;
				$templates[] = $pathname;
			}

			$descriptor = $descriptor[Module::T_EXTENDS] ? $descriptors[$descriptor[Module::T_EXTENDS]] : null;
		}

		foreach ($templates_base as $template)
		{
			$pathname = \ICanBoogie\DOCUMENT_ROOT . 'protected/all/templates/views/' . $template;
			$templates[] = $pathname;
		}

		return $templates;
	}

	public function __invoke()
	{
		$templates = $this->templates;

		$handled = array('php', 'html');

		foreach ($templates as $template)
		{
			foreach ($handled as $extension)
			{
				$pathname = $template . '.' . $extension;

// 				\ICanBoogie\log("tryed: $pathname");

				if (file_exists($pathname))
				{
					return $pathname;
				}
			}
		}
	}
}