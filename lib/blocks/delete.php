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

use ICanBoogie\ActiveRecord;
use ICanBoogie\I18n;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;

/**
 * A block to delete a record.
 *
 * @property string $title The localized title of the block. {@link get_title()}
 * @property ActiveRecord $record The record to delete. {@link get_record()}
 * @property string $record_name The name of the record to delete. {@link get_record_name()}
 */
class DeleteBlock extends Form
{
	/**
	 * Module associated with this block.
	 *
	 * @var Module
	 */
	protected $module;

	/**
	 * Key of the record to delete.
	 *
	 * @var int
	 */
	protected $key;

	/**
	 * Constructor.
	 *
	 * @param Module $module
	 * @param array $attributes
	 * @param array $params Index 0 hold the key of the record to delete.
	 */
	public function __construct(Module $module, array $attributes=array(), array $params=array())
	{
		$this->module = $module;
		$this->key = current($params);

		parent::__construct
		(
			$attributes + array
			(
				Form::HIDDENS => array
				(
					Operation::DESTINATION => $module->id,
					Operation::NAME => Module::OPERATION_DELETE,
					Operation::KEY => $this->key,

					'#location' => \ICanBoogie\Routing\contextualize("/admin/{$module->id}")
				),

				self::ACTIONS => array
				(
					new Button
					(
						'Delete', array
						(
							'class' => 'btn-primary btn-danger',
							'type' => 'submit'
						)
					)
				),

				self::CHILDREN => array
				(
					$this->title_element,
					$this->question_element,
					$this->preview_element,
					$this->dependencies_element
				)
			)
		);
	}

	public function __toString()
	{
		try
		{
			$record = $this->record;
		}
		catch (\Exception $e)
		{
			try
			{
				$message = I18n\t('Unknown record id: %key', array('%key' => $this->key));

				return <<<EOT
<div class="block-alert block--delete">
$this->title_element
<div class="alert alert-error">$message</div>
</div>
EOT;
			}
			catch (\Exception $e)
			{
				return \ICanBoogie\Debug::format_alert($e);
			}
		}

		return parent::__toString();
	}

	/**
	 * Returns the localized title.
	 *
	 * @return string
	 */
	protected function get_title()
	{
		return I18n\t('Delete a record');
	}

	/**
	 * Returns the title element.
	 *
	 * @return \Brickrouge\Element
	 */
	protected function get_title_element()
	{
		return new Element('h1', array(Element::INNER_HTML => \Brickrouge\escape($this->title), 'class' => 'block-title'));
	}

	/**
	 * Returns the record to delete.
	 *
	 * @return ActiveRecord
	 */
	protected function get_record()
	{
		return $this->module->model[$this->key];
	}

	/**
	 * Returns the record name.
	 *
	 * @return string
	 */
	protected function get_record_name()
	{

	}

	/**
	 * Returns the localized confirmation question.
	 *
	 * @return string
	 */
	protected function get_question()
	{
		$record_name = $this->record_name;

		if ($record_name)
		{
			$record_name = '<q>' . $record_name . '</q>';
		}
		else
		{
			$record_name = I18n\t('record_name', array(), array('default' => 'this record'));
		}

		return I18n\t('Are you sure you want to delete :name?', array('name' => $record_name));
	}

	/**
	 * Returns the confirmation question element.
	 *
	 * @return \Brickrouge\Element
	 */
	protected function get_question_element()
	{
		return new Element('p', array(Element::INNER_HTML => $this->question));
	}

	/**
	 * Renders a preview of the record.
	 *
	 * @param \ICanBoogie\ActiveRecord $record
	 *
	 * @return string
	 */
	protected function render_preview(\ICanBoogie\ActiveRecord $record)
	{

	}

	/**
	 * Returns a preview of the record.
	 *
	 * @return string
	 */
	protected function get_preview()
	{
		return $this->render_preview($this->record);
	}

	/**
	 * Returns the preview element.
	 *
	 * @return \Brickrouge\Element
	 */
	protected function get_preview_element()
	{
		return $this->preview ? new Element('div', array(Element::INNER_HTML => $this->preview, 'class' => 'preview')) : null;
	}

	/**
	 * Renders the dependencies of the record.
	 *
	 * @param array $dependencies
	 *
	 * @return string
	 */
	protected function render_dependencies(array $dependencies)
	{
		$html = null;

		foreach ($dependencies as $module_id => $by_module)
		{
			$html .= '<li>';
			$html .= '<strong>' . I18n\t(count($by_module) == 1 ? 'one' : 'other', array(), array('scope' => "$module_id.name")) . '</strong>';
			$html .= '<ul>';

			foreach ($by_module as $key => $dependency)
			{
				$html .= '<li><a href="' . $dependency['edit_url'] . '">' . $dependency['title'] . '</a></li>';
			}

			$html .= '</ul>';
			$html .= '</li>';
		}

		if (!$html)
		{
			return null;
		}

		$p = I18n\t('The following dependencies were found, they will also be deleted:');

		return <<<EOT
<p>$p</p>
<ul>$html</ul>
EOT;
	}

	/**
	 * Returns the dependencies of the record.
	 *
	 * @return string
	 */
	protected function get_dependencies()
	{
		$record = $this->record;
		$dependencies = array();

		new \ICanBoogie\ActiveRecord\CollectDependenciesEvent($record, $dependencies);

		return $this->render_dependencies($dependencies);
	}

	/**
	 * Returns the dependencies element.
	 *
	 * @return \Brickrouge\Element
	 */
	protected function get_dependencies_element()
	{
		return $this->dependencies ? new Element('div', array(Element::INNER_HTML => $this->dependencies, 'class' => 'dependencies')) : null;
	}

	/**
	 * Decorate the form as a block with a title, question and possible preview.
	 *
	 * Because at this level the method has no way of knowing the name of the record, it uses
	 * the localized string "record_name" which defaults to "this record".
	 */
	protected function decorate($html)
	{
		return '<div class="block-alert block--delete">' . $html . '</div>';
	}
}

/*
 * Events
 */

namespace ICanBoogie\ActiveRecord;

use ICanBoogie\Route;

/**
 * Event class for the `ICanBoogie\ActiveRecord::collect_dependencies` event.
 */
class CollectDependenciesEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the dependencies.
	 *
	 * @var array[string]\ICanBoogie\ActiveRecord
	 */
	public $dependencies;

	/**
	 * The event is constructed with the type 'collect_dependencies'.
	 *
	 * @param \ICanBoogie\ActiveRecord $target
	 * @param array $dependencies
	 */
	public function __construct(\ICanBoogie\ActiveRecord $target, array &$dependencies)
	{
		$this->dependencies = &$dependencies;

		parent::__construct($target, 'collect_dependencies');
	}

	/**
	 * Adds a dependency.
	 *
	 * @param string $module_id Identifier of the module managin the dependency.
	 * @param int $key Identifier of the dependency.
	 * @param string $title Title of the dependency.
	 * @param string|true|null $edit_url The URL where the dependency can be edited. If `true`
	 * the URL if automatically generated using the following pattern:
	 * `/admin/:module_id/:key/edit`.
	 * @param string|null $view_url The URL on the website where the dependency can be viewed.
	 */
	public function add($module_id, $key, $title, $edit_url=null, $view_url=null)
	{
		if ($edit_url === true)
		{
			$edit_url = \ICanBoogie\Routing\contextualize("/admin/$module_id/$key/edit");
		}

		$this->dependencies[$module_id][$key] = array('title' => $title, 'edit_url' => $edit_url, 'view_url' => $view_url);
	}
}