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
use ICanBoogie\I18n;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\A;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\SplitButton;

class EditBlock extends Form
{
	/**
	 * Module requesting the block.
	 *
	 * @var Module
	 */
	protected $module;

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
	 * @param array $params
	 * @param array $attributes
	 */
	public function __construct(Module $module, array $attributes=array(), array $params=array())
	{
		$key = isset($params[0]) ? $params[0] : null;

		$this->module = $module;
		$this->key = $key;

		$this->access_control();

		I18n::push_scope($module->flat_id . '.edit');

		try
		{
			$properties = $this->properties;
			$children = $this->children;
			$actions = array
			(
				new Button
				(
					'Save', array
					(
						Element::GROUP => 'save',

						'class' => 'btn-primary',
						'type' => 'submit'
					)
				)
			);

			$attributes += array
			(
				self::ACTIONS => &$actions,

				self::RENDERER => new \Brickrouge\Renderer\Simple
				(
					array
					(
						\Brickrouge\Renderer\Simple::GROUP_CLASS => 'Icybee\Element\Group'
					)
				),

				self::VALUES => &$properties,
				self::CHILDREN => &$children,

				'id' => 'editor',
				'action' => '',
				'class' => 'form-primary edit',
				'name' => (string) $module
			);

			$alter_params = array
			(
				'module' => $module,
				'key' => $key,
				'attributes' => &$attributes
			);

			$this->fire_before_alter_attributes($alter_params);
			$attributes = $this->alter_attributes($attributes);
			$this->fire_alter_attributes($alter_params);

			$alter_params['properties'] = &$properties;

			$this->fire_before_alter_properties($alter_params);
			$properties = $this->alter_properties($properties, $attributes);
			$this->fire_alter_properties($alter_params);

			$alter_params['children'] = &$children;

			$this->fire_before_alter_children($alter_params);
			$children = $this->alter_children($children, $properties, $attributes);
			$this->fire_alter_children($alter_params);

			$alter_params['actions'] = &$actions;

			$this->fire_before_alter_actions($alter_params);
			$actions = $this->alter_actions($actions);
			$this->fire_alter_actions($alter_params);

			if (!$this->permission)
			{
				$attributes[self::ACTIONS] = null;
				$attributes[self::DISABLED] = true;
			}
		}
		catch (\Exception $e)
		{
			I18n::pop_scope();

			throw $e;
		}

		I18n::pop_scope();

		parent::__construct($attributes);
	}

	public function __toString()
	{
		global $core;

		$key = $this->key;

		if ($key)
		{
			$locked = $this->module->lock_entry($key, $lock);

			$this->locked = $locked;

			if (!$locked)
			{
				$luser = $core->models['users'][$lock['uid']];
				$url = $core->request->path;

				$time = round((strtotime($lock['until']) - time()) / 60);
				$message = $time ? "Le verrou devrait disparaitre dans $time minutes." : "Le verrou devrait disparaitre dans moins d'une minutes.";

				return <<<EOT
<div class="block-alert">
<h2>Édition impossible</h2>
<p>Impossible d'éditer l'enregistrement parce qu'il est en cours d'édition par <em>$luser->name</em> <span class="small">($luser->username)</span>.</p>
<form method="get" action="">
<input type="hidden" name="retry" value="1" />
<div class="form-actions">
<button class="btn-success">Réessayer</button> <span class="small light">$message</span>
</div>
</form>
</div>
EOT;
			}
		}

		#
		#
		#

		try
		{
			$this->alter_elements_lang();
		}
		catch (\Exception $e)
		{
			return \Brickrouge\render_exception($e);
		}

		#
		#
		#

		$this->save();

		I18n::push_scope($this->module->flat_id . '.edit');

		try
		{
			$rc = parent::__toString();
		}
		catch (\Exception $e)
		{
			$rc = \Brickrouge\render_exception($e);
		}

		I18n::pop_scope();

		return $rc;
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add(ASSETS . 'js/edit.js');
	}

