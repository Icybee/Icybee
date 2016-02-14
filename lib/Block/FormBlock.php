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

use ICanBoogie\I18n;
use ICanBoogie\Prototyped;
use ICanBoogie\Operation;

use Brickrouge\Button;
use Brickrouge\Document;
use Brickrouge\Element;
use Brickrouge\Form;

use Icybee\Module;

/**
 * Base class for form type blocks.
 *
 * @property-read \ICanBoogie\Core|\Icybee\Binding\CoreBindings $app
 * @property-read Document $document
 * @property-read boolean $permission
 *
 * @property Form $element The {@link Form} element.
 * @property array $attributes Attributes for the {@link Form} element.
 * @property array $actions Actions for the {@link Form} element.
 * @property array $children Children for the {@link Form} element.
 * @property array $values Values for the {@link Form} element.
 */
abstract class FormBlock extends Prototyped
{
	/**
	 * Adds assets to the document.
	 *
	 * The method doesn't add any asset.
	 *
	 * @inheritdoc
	 */
	static protected function add_assets(Document $document)
	{

	}

	/**
	 * Module requesting the block.
	 *
	 * @var Module
	 */
	protected $module;

	/**
	 * Attributes provided during construct.
	 *
	 * @var array
	 */
	protected $initial_attributes;

	protected function get_document()
	{
		return $this->app->document;
	}

	/**
	 * Returns whether the user has permission to display this block.
	 *
	 * @return bool
	 */
	abstract protected function get_permission();

	/**
	 * Constructor.
	 *
	 * @param Module $module
	 * @param array $attributes
	 */
	public function __construct(Module $module, array $attributes = [])
	{
		$this->module = $module;
		$this->initial_attributes = $attributes;

		$this->access_control();
	}

	/**
	 * Controls the access to the block.
	 *
	 * @throws \Exception if the used has no permission to access the block.
	 */
	abstract protected function access_control();

	/**
	 * Alters the various parameters of the block.
	 *
	 * For each parameter the method checks if a `alter_<param>` method exists. If the method
	 * exists the following methods are invoked to alter the value of the parameter:
	 *
	 * 1. `fire_before_<param>`: Fires the `alter_<param>:before` event.
	 * 2. `alter_<param>`: Alters the values of the parameter.
	 * 3. `fire_<param>`: Fires the `alter_<param>` event.
	 *
	 * @param array $params The parameters to alter.
	 *
	 * @return array
	 */
	protected function alter(array $params)
	{
		foreach ($params as $param => &$value)
		{
			$method_name = 'alter_' . $param;

			if (!method_exists($this, $method_name))
			{
				continue;
			}

			call_user_func([ $this, 'fire_before_' . $method_name ], $params);
			$value = $this->$method_name($value, $params);
			call_user_func([ $this, 'fire_' . $method_name ], $params);
		}

		return $params;
	}

	/**
	 * Renders the block into a {@link Form} element.
	 *
	 * The method invokes the {@link alter()} method to alter the attribute for the {@link Form}
	 * element, and invokes the {@link alter_element()} method to alter the {@link Form} element
	 * with the following properties:
	 *
	 * - `module`: The module creating the block.
	 * - `attributes`: The attributes of the {@link Form} element.
	 * - `actions`: The actions of the {@link Form} element.
	 * - `children`: The children of the {@link Form} element.
	 * - `values`: The values of the {@link Form} element.
	 *
	 * @return Form
	 */
	public function render()
	{
		static::add_assets($this->document);

		$this->attributes;
		$attributes = &$this->attributes;
		$this->values;
		$values = &$this->values;
		$this->children;
		$children = &$this->children;
		$this->actions;
		$actions = &$this->actions;

		$params = $this->alter([

			'module' => $this->module,
			'attributes' => &$attributes,
			'actions' => &$actions,
			'children' => &$children,
			'values' => &$values

		]);

		$attributes = [

			Form::ACTIONS => &$actions,
			Form::CHILDREN => &$children,
			Form::VALUES => &$values

		] + $params['attributes'];

		$this->alter_element($this->element, $params);

		return $this;
	}

	/**
	 * Renders the block into a HTML string.
	 *
	 * The method invokes the {@link render()} method.
	 *
	 * @return string
	 */
	public function __toString()
	{
		try
		{
			$this->render();

			I18n::push_scope($this->module->flat_id . '.' . \ICanBoogie\underscore(basename(strtr(get_class($this), '\\', '/'))));

			$html = (string) $this->element;

			I18n::pop_scope();

			return $html;
		}
		catch (\Exception $e)
		{
			return \Brickrouge\render_exception($e);
		}
	}

	public function t($native, array $args = [], array $options = [])
	{
		return $this->app->translate($native, $args, $options);
	}

	/*
	 * ATTRIBUTES
	 */

	/**
	 * Returns the attributes for the {@link Form} element.
	 *
	 * The following attributes are defined:
	 *
	 * - The destination of the operation: The module id.
	 * - The form renderer: An instance of {@link Brickrouge\Renderer\Simple} with the
	 * {@link Icybee\Element\Group} group class.
	 * - Groups: The `primary` group.
	 * - id: `editor.
	 * - action: An empty string.
	 * - class: `form-primary edit`.
	 * - name: The identifier of the module.
	 *
	 * @return array
	 */
	protected function lazy_get_attributes()
	{
		$module = $this->module;

		return \ICanBoogie\array_merge_recursive($this->initial_attributes, [

			Form::HIDDENS => [

				Operation::DESTINATION => $module->id

			],

			Form::RENDERER => new Form\GroupRenderer([

				Form\GroupRenderer::GROUP_CLASS => 'Icybee\Element\Group'

			]),

			Element::GROUPS => [

				'primary' => [

				]

			],

			'id' => 'editor',
			'action' => '',
			'class' => 'form-primary edit',
			'name' => (string) $module

		]);
	}

