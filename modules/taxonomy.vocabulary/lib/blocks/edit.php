<?php

namespace ICanBoogie\Modules\Taxonomy\Vocabulary;

use ICanBoogie\ActiveRecord\Taxonomy\Vocabulary;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;
use Brickrouge\Widget;

class EditBlock extends \Icybee\EditBlock
{
	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('../../public/admin.css');
	}

	protected function alter_attributes(array $attributes)
	{
		return wd_array_merge_recursive
		(
			parent::alter_attributes($attributes), array
			(
				Element::GROUPS => array
				(
					'settings' => array
					(
						'title' => 'Options',
						'weight' => 100
					)
				)
			)
		);
	}

	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				Vocabulary::VOCABULARY => new Widget\TitleSlugCombo
				(
					array
					(
						Form::LABEL => 'title',
						Element::REQUIRED => true
					)
				),

				Vocabulary::SCOPE => $this->get_control__scope($properties, $attributes),

				Vocabulary::IS_TAGS => new Element
				(
					Element::TYPE_CHECKBOX, array
					(

						Element::LABEL => 'is_tags',
						Element::GROUP => 'settings',
						Element::DESCRIPTION => 'is_tags'
					)
				),

				Vocabulary::IS_MULTIPLE => new Element
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

				Vocabulary::IS_REQUIRED => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => 'Requis',
						Element::GROUP => 'settings',
						Element::DESCRIPTION => 'Au moins un terme de ce vocabulaire doit être
						sélectionné.'
					)
				),

				Vocabulary::SITEID => $this->get_control__site($properties, $attributes)
			)
		);
	}

	protected function get_control__scope(array &$properties, array &$attributes)
	{
		global $core;

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

			$scope_options[$module_id] = t($module_id, array(), array('scpope' => array('module', 'title'), 'default' => $descriptor[Module::T_TITLE]));
		}

		uasort($scope_options, 'wd_unaccent_compare_ci');

		$scope_value = null;
		$vid = $properties[Vocabulary::VID];

		if ($vid)
		{
			$scope_value = $this->module->model('scopes')->select('constructor, 1')->find_by_vid($vid)->pairs;

			$properties[Vocabulary::SCOPE] = $scope_value;
		}

		return new Element
		(
			Element::TYPE_CHECKBOX_GROUP, array
			(
				Form::LABEL => 'scope',
				Element::OPTIONS => $scope_options,
				Element::REQUIRED => true,

				'class' => 'list combo',
				'value' => $scope_value
			)
		);
	}

	protected function get_control__site(array &$properties, array &$attributes)
	{
		global $core;

		if (!$core->user->has_permission(\ICanBoogie\Modules\Nodes\Module::PERMISSION_MODIFY_BELONGING_SITE))
		{
			return;
		}

		// TODO-20100906: this should be added by the "sites" modules using the alter event.

		return new Element
		(
			'select', array
			(
				Form::LABEL => 'siteid',
				Element::OPTIONS => array
				(
					null => ''
				)
				+ $core->models['sites']->select('siteid, IF(admin_title != "", admin_title, concat(title, ":", language))')->order('admin_title, title')->pairs,

				Element::DEFAULT_VALUE => $core->site_id,
				Element::GROUP => 'admin',
				Element::DESCRIPTION => 'siteid'
			)
		);
	}
}