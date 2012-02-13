<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Forms;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Module extends \ICanBoogie\Modules\Nodes\Module
{
	const OPERATION_POST = 'post';
	const OPERATION_POST_ID = '#post-id';
	const OPERATION_DEFAULTS = 'defaults';

	protected function block_manage()
	{
		return new Manager
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
				Element::GROUPS => array
				(
					'messages' => array
					(
						'title' => 'messages'
					),

					'notify' => array
					(
						'title' => 'notify'
					),

					'operation' => array
					(
						'title' => 'operation'
					)
				),

				Element::CHILDREN => array
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

					/*
					'pageid' => new \WdPageSelectorElement
					(
						'select', array
						(
							Form::LABEL => 'pageid',
							Element::LABEL_POSITION => 'before'
						)
					),
					*/

					'before' => new \moo_WdEditorElement
					(
						array
						(
							Form::LABEL => 'before',
							Element::GROUP => 'messages',

							'rows' => 5
						)
					),

					'after' => new \moo_WdEditorElement
					(
						array
						(
							Form::LABEL => 'after',
							Element::GROUP => 'messages',

							'rows' => 5
						)
					),

					'complete' => new \moo_WdEditorElement
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

					#
					# notify
					#

					'notify' => new Element\Templated
					(
						'div', array
						(
							Element::GROUP => 'notify',
							Element::CHILDREN => array
							(
								'is_notify' => new Element
								(
									Element::TYPE_CHECKBOX, array
									(
										Element::LABEL => 'is_notify',
										Element::GROUP => 'notify',
										Element::DESCRIPTION => 'is_notify'
									)
								),

								'notify_destination' => new Text
								(
									array
									(
										Form::LABEL => 'notify_destination',
										Element::GROUP => 'notify',
										Element::DEFAULT_VALUE => $core->user->email
									)
								),

								'notify_from' => new Text
								(
									array
									(
										Form::LABEL => 'notify_from',
										Element::GROUP => 'notify',
										Element::DEFAULT_VALUE => $core->site->email
									)
								),

								'notify_bcc' => new Text
								(
									array
									(
										Form::LABEL => 'notify_bcc',
										Element::GROUP => 'notify'
									)
								),

								'notify_subject' => new Text
								(
									array
									(
										Form::LABEL => 'notify_subject',
										Element::GROUP => 'notify'
									)
								),

								'notify_template' => new Element
								(
									'textarea', array
									(
										Form::LABEL => 'notify_template',
										Element::GROUP => 'notify'
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