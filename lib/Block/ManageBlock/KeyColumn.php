<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block\ManageBlock;

use Brickrouge\Document;
use Brickrouge\Element;

use Icybee\Modules\Users\User;
use Icybee\Element\WrappedCheckbox;

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

	public function alter_records(array &$records)
	{
		/* @var $user User */

		$key = $this->id;
		$user = \ICanBoogie\app()->user;
		$ownership = [];

		foreach ($records as $record)
		{
			$ownership[$record->$key] = $user->has_ownership($record);
		}

		$this->ownership = $ownership;

		parent::alter_records($records);
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

	public function add_assets(Document $document)
	{
		$document->js->add(__DIR__ . '/KeyColumn.js');
		$document->css->add(__DIR__ . '/KeyColumn.css');
	}
}
