<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Taxonomy\Vocabulary;

class ManageBlock extends \Icybee\ManageBlock
{
	/**
	 * Adds the following columns:
	 *
	 * - `vocabulary`
	 * - `scope`
	 */
	protected function get_available_columns()
	{
		return array_merge(parent::get_available_columns(), array
		(
			Vocabulary::VOCABULARY => __CLASS__ . '\VocabularyColumn',
			Vocabulary::SCOPE => __CLASS__ . '\ScopeColumn'
		));
	}
}

namespace Icybee\Modules\Taxonomy\Vocabulary\ManageBlock;

use ICanBoogie\I18n;
use ICanBoogie\Module;

use Icybee\ManageBlock\Column;
use Icybee\ManageBlock\EditDecorator;

/**
 * Representation of the `vocabulary` column.
 */
class VocabularyColumn extends Column
{
	public function render_cell($record)
	{
		global $core;

		$vid = $record->vid;
		$terms = $core->models['taxonomy.terms']
		->select('term')
		->filter_by_vid($vid)
		->order('term.weight, term')
		->all(\PDO::FETCH_COLUMN);

		$order_link = null;

		if ($terms)
		{
			$last = array_pop($terms);

			$includes = $terms
				? I18n\t('Including: !list and !last', array('!list' => \ICanBoogie\shorten(implode(', ', $terms), 128, 1), '!last' => $last))
				: I18n\t('Including: !entry', array('!entry' => $last));

			$order_url = \ICanBoogie\Routing\contextualize("/admin/{$this->manager->module->id}/$vid/order");

			$order_link = <<<EOT
<a href="$order_url">Order the terms</a>
EOT;
		}
		else
		{
			$includes = '<em class="light">The vocabulary is empty</em>';
		}

		if ($order_link)
		{
			$order_link = " &ndash; {$order_link}";
		}

		return new EditDecorator($record->vocabulary, $record) . <<<EOT
<br /><span class="small">{$includes}{$order_link}</span>
EOT;
	}
}

/**
 * Representation of the `scope` column.
 */
class ScopeColumn extends Column
{
	public function render_cell($record)
	{
		global $core;

		$scope = $this->manager->module->model('scopes')
		->select('constructor')
		->where('vid = ?', $record->vid)
		->all(\PDO::FETCH_COLUMN);

		if ($scope)
		{
			$context = $core->site->path;

			foreach ($scope as &$constructor)
			{
				$constructor = '<a href="' . $context . '/admin/' . $constructor . '">' . I18n\t($core->modules->descriptors[$constructor][Module::T_TITLE]) . '</a>';
			}

			$last = array_pop($scope);

			$includes = $scope
				? I18n\t(':list and :last', array(':list' => \ICanBoogie\shorten(implode(', ', $scope), 128, 1), ':last' => $last))
				: I18n\t(':one', array(':one' => $last));
		}
		else
		{
			$includes = '<em>Aucune portée</em>';
		}

		return '<span class="small">' . $includes . '</span>';
	}
}