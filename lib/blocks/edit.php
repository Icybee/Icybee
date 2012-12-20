<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\HTTP\HTTPError;
use ICanBoogie\I18n;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\A;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\SplitButton;

use Icybee\Element\ActionbarToolbar;

/**
 * A record editor.
 */
class EditBlock extends FormBlock
{
	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add(ASSETS . 'js/edit.js');
	}

	/**
	 * The key of the record to edit.
	 *
	 * @var int
	 */
	protected $key;

	/**
	 * Constructor.
	 *
	 * @param Module $module
	 * @param array $attributes
	 * @param array $params
	 */
	public function __construct(Module $module, array $attributes=array(), array $params=array())
	{
		$key = isset($params[0]) ? $params[0] : null;

		$this->key = $key;

		parent::__construct($module, $attributes);
	}

	/**
	 * Adds the `key` and `record` properties to the alter parameters.
	 *
	 * @see FormBlock::alter()
	 */
	protected function alter(array $params)
	{
		return parent::alter
		(
			$params + array
			(
				'key' => $this->key,
				'record' => $this->record
			)
		);
	}

	/**
	 * If the record to edit is locked by another user a {@link InterlockBlock} is returned
	 * instead of the {@link Form} element.
	 *
	 * @see Icybee.FormBlock::render()
	 */
	public function render()
	{
		$module = $this->module;
		$key = $this->key;

		if ($key)
		{
			$locked = $module->lock_entry($key, $lock);

			$this->locked = $locked;

			if (!$locked)
			{
				return new InterlockBlock($module, array(), array('lock' => $lock));
			}
		}

		I18n::push_scope($module->flat_id . '.edit');

		try
		{
			$element = parent::render();
			$element->save();

			I18n::pop_scope();

			return $element;
		}
		catch (\Exception $e)
		{
			I18n::pop_scope();

			throw $e;
		}
	}

	protected function get_permission()
	{
		global $core;

		$user = $core->user;
		$permission = $user->has_permission(Module::PERMISSION_CREATE, $this->module);

		#
		# check user ownership
		#

		$record = $this->record;

		if ($record && isset($record->uid))
		{
			$permission = $user->has_ownership($this->module, $record);
		}

		return $permission;
	}

	protected function access_control()
	{
		global $core;

		$key = $this->key;
		$module_id = $this->module->id;

		if (!$key && !$this->permission)
		{
			throw new HTTPError(\ICanBoogie\format("You don't have permission to create records in the %id module.", array('id' => $module_id)), 403);
		}

		#
		# Records that belong to a site can only be edited on that site, thus we need to change
		# site if the current site if not the one associated with the record.
		#

		$record = $this->record;

		if ($record && !($record instanceof \Icybee\Modules\Sites\Site)
		&& !empty($record->siteid) && $record->siteid != $core->site_id)
		{
			$url = $core->models['sites'][$record->siteid]->url;

			header("Location: $url/admin/$module_id/$key/edit");

			exit;
		}
	}

	/**
	 * Returns the record being edited or null if its a new record.
	 *
	 * @return \ICanBoogie\ActiveRecord
	 */
	protected function get_record()
	{
		return $this->key ? $this->module->model[$this->key] : null;
	}

	/*
	 * ATTRIBUTES
	 */

	/**
	 * Adds the following:
	 *
	 * - The name of the operation: `save`.
	 * - The key of the operation: The key provided during construct.
	 * - The `admin` element group.
	 *
	 * @see Icybee\FormBlock::get_attributes()
	 */
	protected function get_attributes()
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::get_attributes(), array
			(
				Form::HIDDENS => array
				(
					Operation::NAME => 'save',
					Operation::KEY => $this->key
				),

				Element::GROUPS => array
				(
					'admin' => array
					(
						'title' => 'Admin',
						'weight' => 1000
					)
				)
			)
		);
	}

	/**
	 * Fires the `alter_attributes:before` event of class {@link EditBlock\BeforeAlterAttributesEvent}.
	 *
	 * @see FormBlock::fire_before_alter_attributes()
	 */
	protected function fire_before_alter_attributes(array $properties)
	{
		new EditBlock\BeforeAlterAttributesEvent($this, $properties);
	}

	/**
	 * Fires the `alter_attributes` event of class {@link EditBlock\AlterAttributesEvent}.
	 *
	 * @see FormBlock::fire_alter_attributes()
	 */
	protected function fire_alter_attributes(array $properties)
	{
		new EditBlock\AlterAttributesEvent($this, $properties);
	}

	/*
	 * VALUES
	 */

	/**
	 * Merges the values returned by the parent with the following arrays:
	 *
	 * - An array with all the properties of the extended schema set to `null`.
	 * - An array with the properties of the record.
	 * - An array with the request params of the request.
	 *
	 * @see FormBlock::get_values()
	 */
	protected function get_values()
	{
		global $core;

		$schema = $this->module->model->extended_schema;
		$record = $this->record;
		$params = $core->request->request_params;

		return array_merge
		(
			parent::get_values(),
			$schema ? array_fill_keys(array_keys($schema['fields']), null) : array(),
			$record ? get_object_vars($record) : array(),
			$params ? $params : array()
		);
	}

	/**
	 * Fires the `alter_values:before` event of class {@link EditBlock\BeforeAlterValuesEvent}.
	 *
	 * @see FormBlock::fire_before_alter_values()
	 */
	protected function fire_before_alter_values(array $properties)
	{
		new EditBlock\BeforeAlterValuesEvent($this, $properties);
	}

	/**
	 * Fires the `alter_values` event of class {@link EditBlock\AlterValuesEvent}.
	 *
	 * @see FormBlock::fire_alter_values()
	 */
	protected function fire_alter_values(array $properties)
	{
		new EditBlock\AlterValuesEvent($this, $properties);
	}

	/*
	 * CHILDREN
	 */

	/**
	 * Fires the `alter_children:before` event of class {@link EditBlock\BeforeAlterChildrenEvent}.
	 *
	 * @see Icybee\FormBlock::fire_before_alter_children()
	 */
	protected function fire_before_alter_children(array $properties)
	{
		new EditBlock\BeforeAlterChildrenEvent($this, $properties);
	}

	/**
	 * Fires the `alter_children` event of class {@link EditBlock\AlterChildrenEvent}.
	 *
	 * @see Icybee\FormBlock::fire_alter_children()
	 */
	protected function fire_alter_children(array $properties)
	{
		new EditBlock\AlterChildrenEvent($this, $properties);
	}

	/*
	 * ACTIONS
	 */

	/**
	 * Replaces the primary button with a button with the label "Save".
	 *
	 * @see FormBlock::get_actions()
	 */
	protected function get_actions()
	{
		return array
		(
			'primary' => new Button
			(
				'Save', array
				(
					'class' => 'btn-primary',
					'type' => 'submit',
					'name' => false
				)
			)
		);
	}

	/**
	 * Adds the save mode checkbox group of the actions as well as the action bar.
	 *
	 * @see FormBlock::alter_actions()
	 */
	protected function alter_actions(array $actions, array $params)
	{
		global $core;

		$module = $this->module;
		$mode = isset($core->session->save_mode[SaveOperation::MODE][$module->id]) ? $core->session->save_mode[SaveOperation::MODE][$module->id] : SaveOperation::MODE_LIST;

		$save_mode_options = array
		(
			SaveOperation::MODE_LIST => t('save_mode_list', array(), array('scope' => 'option')),
			SaveOperation::MODE_CONTINUE => t('save_mode_continue', array(), array('scope' => 'option')),
			SaveOperation::MODE_NEW => t('save_mode_new', array(), array('scope' => 'option')),
		);

		$record = $this->record;

		if ($record instanceof \ICanBoogie\Object && $record->has_property('url'))
		{
			$url = $record->url;

			if ($url)
			{
				$save_mode_options[\Icybee\Modules\Nodes\SaveOperation::MODE_DISPLAY] = t('save_mode_display', array(), array('scope' => 'option'));
			}
		}

		if (empty($save_mode_options[$mode]))
		{
			$mode = key($save_mode_options);
		}

		$key = $this->key;
		$block = $this;

		\ICanBoogie\Event\attach
		(
			function(ActionbarToolbar\CollectEvent $event, ActionbarToolbar $sender) use($record, $module, $key, $save_mode_options, $mode, $block)
			{
				global $core;

				if ($record instanceof \Icybee\Modules\Nodes\Node && $record->url[0] != '#')
				{
					$event->buttons[] = '<a href="' . $record->url . '" class="actionbar-link">' . t('View', array(), array('scope' => 'button')) . '</a>';
				}

				$locked = true;

				if ($key)
				{
					$locked = $module->lock_entry($key, $lock);
				}

				if ($locked)
				{
					if ($key && $core->user->has_permission(Module::PERMISSION_MANAGE, $module) && $core->user->has_ownership($module, $record))
					{
						$event->buttons[] = new A
						(
							t('Delete', array(), array('scope' => 'button')), Route::contextualize('/admin/' . $module . '/' . $key . '/delete'), array
							(
								'class' => 'btn btn-danger'
							)
						);
					}

					if (isset($block->actions[SaveOperation::MODE]))
					{
						$event->buttons[] = new SplitButton
						(
							$save_mode_options[$mode], array
							(
								Element::OPTIONS => $save_mode_options,

								'value' => $mode,
								'class' => 'btn-primary record-save-mode'
							)
						);
					}
				}
			}
		);

		return array_merge
		(
			array
			(
				SaveOperation::MODE => new Element
				(
					Element::TYPE_RADIO_GROUP, array
					(
						Element::GROUP => 'save',
						Element::OPTIONS => $save_mode_options,

						'value' => $mode,
						'class' => 'inputs-list save-mode'
					)
				)
			),

			$actions
		);
	}

	/**
	 * Fires the `alter_actions:before` event of class {@link EditBlock\BeforeAlterActionsEvent}.
	 *
	 * @see Icybee\FormBlock::fire_before_alter_actions()
	 */
	protected function fire_before_alter_actions(array $properties)
	{
		new EditBlock\BeforeAlterActionsEvent($this, $properties);
	}

	/**
	 * Fires the `alter_actions` event of class {@link EditBlock\AlterActionsEvent}.
	 *
	 * @see Icybee\FormBlock::fire_alter_actions()
	 */
	protected function fire_alter_actions(array $properties)
	{
		new EditBlock\AlterActionsEvent($this, $properties);
	}

	/**
	 * If the user doesn't have the permission to edit the record, the actions of the
	 * {@link Form} element are set to `null` and the form is disabled.
	 *
	 * @see FormBlock::alter_element()
	 */
	protected function alter_element(Form $element, array $params)
	{
		global $core;

		$element = parent::alter_element($element, $params);

		if (!$this->permission)
		{
			$element[self::ACTIONS] = null;
			$element[self::DISABLED] = true;
		}

		$language = $core->site->language;

		foreach ($element as $control)
		{
			if ($control->tag_name != 'textarea')
			{
				continue;
			}

			$control['lang'] = $language;
		}

		return $element;
	}
}

