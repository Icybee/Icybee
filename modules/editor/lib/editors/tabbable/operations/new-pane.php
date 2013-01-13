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

use ICanBoogie\I18n;

/**
 * Returns a new pane for the {@link TabbableEditor}.
 */
class TabbableNewPaneOperation extends \ICanBoogie\Operation
{
	/**
	 * The `control_name` parameter is request.
	 *
	 * @see ICanBoogie.Operation::validate()
	 */
	protected function validate(\ICanBoogie\Errors $errors)
	{
		if (!$this->request['control_name'])
		{
			$errors['control_name'] = I18n\t('The %identifier is required.', array('identifier' => 'control_name'));
		}

		return true;
	}

	/**
	 * Returns a pane HTML string.
	 *
	 * Adds the following response properties:
	 *
	 * - (string) tab: The tab element associated with the pane.
	 * - (array) assets: The assets required by the elements.
	 *
	 * @see ICanBoogie.Operation::process()
	 */
	protected function process()
	{
		global $core;

		$request = $this->request;
		$properties = array
		(
			'name' => $request['control_name'] . '[' . uniqid() . ']',
			'title' => 'New tab',
			'editor_id' => 'rte',
			'serialized_content' => null
		);

		$tab = TabbableEditorElement::create_tab($properties);
		$pane = TabbableEditorElement::create_pane($properties);

		$tab->add_class('active');
		$pane->add_class('active');

		$tab = (string) $tab;
		$pane = (string) $pane;

		$this->response['tab'] = $tab;
		$this->response['assets'] = $core->document->assets;

		return $pane;
	}
}