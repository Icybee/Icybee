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

/**
 * Representation of a _primary key_ column.
 */
class KeyColumn extends Column
{
	private $ownership;

	public function __construct($manager, $id, array $options=array())
	{
		parent::__construct
		(
			$manager, $id, array
			(
				'title' => null,
				'class' => 'cell-key'
			)
		);
	}

	public function alter_records(array $records)
	{
		global $core;

		$key = $this->id;
		$module = $this->manager->module;
		$user = $core->user;
		$ownership = array();

		foreach ($records as $record)
		{
			$ownership[$record->$key] = $user->has_ownership($module, $record);
		}

		$this->ownership = $ownership;

		return parent::alter_records($records);
	}

	public function render_cell($record)
	{
		global $core;

		$key = $this->id;

		return new Element
		(
			'label', array
			(
				Element::CHILDREN => array
				(
					new Element
					(
						Element::TYPE_CHECKBOX, array
						(
							'value' => $record->$key,
							'disabled' => !$this->ownership[$record->$key]
						)
					)
				),

				'class' => 'checkbox-wrapper rectangle',
				'title' => $this->t('Toggle selection for record #:key', array('key' => $key))
			)
		);
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

		return new Element
		(
			'label', array
			(
				Element::CHILDREN => array
				(
					new Element
					(
						Element::TYPE_CHECKBOX
					)
				),

				'class' => 'checkbox-wrapper rectangle',
				'title' => $this->t('Toggle selection for the entries ([alt] to toggle selection)')
			)
		);
	}

	public function add_assets(\BrickRouge\Document $document)
	{
		$document->js->add('key.column.js');
		$document->css->add('key.column.css');
	}
}