	protected function __get_permission()
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
			throw new Exception\HTTP("You don't have permission to create records in the %id module.", array('id' => $module_id), 403);
		}

		#
		# Records that belong to a site can only be edited on that site, thus we need to change
		# site if the current site if not the one associated with the record.
		#

		$record = $this->record;

		if ($record && !($record instanceof \ICanBoogie\ActiveRecord\Site)
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
	protected function __get_record()
	{
		return $this->key ? $this->module->model[$this->key] : null;
	}

	/*
	 * ATTRIBUTES
	 */

	protected function alter_attributes(array $attributes)
	{
		return \ICanBoogie\array_merge_recursive
		(
			array
			(
				Form::HIDDENS => array
				(
					Operation::DESTINATION => $this->module->id,
					Operation::NAME => 'save',
					Operation::KEY => $this->key
				),

				Element::GROUPS => array
				(
					'primary' => array
					(

					),

					'admin' => array
					(
						'title' => 'Admin',
						'weight' => 1000
					)
				)
			)

			+ $attributes
		);
	}

	protected function fire_before_alter_attributes(array $properties)
	{
		new EditBlock\BeforeAlterAttributesEvent($this, $properties);
	}

	protected function fire_alter_attributes(array $properties)
	{
		new EditBlock\AlterAttributesEvent($this, $properties);
	}

	/*
	 * PROPERTIES
	 */

	/**
	 * Returns the editable properties of the record.
	 *
	 * @return array
	 */
	protected function __get_properties()
	{
		global $core;

		$schema = $this->module->model->extended_schema;
		$record = $this->record;
		$params = $core->request->request_params;

		return array_merge
		(
			$schema ? array_fill_keys(array_keys($schema['fields']), null) : array(),
			$record ? get_object_vars($record) : array(),
			$params ? $params : array()
		);
	}

	/**
	 * Alerts the editable properties of the record.
	 *
	 * @param array $properties
	 */
	protected function alter_properties(array $properties, array &$attributes)
	{
		return $properties;
	}

	protected function fire_before_alter_properties(array $properties)
	{
		new EditBlock\BeforeAlterPropertiesEvent($this, $properties);
	}

	protected function fire_alter_properties(array $properties)
	{
		new EditBlock\AlterPropertiesEvent($this, $properties);
	}

	/*
	 * CHILDREN
	 */

	/**
	 * Alerts the children of the block.
	 *
	 * @param array $children
	 */
	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		return $children;
	}

	protected function fire_before_alter_children(array $properties)
	{
		new EditBlock\BeforeAlterChildrenEvent($this, $properties);
	}

	protected function fire_alter_children(array $properties)
	{
		new EditBlock\AlterChildrenEvent($this, $properties);
	}

	/*
	 * ACTIONS
	 */

	/**
	 * Alerts form actions
	 */
	protected function alter_actions(array $actions)
	{
		global $core;

		$module = $this->module;
		$mode = isset($core->session->wdpmodule[SaveOperation::MODE][$module->id]) ? $core->session->wdpmodule[SaveOperation::MODE][$module->id] : SaveOperation::MODE_LIST;

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
				$save_mode_options[\ICanBoogie\Modules\Nodes\SaveOperation::MODE_DISPLAY] = t('save_mode_display', array(), array('scope' => 'option'));
			}
		}

		if (empty($save_mode_options[$mode]))
		{
			$mode = key($save_mode_options);
		}

		$key = $this->key;
		$block = $this;

		\ICanBoogie\Events::attach
		(
			'Icybee\Admin\Element\ActionbarToolbar::alter_buttons', function(Event $event, \Icybee\Admin\Element\ActionbarToolbar $sender) use($record, $module, $key, $save_mode_options, $mode, $block)
			{
				global $core;

				if ($record instanceof \ICanBoogie\ActiveRecord\Node && $record->url[0] != '#')
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

					if (isset($block[Form::ACTIONS][SaveOperation::MODE]))
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

	protected function fire_before_alter_actions(array $properties)
	{
		new EditBlock\BeforeAlterActionsEvent($this, $properties);
	}

	protected function fire_alter_actions(array $properties)
	{
		new EditBlock\AlterActionsEvent($this, $properties);
	}

	protected function alter_elements_lang()
	{
		global $core;

		$iterator = new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);
		$language = $core->site->language;

		foreach ($iterator as $element)
		{
			if ($element->tag_name != 'textarea')
			{
				continue;
			}

			$element['lang'] = $language;
		}
	}
}

namespace Icybee\EditBlock;

/**
 * Event class for the `Icybee\EditBlock::alter_attributes:before` event.
 */
