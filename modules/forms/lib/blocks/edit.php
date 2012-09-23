<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Forms;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

/**
 * A block to edit forms.
 */
class EditBlock extends \Icybee\Modules\Nodes\EditBlock
{
	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('../../public/admin.css');
		$document->js->add('../../public/admin.js');
	}

	protected function get_attributes()
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::get_attributes(), array
			(
				Element::GROUPS => array
				(
					'messages' => array
					(
						'title' => 'messages'
					),

					'options' => array
					(
						'title' => 'options'
					),

					'operation' => array
					(
						'title' => 'operation'
					)
				)
			)
		);
	}

	protected function get_children()
	{
		global $core;

		$models = $core->configs->synthesize('formmodels', 'merge');
		$models_options = array();

		if ($models)
		{
			foreach ($models as $modelid => $model)
			{
				$models_options[$modelid] = $model['title'];
			}

			asort($models_options);
		}

		$label_default_values = t('Default values');
		$description_notify = t('description_notify', array(':link' => '<a href="http://github.com/Weirdog/WdPatron" target="_blank">WdPatron</a>'));

		return array_merge
		(
			parent::get_children(), array
			(
				'modelid' => new Element
				(
					'select', array
					(
						Form::LABEL => 'modelid',
						Element::REQUIRED => true,
						Element::OPTIONS => array(null => '') + $models_options,
						Element::LABEL_POSITION => 'before'
					)
				),

				'before' => $core->editors['rte']->from
				(
					array
					(
						Form::LABEL => 'before',
						Element::GROUP => 'messages',

						'rows' => 5
					)
				),

				'after' => $core->editors['rte']->from
				(
					array
					(
						Form::LABEL => 'after',
						Element::GROUP => 'messages',

						'rows' => 5
					)
				),

				'complete' => $core->editors['rte']->from
				(
					array
					(
						Form::LABEL => 'complete',
						Element::GROUP => 'messages',
						Element::REQUIRED => true,
						Element::DESCRIPTION => 'complete',
						Element::DEFAULT_VALUE => '<p>' . t('default.complete') . '</p>',

						'rows' => 5
					)
				),

				'is_notify' => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => 'is_notify',
						Element::GROUP => 'options',
						Element::DESCRIPTION => 'is_notify'
					)
				),

				'notify_' => new EmailComposer
				(
					array
					(
						Element::GROUP => 'options',
						Element::DEFAULT_VALUE => array
						(
							'from' => $core->site->email,
							'destination' => $core->site->email
						),

						'class' => 'form-horizontal'
					)
				)
			)
		);
	}
}