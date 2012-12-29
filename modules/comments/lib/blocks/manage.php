<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Comments;

use ICanBoogie\ActiveRecord\Query;

use Brickrouge\A;
use Brickrouge\Document;
use Brickrouge\DropdownMenu;
use Brickrouge\Element;

class ManageBlock extends \WdManager
{
	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add('../../public/admin.css');
		$document->js->add('../../public/admin.js');
	}

	public function __construct($module, array $attributes=array())
	{
		parent::__construct
		(
			$module, $attributes + array
			(
				self::T_KEY => 'commentid',
				self::T_COLUMNS_ORDER => array
				(
					'comment', 'status', 'author', /*'score',*/ 'nid', 'created'
				),

				self::T_ORDER_BY => array('created', 'desc'),
			)
		);
	}

	protected function columns()
	{
		return parent::columns() + array
		(
			'comment' => array
			(
				'orderable' => false
			),

			'status' => array
			(
				'filters' => array
				(
					'options' => array
					(
						'=approved' => "Approved",
						'=pending' => "Pending",
						'=spam' => "Spam"
					)
				),

				'orderable' => false,

				'label' => 'Status',
				'class' => 'pull-right'
			),

			'score' => array
			(
				'class' => 'score',
				'orderable' => false
			),

			Comment::AUTHOR => array
			(
				'class' => 'author'
			),

			Comment::NID => array
			(
				'orderable' => false
			),

			Comment::CREATED => array
			(
				'class' => 'date'
			)
		);
	}

	/**
	 * Update filters with the `status` modifier.
	 *
	 * @see Icybee.Manager::update_filters()
	 */
	protected function update_filters(array $filters, array $modifiers)
	{
		$filters = parent::update_filters($filters, $modifiers);

		if (isset($modifiers['status']))
		{
			$value = $modifiers['status'];

			if (in_array($value, array('approved', 'pending', 'spam')))
			{
				$filters['status'] = $value;
			}
			else if (!$value)
			{
				unset($filters['status']);
			}
		}

		return $filters;
	}

	protected function alter_query(Query $query, array $filters)
	{
		global $core;

		$query = parent::alter_query($query, $filters);

		$query->where('(SELECT 1 FROM {prefix}nodes WHERE nid = comment.nid AND (siteid = 0 OR siteid = ?)) IS NOT NULL', $core->site_id);

		return $query;
	}

	protected function render_cell_comment($record, $property)
	{
		$url = $this->render_cell_url($record);
		$modify = parent::modify_code(\ICanBoogie\shorten(strip_tags($record), 48, 1), $record->commentid, $this);

		return $url . $modify;
	}

	protected function render_cell_status($record, $property)
	{
		static $labels = array
		(
			Comment::STATUS_APPROVED => 'Approved',
			Comment::STATUS_PENDING => 'Pending',
			Comment::STATUS_SPAM => 'Spam'
		);

		static $classes = array
		(
			Comment::STATUS_APPROVED => 'btn-success',
			Comment::STATUS_PENDING => 'btn-warning',
			Comment::STATUS_SPAM => 'btn-danger'
		);

		$status = $record->status;
		$status_label = isset($labels[$status]) ? $labels[$status] : "<em>Invalid status code: $status</em>";
		$status_class = isset($classes[$status]) ? $classes[$status] : 'btn-danger';
		$commentid = $record->commentid;

		$menu = new DropdownMenu
		(
			array
			(
				DropdownMenu::OPTIONS => $labels,

				'value' => $status
			)
		);

		$classes_json = \Brickrouge\escape(json_encode($classes));

		return <<<EOT
<div class="btn-group" data-property="status" data-key="$commentid" data-classes="$classes_json">
	<span class="btn $status_class dropdown-toggle" data-toggle="dropdown"><span class="text">$status_label</span> <span class="caret"></span></span>
    $menu
</div>
EOT;
	}

	protected function render_cell_url($record)
	{
		return new Element
		(
			'a', array
			(
				Element::INNER_HTML => 'Voir le commentaire',

				'href' => $record->url,
				'class' => 'view'
			)
		);
	}

	protected $last_rendered_author;

	protected function render_cell_author($record, $property)
	{
		if ($this->last_rendered_author == $record->author_email)
		{
			return self::REPEAT_PLACEHOLDER;
		}

		$this->last_rendered_author = $record->author_email;

		$rc = '';

		if ($record->author_email)
		{
			$rc .= new Element
			(
				'img', array
				(
					'src' => $record->author_icon . '&s=32',
					'alt' => $record->author,
					'width' => 32,
					'height' => 32
				)
			);
		}

		$rc .= '<div class="details">';

		$rc .= $this->render_filter_cell($record, $property);

		$email = $record->author_email;

		if ($email)
		{
			$rc .= ' <span class="small">&lt;';
			$rc .= new A($email, 'mailto:' . $email);
			$rc .= '&gt;</span>';
		}

		$url = $record->author_url;

		if ($url)
		{
			$rc .= '<br /><span class="small">';
			$rc .= new A($url, $url, array('target' => '_blank'));
			$rc .= '</span>';
		}

		$rc .= '</div>';

		return $rc;
	}

	protected function render_cell_nid($record, $property)
	{
		$node = $record->node;

		$rc = '';

		if ($node)
		{
			$title = $node->title;
			$label = \ICanBoogie\escape(\ICanBoogie\shorten($title, 48, .75, $shortened));

			$rc .= new A
			(
				"View record", $node->url, array
				(
					'title' => $title,
					'class' => 'view'
				)
			);
		}
		else
		{
			$label = '<em class="warn">unknown-node-' . $record->$property . '</em>';
		}

		return $rc . $this->render_filter_cell($record, $property, $label);
	}

	protected function render_cell_created($record, $property)
	{
		return $this->render_cell_datetime($record, $property);
	}
}