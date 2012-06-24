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

					'#location' => Route::contextualize("/admin/{$module->id}")
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
				$title = $this->title;
				$message = t('Unknown record id: %key', array('%key' => $this->key));

				return <<<EOT
<div class="block-alert block--delete">
<h2>$title</h2>
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
	 * Returns the localized title of the block.
	 *
	 * @return string
	 */
	protected function get_title()
	{
		return t('Delete a record');
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
	 * Decorate the form as a block with a title, question and possible preview.
	 *
	 * Because at this level the method has no way of knowing the name of the record, it uses
	 * the localized string "record_name" which defaults to "this record".
	 *
	 * @see Brickrouge.Element::decorate()
	 */
	protected function decorate($html)
	{
		$title = \Brickrouge\escape($this->title);

		$record_name = $this->record_name;

		if ($record_name)
		{
			$record_name = '<q>' . $record_name . '</q>';
		}
		else
		{
			$record_name = t('record_name', array(), array('default' => 'this record'));
		}

		$question = t('Are you sure you want to delete :name?', array('name' => $record_name));
		$preview = $this->render_preview($this->record);

		return <<<EOT
<div class="block-alert block--delete">
<h2>$title</h2>
<p>$question</p>
<div class="preview">$preview</div>
$html
</div>
EOT;
	}

	/**
	 * Renders a preview of the record.
	 *
	 * @param ActiveRecord $record
	 *
	 * @return string
	 */
	protected function render_preview(ActiveRecord $record)
	{

	}
}
