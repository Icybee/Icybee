<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Widget;

use Brickrouge\Element;

class PopNode extends \Brickrouge\Widget
{
	const T_CONSTRUCTOR = '#popnode-constructor';
	const T_PLACEHOLDER = '#popnode-placeholder';

	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'a', $attributes + array
			(
				self::T_CONSTRUCTOR => 'nodes',
				self::T_PLACEHOLDER => 'Sélectionner un enregistrement',

				'class' => 'spinner',
				'data-adjust' => 'adjust-node',
				'href' => 'javascript:void()',
				'type' => 'button'
			)
		);
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('pop-node.css');
		$document->js->add('pop-node.js');
	}

	protected function render_dataset(array $dataset)
	{
		$dataset['constructor'] = $this[self::T_CONSTRUCTOR];
		$dataset['placeholder'] = $this[self::T_PLACEHOLDER];

		return parent::render_dataset($dataset);
	}

	protected function render_inner_html()
	{
		global $core;

		$rc = parent::render_inner_html();

		$constructor = $this[self::T_CONSTRUCTOR];
		$value = $this['value'] ?: $this[self::DEFAULT_VALUE];
		$record = null;

		if ($value)
		{
			$model = $core->models[$constructor];

			try
			{
				$record = is_numeric($value) ? $model[$value] : $this->getEntry($model, $value);
			}
			catch (\Exception $e)
			{
				wd_log_error('PopNode: Missing record %nid', array('%nid' => $value));
			}
		}

		if (!$record)
		{
			$this->add_class('placeholder');
			$value = null;
		}

		$rc .= new Element('input', array('type' => 'hidden', 'name' => $this['name'], 'value' => $value));

		$rc .= $this->getPreview($record);

		return $rc;
	}

	protected function getEntry($model, $value)
	{
		return $model->where('title = ? OR slug = ?', $value, $value)->order('created DESC')->one;
	}

	protected function getPreview($entry)
	{
		$title = $this->get(self::T_PLACEHOLDER);

		if (!$entry)
		{
			return '<span class="title"><em>' . wd_entities($title) . '</em></span>';
		}

		$value = $entry->nid;
		$title = $entry->title;

		$label = wd_shorten($title, 32, .75, $shortened);

		$rc  = '<span class="title"' . ($shortened ? ' title="' . wd_entities($title) . '"' : '') . '>';
		$rc .= wd_entities($label) . '</span>';

		return $rc;
	}
}