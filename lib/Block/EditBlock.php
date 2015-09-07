<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block;

use Brickrouge\Document;
use ICanBoogie\HTTP\ForceRedirect;
use ICanBoogie\HTTP\PermissionRequired;
use ICanBoogie\I18n;
use ICanBoogie\Operation;

use Brickrouge\A;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\SplitButton;

use Icybee;
use Icybee\Element\ActionbarToolbar;
use Icybee\Module;
use Icybee\Modules\Sites\Site;

/**
 * A record editor.
 *
 * @property-read \ICanBoogie\EventCollection $events
 * @property-read \ICanBoogie\Module\ModelCollection $models
 * @property \ICanBoogie\ActiveRecord $record
 * @property-read \ICanBoogie\HTTP\Request $request
 * @property-read \ICanBoogie\Session $session
 * @property-read \Icybee\Modules\Sites\Site $site
 * @property-read \Icybee\Modules\Users\User $user
 * @property-read \Icybee\Modules\Views\Collection $views
 */
class EditBlock extends FormBlock
{
	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->js->add(__DIR__ . '/EditBlock.js');
	}

	/**
	 * The key of the record to edit.
	 *
	 * @var int
	 */
	protected $key;

	protected function get_events()
	{
		return $this->app->events;
	}

	protected function get_models()
	{
		return $this->app->models;
	}

	/**
	 * Returns the record being edited or null if it's a new record.
	 *
	 * @return \ICanBoogie\ActiveRecord
	 */
	protected function lazy_get_record()
	{
		return $this->key ? $this->module->model[$this->key] : null;
	}

	protected function get_request()
	{
		return $this->app->request;
	}

	protected function get_session()
	{
		return $this->app->session;
	}

	protected function get_site()
	{
		return $this->app->site;
	}

	protected function get_user()
	{
		return $this->app->user;
	}

	protected function get_views()
	{
		return $this->app->views;
	}

	/**
	 * Constructor.
	 *
	 * @param Module $module
	 * @param array $attributes
	 * @param array $params
	 */
	public function __construct(Module $module, array $attributes = [], array $params = [])
	{
		$key = isset($params[0]) ? $params[0] : null;

		$this->key = $key;

		parent::__construct($module, $attributes);
	}

	/**
	 * Adds the `key` and `record` properties to the alter parameters.
	 *
	 * @inheritdoc
	 */
	protected function alter(array $params)
	{
		return parent::alter($params + [

			'key' => $this->key,
			'record' => $this->record

		]);
	}

	/**
	 * If the record to edit is locked by another user a {@link InterlockBlock} is returned
	 * instead of the {@link Form} element.
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
				return new InterlockBlock($module, [], [ 'lock' => $lock ]);
			}
		}

		I18n::push_scope($module->flat_id . '.edit');

		try
		{
			$element = parent::render();
			$this->element->save();

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
		$user = $this->user;
		$permission = $user->has_permission(Module::PERMISSION_CREATE, $this->module);

		#
		# check user ownership
		#

		$record = $this->record;

		if ($record && isset($record->uid))
		{
			$permission = $user->has_ownership($record);
		}

		return $permission;
	}

	protected function access_control()
	{
		$key = $this->key;
		$module_id = $this->module->id;

		if (!$key && !$this->permission)
		{
			throw new PermissionRequired(\ICanBoogie\format("You don't have permission to create records in the %id module.", [ 'id' => $module_id ]), 403);
		}

		#
		# Records that belong to a site can only be edited on that site, thus we need to change
		# site if the current site if not the one associated with the record.
		#

		$record = $this->record;

		if ($record && !($record instanceof Site)
		&& !empty($record->site_id) && $record->site_id != $this->site->site_id)
		{
			$url = $this->models['sites'][$record->site_id]->url;

			throw new ForceRedirect("$url/admin/$module_id/$key/edit");
		}
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
	 */
	protected function lazy_get_attributes()
	{
		return \ICanBoogie\array_merge_recursive(parent::lazy_get_attributes(), [

			Form::HIDDENS => [

				Operation::NAME => 'save',
				Operation::KEY => $this->key

			],

			Element::GROUPS => [

				'admin' => [

					'title' => 'Admin',
					'weight' => 1000

				]
			]
		]);
	}

	/**
	 * Fires the `alter_attributes:before` event of class {@link EditBlock\BeforeAlterAttributesEvent}.
	 *
	 * @inheritdoc
	 */
	protected function fire_before_alter_attributes(array $properties)
	{
		new EditBlock\BeforeAlterAttributesEvent($this, $properties);
	}

	/**
	 * Fires the `alter_attributes` event of class {@link EditBlock\AlterAttributesEvent}.
	 *
	 * @inheritdoc
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
	 */
	protected function lazy_get_values()
	{
		$schema = $this->module->model->extended_schema;
		$record = $this->record;
		$params = $this->request->request_params;

		return array_merge
		(
			parent::lazy_get_values(),
			$schema ? array_fill_keys(array_keys($schema->columns), null) : [],
			$record ? $record->to_array() : [],
			$params ? $params : []
		);
	}

	/**
	 * Fires the `alter_values:before` event of class {@link EditBlock\BeforeAlterValuesEvent}.
	 *
	 * @inheritdoc
	 */
	protected function fire_before_alter_values(array $properties)
	{
		new EditBlock\BeforeAlterValuesEvent($this, $properties);
	}

	/**
	 * Fires the `alter_values` event of class {@link EditBlock\AlterValuesEvent}.
	 *
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	protected function fire_before_alter_children(array $properties)
	{
		new EditBlock\BeforeAlterChildrenEvent($this, $properties);
	}

	/**
	 * Fires the `alter_children` event of class {@link EditBlock\AlterChildrenEvent}.
	 *
	 * @inheritdoc
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
	 */
	protected function lazy_get_actions()
	{
		return [

			'primary' => new Button('Save', [

				'class' => 'btn-primary',
				'type' => 'submit',
				'name' => false

			])

		];
	}

	/**
	 * Adds the save mode checkbox group of the actions as well as the action bar.
	 *
	 * @inheritdoc
	 */
	protected function alter_actions(array $actions, array $params)
	{
		$app = $this->app;
		$module = $this->module;
		$record = $this->record;

		$mode = isset($this->session->operation_save_mode[$module->id])
			? $this->session->operation_save_mode[$module->id]
			: Icybee\OPERATION_SAVE_MODE_LIST;

		$save_mode_options =[

			Icybee\OPERATION_SAVE_MODE_LIST => $this->t('save_mode_list', [ ], [ 'scope' => 'option' ]),
			Icybee\OPERATION_SAVE_MODE_CONTINUE => $this->t('save_mode_continue', [ ], [ 'scope' => 'option' ]),
			Icybee\OPERATION_SAVE_MODE_NEW => $this->t('save_mode_new', [ ], [ 'scope' => 'option' ]),

		];

		try
		{
			$this->views["{$module->id}/view"];
			$save_mode_options[Icybee\OPERATION_SAVE_MODE_DISPLAY] = $this->t('save_mode_display', [], [ 'scope' => 'option' ]);
		}
		catch (\Icybee\Modules\Views\Collection\ViewNotDefined $e)
		{
			if ($record instanceof \ICanBoogie\Prototyped && $record->has_property('url'))
			{
				$url = $record->url;

				if ($url)
				{
					$save_mode_options[Icybee\OPERATION_SAVE_MODE_DISPLAY] = $this->t('save_mode_display', [], [ 'scope' => 'option' ]);
				}
			}
		}

		if (empty($save_mode_options[$mode]))
		{
			$mode = key($save_mode_options);
		}

		$key = $this->key;
		$block = $this;

		$this->events->attach(function(ActionbarToolbar\CollectEvent $event, ActionbarToolbar $sender) use($record, $module, $key, $save_mode_options, $mode, $block, $app) {

			if ($record)
			{
				try
				{
					$url = $record->url;

					if ($url[0] != '#')
					{
						$event->buttons[] = '<a href="' . $record->url . '" class="actionbar-link">' . $this->t('View', [], [ 'scope' => 'button' ]) . '</a>';
					}
				}
				catch (\Exception $e) {}
			}

			$locked = true;

			if ($key)
			{
				$locked = $module->lock_entry($key, $lock);
			}

			if (!$locked)
			{
				return;
			}

			if ($key
			&& $this->user->has_permission(Module::PERMISSION_MANAGE, $module)
			&& $this->user->has_ownership($record))
			{
				$event->buttons[] = new A($this->t('Delete', [], [ 'scope' => 'button' ]), $this->app->url_for("admin:{$module}:confirm-delete", $record), [

					'class' => 'btn btn-danger'
				]);
			}

			if (isset($block->actions[Icybee\OPERATION_SAVE_MODE]))
			{
				$event->buttons[] = new SplitButton($save_mode_options[$mode], [

					Element::OPTIONS => $save_mode_options,

					'value' => $mode,
					'class' => 'btn-primary record-save-mode'

				]);
			}
		});

		return array_merge([

			Icybee\OPERATION_SAVE_MODE => new Element(Element::TYPE_RADIO_GROUP, [

				Element::GROUP => 'save',
				Element::OPTIONS => $save_mode_options,

				'value' => $mode,
				'class' => 'inputs-list save-mode'

			])

		], $actions);
	}

	/**
	 * Fires the `alter_actions:before` event of class {@link EditBlock\BeforeAlterActionsEvent}.
	 *
	 * @inheritdoc
	 */
	protected function fire_before_alter_actions(array $properties)
	{
		new EditBlock\BeforeAlterActionsEvent($this, $properties);
	}

	/**
	 * Fires the `alter_actions` event of class {@link EditBlock\AlterActionsEvent}.
	 *
	 * @inheritdoc
	 */
	protected function fire_alter_actions(array $properties)
	{
		new EditBlock\AlterActionsEvent($this, $properties);
	}

	/**
	 * If the user doesn't have the permission to edit the record, the actions of the
	 * {@link Form} element are set to `null` and the form is disabled.
	 *
	 * @inheritdoc
	 */
	protected function alter_element(Form $element, array $params)
	{
		$element = parent::alter_element($element, $params);

		if (!$this->permission)
		{
			$element[Form::ACTIONS] = null;
			$element[Form::DISABLED] = true;
		}

		$language = $this->site->language;

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

namespace Icybee\Block\EditBlock;

use Icybee\Block\EditBlock;

/**
 * Base class for the alter events of the {@link EditBlock} class.
 *
 * The class extends {@link FormBlock\AlterEvent} with the `key` and `record` properties.
 */
abstract class AlterEvent extends \Icybee\Block\FormBlock\AlterEvent
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
 * Event class for the `Icybee\Block\EditBlock::alter_attributes:before` event.
 */
class BeforeAlterAttributesEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_attributes:before`.
	 *
	 * @param EditBlock $target
	 * @param array $properties
	 */
	public function __construct(EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_attributes:before', $properties);
	}
}

/**
 * Event class for the `Icybee\Block\EditBlock::alter_attributes` event.
 */
class AlterAttributesEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_attributes`.
	 *
	 * @param EditBlock $target
	 * @param array $properties
	 */
	public function __construct(EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_attributes', $properties);
	}
}