namespace Icybee\EditBlock;

/**
 * Base class for the alter events of the {@link EditBlock} class.
 *
 * The class extends {@link FormBlock\AlterEvent} with the `key` and `record` properties.
 */
abstract class AlterEvent extends \Icybee\FormBlock\AlterEvent
{
	/**
	 * Key of the record being edited.
	 *
	 * @var int
	 */
	public $key;

	/**
	 * The record being edited.
	 *
	 * @var \ICanBoogie\ActiveRecord
	 */
	public $record;
}

/**
 * Event class for the `Icybee\EditBlock::alter_attributes:before` event.
 */
class BeforeAlterAttributesEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_attributes:before`.
	 *
	 * @param \Icybee\EditBlock $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_attributes:before', $properties);
	}
}

/**
 * Event class for the `Icybee\EditBlock::alter_attributes` event.
 */
class AlterAttributesEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_attributes`.
	 *
	 * @param \Icybee\EditBlock $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_attributes', $properties);
	}
}

/**
 * Event class for the `Icybee\EditBlock::alter_values:before` event.
 */
class BeforeAlterValuesEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_values:before`.
	 *
	 * @param \Icybee\EditBlock $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_values:before', $properties);
	}
}

/**
 * Event class for the `Icybee\EditBlock::alter_values` event.
 */
class AlterValuesEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_values`.
	 *
	 * @param \Icybee\EditBlock $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_values', $properties);
	}
}

/**
 * Event class for the `Icybee\EditBlock::alter_children:before` event.
 */
class BeforeAlterChildrenEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_children:before`.
	 *
	 * @param \Icybee\EditBlock $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_children:before', $properties);
	}
}

/**
 * Event class for the `Icybee\EditBlock::alter_children` event.
 */
class AlterChildrenEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_children`.
	 *
	 * @param \Icybee\EditBlock $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_children', $properties);
	}
}

/**
 * Event class for the `Icybee\EditBlock::alter_actions:before` event.
 */
class BeforeAlterActionsEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_actions:before`.
	 *
	 * @param \Icybee\EditBlock $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_actions:before', $properties);
	}
}

/**
 * Event class for the `Icybee\EditBlock::alter_actions` event.
 */
class AlterActionsEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_actions`.
	 *
	 * @param \Icybee\EditBlock $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_actions', $properties);
	}
}