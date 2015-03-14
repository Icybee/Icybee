<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\ManageBlock;

use Brickrouge\Element;

use Icybee\WrappedCheckbox;

/**
 * Representation of a _primary key_ column.
 */
class KeyColumn extends Column
{
	private $ownership;

	public function __construct($manager, $id, array $options = [])
	{
		parent::__construct($manager, $id, $options + [

			'title' => null,
			'class' => 'cell-key'

		]);
	}

	public function alter_records(array $records)
	{
		$key = $this->id;
		$module = $this->manager->module;
		$user = \ICanBoogie\app()->user;
		$ownership = [];

		foreach ($records as $record)
		{
			$ownership[$record->$key] = $user->has_ownership($module, $record);
		}

		$this->ownership = $ownership;

		return parent::alter_records($records);
	}

	public function render_cell($record)
	{
		$key = $record->{ $this->id };

		return new WrappedCheckbox([

			'value' => $key,
			'disabled' => !$this->ownership[$key],
			'title' => $this->t('Toggle selection for the record #:key', [ ':key' => $key ])
		]);
	}

	/**
	 * Renders a _master_ checkbox.
	 *
	 * If the user as no ownership of any of the records a non-breakable space is returned instead.
	 *
	 * @return Element|string
	 */
	public function render_header()
	{
		if (!count($this->ownership))
		{
			return '&nbsp;';
		}

		return new WrappedCheckbox([

			'class' => 'wrapped-checkbox rectangle',
			'title' => $this->t('Toggle selection for the records ([alt] to toggle selection)')

		]);
	}

	public function add_assets(\BrickRouge\Document $document)
	{
		$document->js->add('key.column.js');
		$document->css->add('key.column.css');
	}
}