/**
 * Event class for the `Icybee\Block\EditBlock::alter_values:before` event.
 */
class BeforeAlterValuesEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_values:before`.
	 *
	 * @param EditBlock $target
	 * @param array $properties
	 */
	public function __construct(EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_values:before', $properties);
	}
}

/**
 * Event class for the `Icybee\Block\EditBlock::alter_values` event.
 */
class AlterValuesEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_values`.
	 *
	 * @param EditBlock $target
	 * @param array $properties
	 */
	public function __construct(EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_values', $properties);
	}
}

/**
 * Event class for the `Icybee\Block\EditBlock::alter_children:before` event.
 */
class BeforeAlterChildrenEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_children:before`.
	 *
	 * @param EditBlock $target
	 * @param array $properties
	 */
	public function __construct(EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_children:before', $properties);
	}
}

/**
 * Event class for the `Icybee\Block\EditBlock::alter_children` event.
 */
class AlterChildrenEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_children`.
	 *
	 * @param EditBlock $target
	 * @param array $properties
	 */
	public function __construct(EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_children', $properties);
	}
}

/**
 * Event class for the `Icybee\Block\EditBlock::alter_actions:before` event.
 */
class BeforeAlterActionsEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_actions:before`.
	 *
	 * @param EditBlock $target
	 * @param array $properties
	 */
	public function __construct(EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_actions:before', $properties);
	}
}

/**
 * Event class for the `Icybee\Block\EditBlock::alter_actions` event.
 */
class AlterActionsEvent extends AlterEvent
{
	/**
	 * The event is constructed with the type `alter_actions`.
	 *
	 * @param EditBlock $target
	 * @param array $properties
	 */
	public function __construct(EditBlock $target, array $properties)
	{
		parent::__construct($target, 'alter_actions', $properties);
	}
}