class BeforeAlterAttributesEvent extends \ICanBoogie\Event
{
	/**
	 * The module creating the block.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Key of the record being edited.
	 *
	 * @var int
	 */
	public $key;

	/**
	 * Reference to the attributes of the block.
	 *
	 * @var array
	 */
	public $attributes;

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
class AlterAttributesEvent extends \ICanBoogie\Event
{
	/**
	 * The module creating the block.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Key of the record being edited.
	 *
	 * @var int
	 */
	public $key;

	/**
	 * Reference to the attributes of the block.
	 *
	 * @var array
	 */
	public $attributes;

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
 * Event class for the `Icybee\EditBlock::alter_properties:before` event.
 */
class BeforeAlterPropertiesEvent extends \ICanBoogie\Event
{
	/**
	 * The module creating the block.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Key of the record being edited.
	 *
	 * @var int
	 */
	public $key;

	/**
	 * Reference to the attributes of the block.
	 *
	 * @var array
	 */
	public $attributes;

	/**
	 * Reference to the properties of the record being edited.
	 *
	 * @var array
	 */
	public $properties;

	/**
	 * The event is constructed with the type `alter_properties:before`.
	 *
	 * @param \Icybee\EditBlock $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_properties:before', $properties);
	}
}

/**
 * Event class for the `Icybee\EditBlock::alter_properties` event.
 */
class AlterPropertiesEvent extends \ICanBoogie\Event
{
	/**
	 * The module creating the block.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Key of the record being edited.
	 *
	 * @var int
	 */
	public $key;

	/**
	 * Reference to the attributes of the block.
	 *
	 * @var array
	 */
	public $attributes;

	/**
	 * Reference to the properties of the record being edited.
	 *
	 * @var array
	 */
	public $properties;

	/**
	 * The event is constructed with the type `alter_properties`.
	 *
	 * @param \Icybee\EditBlock $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_properties', $properties);
	}
}

/**
 * Event class for the `Icybee\EditBlock::alter_children:before` event.
 */
class BeforeAlterChildrenEvent extends \ICanBoogie\Event
{
	/**
	 * The module creating the block.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Key of the record being edited.
	 *
	 * @var int
	 */
	public $key;

	/**
	 * Reference to the attributes of the block.
	 *
	 * @var array
	 */
	public $attributes;

	/**
	 * Reference to the properties of the record being edited.
	 *
	 * @var array
	 */
	public $properties;

	/**
	 * Reference to the children of the block.
	 *
	 * @var array
	 */
	public $children;

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
class AlterChildrenEvent extends \ICanBoogie\Event
{
	/**
	 * The module creating the block.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Key of the record being edited.
	 *
	 * @var int
	 */
	public $key;

	/**
	 * Reference to the attributes of the block.
	 *
	 * @var array
	 */
	public $attributes;

	/**
	 * Reference to the properties of the record being edited.
	 *
	 * @var array
	 */
	public $properties;

	/**
	 * Reference to the children of the block.
	 *
	 * @var array
	 */
	public $children;

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
class BeforeAlterActionsEvent extends \ICanBoogie\Event
{
	/**
	 * The module creating the block.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Key of the record being edited.
	 *
	 * @var int
	 */
	public $key;

	/**
	 * Reference to the attributes of the block.
	 *
	 * @var array
	 */
	public $attributes;

	/**
	 * Reference to the properties of the record being edited.
	 *
	 * @var array
	 */
	public $properties;

	/**
	 * Reference to the children of the block.
	 *
	 * @var array
	 */
	public $children;

	/**
	 * Reference to the actions of the block.
	 *
	 * @var array
	 */
	public $actions;

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
class AlterActionsEvent extends \ICanBoogie\Event
{
	/**
	 * The module creating the block.
	 *
	 * @var \ICanBoogie\Module
	 */
	public $module;

	/**
	 * Key of the record being edited.
	 *
	 * @var int
	 */
	public $key;

	/**
	 * Reference to the attributes of the block.
	 *
	 * @var array
	 */
	public $attributes;

	/**
	 * Reference to the properties of the record being edited.
	 *
	 * @var array
	 */
	public $properties;

	/**
	 * Reference to the children of the block.
	 *
	 * @var array
	 */
	public $children;

	/**
	 * Reference to the actions of the block.
	 *
	 * @var array
	 */
	public $actions;

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