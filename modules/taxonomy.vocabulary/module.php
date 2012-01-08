<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\Taxonomy;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Event;
use ICanBoogie\Module;
use ICanBoogie\Operation;

use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Widget;

use Icybee\Manager;

class Vocabulary extends \Icybee\Module
{
	const OPERATION_ORDER = 'order';

	protected function block_manage()
	{
		return new Manager\Taxonomy\Vocabulary($this);
	}

	protected function block_edit(array $properties, $permission)
	{
		global $core;

		$core->document->css->add('public/edit.css');

		#
		# vocabulary scope
		#

		$scope_options = array();

		foreach ($core->modules->descriptors as $module_id => $descriptor)
		{
			if ($module_id == 'nodes' || !isset($core->modules[$module_id]))
			{
				continue;
			}

			$is_instance = Module::is_extending($module_id, 'nodes');

			if (!$is_instance)
			{
				continue;
			}

			$scope_options[$module_id] = t($module_id, array(), array('scpope' => array('module', 'title'), 'default' => $descriptor[self::T_TITLE]));
		}

		uasort($scope_options, 'wd_unaccent_compare_ci');

		$scope_value = null;
		$vid = $properties[ActiveRecord\Taxonomy\Vocabulary::VID];

		if ($vid)
		{
			$scope_value = $this->model('scopes')->select('constructor, 1')->find_by_vid($vid)->pairs;

			$properties[ActiveRecord\Taxonomy\Vocabulary::SCOPE] = $scope_value;
		}

		#
		# belonging site
		#

		if ($core->user->has_permission(Module\Nodes::PERMISSION_MODIFY_BELONGING_SITE))
		{
			// TODO-20100906: this should be added by the "sites" modules using the alter event.

			$siteid_el = new Element
			(
				'select', array
				(
					Element::LABEL => '.siteid',
					Element::LABEL_POSITION => 'before',
					Element::OPTIONS => array
					(
						null => ''
					)
					+ $core->models['sites']->select('siteid, IF(admin_title != "", admin_title, concat(title, ":", language))')->order('admin_title, title')->pairs,

					Element::DEFAULT_VALUE => $core->site_id,
					Element::GROUP => 'admin',
					Element::DESCRIPTION => '.siteid'
				)
			);
		}

		return array
		(
			Element::GROUPS => array
			(
				'settings' => array
				(
					'title' => '.options',
					'weight' => 100,
					'class' => 'form-section flat'
				)
			),

			Element::CHILDREN => array
			(
				ActiveRecord\Taxonomy\Vocabulary::VOCABULARY => new Widget\TitleSlugCombo
				(
					array
					(
						Form::LABEL => '.title',
						Element::REQUIRED => true
					)
				),

				ActiveRecord\Taxonomy\Vocabulary::SCOPE => new Element
				(
					Element::TYPE_CHECKBOX_GROUP, array
					(
						Form::LABEL => '.scope',
						Element::OPTIONS => $scope_options,
						Element::REQUIRED => true,

						'class' => 'list combo',
						'value' => $scope_value
					)
				),

				ActiveRecord\Taxonomy\Vocabulary::IS_TAGS => new Element
				(
					Element::TYPE_CHECKBOX, array
					(

						Element::LABEL => '.is_tags',
						Element::GROUP => 'settings',
						Element::DESCRIPTION => '.is_tags'
					)
				),

				ActiveRecord\Taxonomy\Vocabulary::IS_MULTIPLE => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => 'Multiple appartenance',
						Element::GROUP => 'settings',
						Element::DESCRIPTION => "Les enregistrements peuvent appartenir à
						plusieurs terms du vocabulaire (c'est toujours le cas pour les
						<em>étiquettes</em>)"
					)
				),

				ActiveRecord\Taxonomy\Vocabulary::IS_REQUIRED => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => 'Requis',
						Element::GROUP => 'settings',
						Element::DESCRIPTION => 'Au moins un terme de ce vocabulaire doit être
						sélectionné.'
					)
				),

				ActiveRecord\Taxonomy\Vocabulary::SITEID => $siteid_el
			)
		);
	}


	protected function block_order($vid)
	{
		global $core;

		$document = $core->document;

		$document->js->add('public/order.js');
		$document->css->add('public/order.css');

		$terms = $core->models['taxonomy.terms']->where('vid = ?', $vid)->order('term.weight, vtid')->all;

		$rc  = '<form id="taxonomy-order" method="post">';
		$rc .= '<input type="hidden" name="#operation" value="' . self::OPERATION_ORDER . '" />';
		$rc .= '<input type="hidden" name="#destination" value="' . $this . '" />';
		$rc .= '<input type="hidden" name="' . Operation::KEY . '" value="' . $vid . '" />';
		$rc .= '<ol>';

		foreach ($terms as $term)
		{
			$rc .= '<li>';
			$rc .= '<input type="hidden" name="terms[' . $term->vtid . ']" value="' . $term->weight . '" />';
			$rc .= wd_entities($term->term);
			$rc .= '</li>';
		}

		$rc .= '</ol>';

		$rc .= '<div class="actions">';
		$rc .= '<button class="save">' . t('label.save') . '</button>';
		$rc .= '</div>';

		$rc .= '</form>';

		return $rc;
	}
}