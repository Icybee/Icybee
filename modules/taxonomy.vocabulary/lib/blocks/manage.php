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

use ICanBoogie\I18n;

class ManageBlock extends \Icybee\ManageBlock
{
	public function __construct($module, array $attributes=array())
	{
		parent::__construct
		(
			$module, $attributes += array
			(
				self::T_KEY => 'vid'
			)
		);
	}

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
			Vocabulary::VOCABULARY => array
			(
				'title' => 'Vocabulary'
			),

			Vocabulary::SCOPE => array
			(
				'title' => 'Portée',
				'orderable' => false
			)
		));
	}

	protected function render_cell_vocabulary($record, $tag)
	{
		global $core;

		$vid = $record->vid;
		$terms = $core->models['taxonomy.terms']->select('term')->filter_by_vid($vid)->order('term.weight, term')->all(\PDO::FETCH_COLUMN);

		if ($terms)
		{
			$last = array_pop($terms);

			$includes = $terms
				? I18n\t('Comprenant&nbsp;: !list et !last', array('!list' => \ICanBoogie\shorten(implode(', ', $terms), 128, 1), '!last' => $last))
				: I18n\t('Comprenant&nbsp;: !entry', array('!entry' => $last));
		}
		else
		{
			$includes = '<em>La liste est vide</em>';
		}

		$context = $core->site->path;

		return parent::modify_code($record->vocabulary, $vid, $this) . <<<EOT
<span class="small"> &ndash; <a href="$context/admin/{$this->module}/$vid/order">Ordonner les termes du vocabulaire</a></span>
<br />
<span class="small">$includes</span>
EOT;
	}

	protected function render_cell_scope($record, $tag)
	{
		global $core;

		$scope = $this->module->model('scopes')
		->select('constructor')->where('vid = ?', $record->vid)->all(\PDO::FETCH_COLUMN);

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