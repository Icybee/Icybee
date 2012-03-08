<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Modules;

use Brickrouge\Button;

use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\Element;
use Brickrouge\Form;

use Icybee;

class Module extends \Icybee\Module
{
	const MANAGE_MODE = '#manage-mode';
	const MANAGE_MODE_INSTALLER = 'installer';

	const OPERATION_ACTIVATE = 'activate';
	const OPERATION_DEACTIVATE = 'deactivate';

	protected function block_install($module_id)
	{
		global $core;

		if (!$core->user->has_permission(self::PERMISSION_ADMINISTER, $this))
		{
			return '<div class="alert alert-error">' . t('You don\'t have enought privileges to install packages.') . '</div>';
		}

		if (empty($core->modules[$module_id]))
		{
			return '<div class="alert alert-error">' . t('The module %module_id does not exists.', array('%module_id' => $module_id)) . '</div>';
		}

		$errors = new \ICanBoogie\Errors;
		$module = $core->modules[$module_id];

		$is_installed = $module->is_installed($errors);

		if ($is_installed && !count($errors))
		{
			return '<div class="alert alert-error">' . t('The module %module is already installed', array('%module' => $module_id)) . '</div>';
		}

		$errors->clear();
		$is_installed = $module->install($errors);

		if (!$is_installed || count($errors))
		{
			return '<div class="alert alert-error">' . t('Unable to install the module %module', array('%module' => $module_id)) . '</div>';
		}

		return '<div class="alert alert-success">' . t('The module %module has been installed. <a href="' . $core->site->path . '/admin/' . $this . '">Retourner à la liste.</a>', array('%module' => $module_id)) . '</div>';
	}

	protected function block_inactives()
	{
		global $core, $document;

		$document->css->add(Icybee\ASSETS . 'css/manage.css');
		$document->css->add('lib/blocks/manage.css');

		#
		# read and sort packages and modules
		#

		$categories = array();
		$modules = array();

		$descriptors = $core->modules->disabled_modules_descriptors;

		self::sort_descriptors($descriptors);

		foreach ($descriptors as $id => $descriptor)
		{
			if ($descriptor[Module::T_REQUIRED])
			{
				unset($descriptors[$id]);

				continue;
			}

			$name = $descriptor[Module::T_TITLE];

			if (isset($descriptor[Module::T_CATEGORY]))
			{
				$category = $descriptor[Module::T_CATEGORY];
			}
			else
			{
				list($category) = explode('.', $id);
			}

			$category = t($category, array(), array('scope' => 'module_category', 'default' => ucfirst($category)));
			$title = $descriptor['_locale_title'];

			$categories[$category][$title] = array_merge
			(
				$descriptor, array
				(
					self::T_ID => $id
				)
			);
		}

		uksort($categories, 'wd_unaccent_compare_ci');

		#
		# disabled modules
		#

		$rows = '';

		foreach ($categories as $category => $descriptors)
		{
			$category_rows = null;

			foreach ($descriptors as $title => $descriptor)
			{
				$moduleid = $descriptor[Module::T_ID];

				$checkbox = new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						'name' => Operation::KEY . '[' . $moduleid . ']'
					)
				);

				$author = 'Olivier Laviale';
				$description = $this->render_cell_description($descriptor, $moduleid);

				if (!$description)
				{
					$description = '&nbsp;';
				}

				$category_rows .= <<<EOT
<tr>
	<td class="count">$checkbox</td>
	<td class="name">$title</td>
	<td>$author</td>
	<td>$description</td>
</tr>
EOT;
			}

			if ($category_rows)
			{
				$rows .= <<<EOT
<tr class="row--category">
	<td colspan="5">$category</td>
</tr>

$category_rows
EOT;
			}
		}

		$disabled_table = null;

		$rc = '';

		if ($rows)
		{
			$label_author = t('Author');
			$label_description = t('Description');

			$rc = <<<EOT
<table class="manage resume" cellpadding="4" cellspacing="0">
	<thead>
		<tr>
		<th colspan="2"><div>&nbsp;</div></th>
		<th><div>$label_author</div></th>
		<th><div>$label_description</div></th>
		</tr>
	</thead>

	<tbody>$rows</tbody>
</table>
EOT;
		}

		return new Form
		(
			array
			(
				Form::ACTIONS => new Button
				(
					'Activate the selected modules', array
					(
						'class' => 'btn-primary btn-danger',
						'type' => 'submit'
					)
				),

				Form::HIDDENS => array
				(
					Operation::NAME => self::OPERATION_ACTIVATE,
					Operation::DESTINATION => $this
				),

				Element::INNER_HTML => $rc,

				'class' => 'form-primary'
			)
		);
	}

	protected function render_cell_description(array $descriptor, $moduleid)
	{
		global $core;

		$rc = '';

		$description = $core->locale->translator[strtr($moduleid, '.', '_') . '.description'];

		if (!$description && isset($descriptor[Module::T_DESCRIPTION]))
		{
			$description = $descriptor[Module::T_DESCRIPTION];
		}

		if ($description)
		{
			$rc .= '<div class="description">' . $description . '</div>';
		}

		$more = '';

		if (isset($descriptor[Module::T_EXTENDS]))
		{
			$extends = $descriptor[Module::T_EXTENDS];

			$more .= '<div class="extends">Étends le module <q>' . $extends . '</q></div>';
		}

		if ($more)
		{
			$rc .= '<div class="more small">' . $more . '</div>';
		}

		return $rc;
	}

	static private function sort_descriptors(array &$descriptors)
	{
		\ICanBoogie\stable_sort
		(
			$descriptors, function(&$descriptor)
			{
				$id = $descriptor[Module::T_ID];
				$title = t
				(
					strtr($id, '.', '_'), array(), array
					(
						'scope' => 'module_title',
						'default' => isset($descriptor[Module::T_TITLE]) ? $descriptor[Module::T_TITLE] : $id
					)
				);

				$descriptor['_locale_title'] = $title;

				return wd_remove_accents($title);
			}
		);
	}
}