	/**
	 * Alters the attributes of the {@link Form} element.
	 *
	 * The method returns the attributes as is.
	 *
	 * @param array $attributes The attributes to alter.
	 * @param array $params The alter parameters.
	 *
	 * @return array
	 */
	protected function alter_attributes(array $attributes, array $params)
	{
		return $attributes;
	}

	/**
	 * Fires the `alter_attributes:before` event of class {@link FormBlock\BeforeAlterAttributesEvent}.
	 *
	 * @param array $payload The properties of the event.
	 */
	protected function fire_before_alter_attributes(array $payload)
	{
		new FormBlock\BeforeAlterAttributesEvent($this, $payload);
	}

	/**
	 * Fires the `alter_attributes` event of class {@link FormBlock\AlterAttributesEvent}.
	 *
	 * @param array $payload The properties of the event.
	 */
	protected function fire_alter_attributes(array $payload)
	{
		new FormBlock\AlterAttributesEvent($this, $payload);
	}

	/*
	 * VALUES
	 */

	/**
	 * Returns the values for the {@link Form} element.
	 *
	 * The method returns the values defined in the initial attributes or an empty array
	 * if they were not defined.
	 *
	 * @return array
	 */
	protected function lazy_get_values()
	{
		return isset($this->initial_attributes[Form::VALUES]) ? $this->initial_attributes[Form::VALUES] : [];
	}

	/**
	 * Alerts the values for the {@link Form} element.
	 *
	 * The method returns the values as is.
	 *
	 * @param array $values The values to alter.
	 * @param array $params The alter parameters.
	 *
	 * @return array
	 */
	protected function alter_values(array $values, array $params)
	{
		return $values;
	}

	/**
	 * Fires the `alter_values:before` event of class {@link FormBlock\BeforeAlterValuesEvent}.
	 *
	 * @param array $payload The properties of the event.
	 */
	protected function fire_before_alter_values(array $payload)
	{
		new FormBlock\BeforeAlterValuesEvent($this, $payload);
	}

	/**
	 * Fires the `alter_values` event of class {@link FormBlock\AlterValuesEvent}.
	 *
	 * @param array $payload The properties of the event.
	 */
	protected function fire_alter_values(array $payload)
	{
		new FormBlock\AlterValuesEvent($this, $payload);
	}

	/*
	 * CHILDREN
	 */

	/**
	 * Returns the children of the {@link Form} element.
	 *
	 * The method returns the children defined in the initial attributes or an empty array
	 * if they were not defined.
	 *
	 * @return array
	 */
	protected function lazy_get_children()
	{
		return isset($this->initial_attributes[Element::CHILDREN])
			? $this->initial_attributes[Element::CHILDREN]
			: [];
	}

	/**
	 * Alters the children for the {@link Form} element.
	 *
	 * The method returns the children as is.
	 *
	 * @param array $children The children to alter.
	 * @param array $params The alter parameters.
	 *
	 * @return array
	 */
	protected function alter_children(array $children, array $params)
	{
		return $children;
	}

	/**
	 * Fires the `alter_children:before` event of class {@link FormBlock\BeforeAlterChildrenEvent}.
	 *
	 * @param array $payload The properties of the event.
	 */
	protected function fire_before_alter_children(array $payload)
	{
		new FormBlock\BeforeAlterChildrenEvent($this, $payload);
	}

	/**
	 * Fires the `alter_children` event of class {@link FormBlock\AlterChildrenEvent}.
	 *
	 * @param array $payload The properties of the event.
	 */
	protected function fire_alter_children(array $payload)
	{
		new FormBlock\AlterChildrenEvent($this, $payload);
	}

	/*
	 * ACTIONS
	 */

	/**
	 * Returns the actions for the {@link Form} element.
	 *
	 * The method returns an array with a `Send` button. The button can be overrode using the
	 * `primary` key.
	 *
	 * @return array
	 */
	protected function lazy_get_actions()
	{
		return [

			'primary' => new Button('Send', [

				'class' => 'btn-primary',
				'type' => 'submit',
				'name' => false

			])
		];
	}

	/**
	 * Alters the actions for the {@link Form} element.
	 *
	 * The method returns the actions as is.
	 *
	 * @param array $actions The actions to alter.
	 * @param array $params The alter parameters.
	 *
	 * @return array
	 */
	protected function alter_actions(array $actions, array $params)
	{
		return $actions;
	}

	/**
	 * Fires the `alter_actions:before` event of class {@link FormBlock\BeforeAlterActionsEvent}.
	 *
	 * @param array $payload The properties of the event.
	 */
	protected function fire_before_alter_actions(array $payload)
	{
		new FormBlock\BeforeAlterActionsEvent($this, $payload);
	}

	/**
	 * Fires the `alter_actions` event of class {@link FormBlock\AlterActionsEvent}.
	 *
	 * @param array $payload The properties of the event.
	 */
	protected function fire_alter_actions(array $payload)
	{
		new FormBlock\AlterActionsEvent($this, $payload);
	}

	/*
	 * ELEMENT
	 */

	/**
	 * Returns the {@link Form} element.
	 *
	 * @return \Brickrouge\Form
	 */
	protected function lazy_get_element()
	{
		return new Form($this->attributes);
	}

	/**
	 * Alters the {@link Form} element.
	 *
	 * The method return the element as is.
	 *
	 * @param Form $element The element to alter.
	 * @param array $params The alter parameters.
	 *
	 * @return Form
	 */
	protected function alter_element(Form $element, array $params)
	{
		return $element;
	}
}

namespace Icybee\FormBlock;

use ICanBoogie\Event;
use Icybee\FormBlock;









