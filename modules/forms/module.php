<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module;

use BrickRouge\Element;
use BrickRouge\Form;

use Icybee\Manager;

class Forms extends Nodes
{
	const OPERATION_POST = 'post';
	const OPERATION_POST_ID = '#post-id';
	const OPERATION_DEFAULTS = 'defaults';

	protected function block_manage()
	{
		return new Manager\Forms
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array('title', 'modelid', 'uid', 'is_online', 'modified')
			)
		);
	}

	protected function block_edit(array $properties, $permission)
	{
		global $core, $document;

		$document->css->add('public/edit.css');
		$document->js->add('public/edit.js');

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

		return wd_array_merge_recursive
		(
			parent::block_edit($properties, $permission), array
			(
				Element::T_GROUPS => array
				(
					'messages' => array
					(
						'title' => '.messages',
						'class' => 'form-section flat'
					),

					'notify' => array
					(
						'title' => '.notify',
						'class' => 'form-section flat'
					),

					'operation' => array
					(
						'title' => '.operation'
					)
				),

				Element::T_CHILDREN => array
				(
					'modelid' => new Element
					(
						'select', array
						(
							Form::T_LABEL => '.modelid',
							Element::T_REQUIRED => true,
							Element::T_OPTIONS => array(null => '') + $models_options,
							Element::T_LABEL_POSITION => 'before'
						)
					),

					'pageid' => new \WdPageSelectorElement
					(
						'select', array
						(
							Form::T_LABEL => '.pageid',
							Element::T_LABEL_POSITION => 'before'
						)
					),

					'before' => new \moo_WdEditorElement
					(
						array
						(
							Form::T_LABEL => '.before',
							Element::T_GROUP => 'messages',

							'rows' => 5
						)
					),

					'after' => new \moo_WdEditorElement
					(
						array
						(
							Form::T_LABEL => '.after',
							Element::T_GROUP => 'messages',

							'rows' => 5
						)
					),

					'complete' => new \moo_WdEditorElement
					(
						array
						(
							Form::T_LABEL => '.complete',
							Element::T_GROUP => 'messages',
							Element::T_REQUIRED => true,
							Element::T_DESCRIPTION => '.complete',
							Element::T_DEFAULT => '<p>' . t('default.complete') . '</p>',

							'rows' => 5
						)
					),

					#
					# notify
					#

					'notify' => new Element\Templated
					(
						'div', array
						(
							Element::T_GROUP => 'notify',
							Element::T_CHILDREN => array
							(
								'is_notify' => new Element
								(
									Element::E_CHECKBOX, array
									(
										Element::T_LABEL => '.is_notify',
										Element::T_GROUP => 'notify',
										Element::T_DESCRIPTION => '.is_notify'
									)
								),

								'notify_destination' => new Element
								(
									Element::E_TEXT, array
									(
										Form::T_LABEL => '.notify_destination',
										Element::T_GROUP => 'notify',
										Element::T_DEFAULT => $core->user->email
									)
								),

								'notify_from' => new Element
								(
									Element::E_TEXT, array
									(
										Form::T_LABEL => '.notify_from',
										Element::T_GROUP => 'notify'
									)
								),

								'notify_bcc' => new Element
								(
									Element::E_TEXT, array
									(
										Form::T_LABEL => '.notify_bcc',
										Element::T_GROUP => 'notify'
									)
								),

								'notify_subject' => new Element
								(
									Element::E_TEXT, array
									(
										Form::T_LABEL => '.notify_subject',
										Element::T_GROUP => 'notify'
									)
								),

								'notify_template' => new Element
								(
									'textarea', array
									(
										Form::T_LABEL => '.notify_template',
										Element::T_GROUP => 'notify'
									)
								)
							)
						),

						<<<EOT
<div class="panel">
<div class="form-element is_notify">{\$is_notify}</div>
<table>
<tr><td class="label">{\$notify_from.label:}</td><td>{\$notify_from}</td><td colspan="2">&nbsp;</td></tr>
<tr><td class="label">{\$notify_destination.label:}</td><td>{\$notify_destination}</td>
<td class="label">{\$notify_bcc.label:}</td><td>{\$notify_bcc}</td></tr>
<tr><td class="label">{\$notify_subject.label:}</td><td colspan="3">{\$notify_subject}</td></tr>
<tr><td colspan="4">{\$notify_template}<button class="reset small warn" type="button" value="/api/forms/%modelid/defaults">$label_default_values</button>

<div class="element-description">$description_notify</div>
</td></tr>
</table>
</div>
EOT
					)
				)
			)
		);
	}
}