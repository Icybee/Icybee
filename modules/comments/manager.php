<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Comments;

use ICanBoogie\ActiveRecord\Comment;
use ICanBoogie\ActiveRecord\Query;

use Brickrouge\Document;
use Brickrouge\Element;

class Manager extends \WdManager
{
	public function __construct($module, array $tags=array())
	{
		parent::__construct
		(
			$module, $tags + array
			(
				self::T_KEY => 'commentid'
			)
		);
	}

	protected static function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add('public/admin.css');
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
				'orderable' => false,
				'filters' => array
				(
					'options' => array
					(
						'=approved' => "Approved",
						'=pending' => "Pending",
						'=spam' => "Spam"
					)
				),
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
		$rc  = $this->render_cell_url($record);

		$rc .= '<span class="contents">';
		$rc .= parent::modify_code(strip_tags($record->excerpt(24)), $record->commentid, $this);
		$rc .= '</span><br />';

		return $rc;
	}

	protected function render_cell_status($record, $property)
	{
		return $this->render_filter_cell($record, $property, $this->t->__invoke($record->$property), array('scope' => '.status'));
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
			$rc .= '<img src="' . wd_entities($record->author_icon . '&s=32') . '" alt="' . wd_entities($record->author) . '" width="32" height="32" />';
		}

		$rc .= '<div class="details">';

		$rc .= $this->render_filter_cell($record, $property);

		$email = $record->author_email;

		if ($email)
		{
			$rc .= '<br /><span class="small">';
			$rc .= '<a href="mailto:' . wd_entities($email) . '">' . wd_entities($email) . '</a>';
			$rc .= '</span>';
		}

		$url = $record->author_url;

		if ($url)
		{
			$rc .= '<br /><span class="small">';
			$rc .= '<a href="' . wd_entities($url) . '" target="_blank">' . wd_entities($url) . '</a>';
			$rc .= '</span>';
		}

		$rc .= '</div>';

		return $rc;
	}

	protected function render_cell_score($record)
	{
		return Module::score_spam($record->contents, $record->author_url, $record->author);
	}

	protected function render_cell_nid($record, $property)
	{
		$node = $record->node;

		$rc = '';

		if ($node)
		{
			$title = $node->title;
			$label = wd_entities(wd_shorten($title, 48, .75, $shortened));

			$rc .= new Element
			(
				'a', array
				(
					Element::INNER_HTML => 'Aller à l\'article',

					'href' => $node->url,
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