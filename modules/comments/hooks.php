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

use ICanBoogie;
use ICanBoogie\ActiveRecord\Node;
use ICanBoogie\ActiveRecord\Comment;
use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Hooks
{
	public static function before_node_save(Event $event, \ICanBoogie\Modules\Nodes\SaveOperation $sender)
	{
		$request = $event->request;

		if (isset($request['metas']['comments/reply']))
		{
			$metas = &$request->params['metas']['comments/reply'];

			$metas += array
			(
				'is_notify' => null
			);

			$metas['is_notify'] = filter_var($metas['is_notify'], FILTER_VALIDATE_BOOLEAN);
		}
	}

	/**
	 * Deletes all the comments attached to a node.
	 *
	 * @param Event $event
	 * @param ICanBoogie\Modules\Nodes\DeleteOperation $sender
	 */
	public static function on_node_delete(Event $event, \ICanBoogie\Modules\Nodes\DeleteOperation $sender)
	{
		global $core;

		try
		{
			$model = $core->models['comments'];
		}
		catch (\Exception $e)
		{
			return;
		}

		$ids = $model->select('{primary}')->where('nid = ?', $sender->key)->all(\PDO::FETCH_COLUMN);

		foreach ($ids as $commentid)
		{
			$model->delete($commentid);
		}
	}

	public static function alter_block_edit(Event $event)
	{
		global $core;

		if (!isset($core->modules['comments']))
		{
			return;
		}

		$values = null;
		$key = 'comments/reply';
		$metas_prefix = 'metas[' . $key . ']';

		if ($event->entry)
		{
			$entry = $event->entry;

			$values = array
			(
				$metas_prefix => unserialize($entry->metas[$key])
			);
		}

		$ns = wd_entities($metas_prefix);

		$event->tags = \ICanBoogie\array_merge_recursive
		(
			$event->tags, array
			(
				Form::VALUES => $values ? $values : array(),

				Element::CHILDREN => array
				(
					$key => new Element\Templated
					(
						'div', array
						(
							Element::GROUP => 'notify',
							Element::CHILDREN => array
							(
								$metas_prefix . '[is_notify]' => new Element
								(
									Element::TYPE_CHECKBOX, array
									(
										Element::LABEL => 'Activer la notification aux réponses',
										Element::DESCRIPTION => "Cette option déclanche l'envoi
										d'un email à l'auteur ayant choisi d'être informé d'une
										réponse à son commentaire."
									)
								),

								$metas_prefix . '[from]' => new Text
								(
									array
									(
										Form::LABEL => 'Adresse d\'expédition'
									)
								),

								$metas_prefix . '[bcc]' => new Text
								(
									array
									(
										Form::LABEL => 'Copie cachée'
									)
								),

								$metas_prefix . '[subject]' => new Text
								(
									array
									(
										Form::LABEL => 'Sujet du message'
									)
								),

								$metas_prefix . '[template]' => new Element
								(
									'textarea', array
									(
										Form::LABEL => 'Patron du message',
										Element::DESCRIPTION => "Le sujet du message et le corps du message
										sont formatés par <a href=\"http://github.com/Weirdog/WdPatron\" target=\"_blank\">WdPatron</a>,
										utilisez ses fonctionnalités avancées pour les personnaliser."
									)
								)
							)
						),

						<<<EOT
<div class="panel">
<div class="form-element is_notify">{\${$metas_prefix}[is_notify]}</div>
<table>
<tr><td class="label">{\${$metas_prefix}[from].label:}</td><td>{\${$metas_prefix}[from]}</td>
<td class="label">{\${$metas_prefix}[bcc].label:}</td><td>{\${$metas_prefix}[bcc]}</td></tr>
<tr><td class="label">{\${$metas_prefix}[subject].label:}</td><td colspan="3">{\${$metas_prefix}[subject]}</td></tr>
<tr><td colspan="4">{\${$metas_prefix}[template]}<button type="button" class="reset small warn" value="/api/forms/feedback.comments/defaults?type=notify" data-ns="$ns">Valeurs par défaut</button></td></tr>
</table>
</div>
EOT
					)
				)
			)
		);
	}

	public static function get_comments(Node $ar)
	{
		global $core;

		return $core->models['comments']->where('nid = ? AND status = "approved"', $ar->nid)->order('created')->all;
	}

	public static function get_comments_count(Node $ar)
	{
		global $core;

		return $core->models['comments']->where('nid = ? AND status = "approved"', $ar->nid)->count;
	}

	public static function get_rendered_comments_count(Node $ar)
	{
		return t(':count comments', array(':count' => $ar->comments_count));
	}

	public static function dashboard_last()
	{
		global $core, $document;

		if (empty($core->modules['comments']))
		{
			return;
		}

		$document->css->add('public/admin.css');

		$entries = $core->models['comments']
		->where('(SELECT 1 FROM {prefix}nodes WHERE nid = comment.nid AND (siteid = 0 OR siteid = ?)) IS NOT NULL', $core->site_id)
		->order('created DESC')->limit(5)->all;

		if (!$entries)
		{
			return '<p class="nothing">' . t('No record yet') . '</p>';
		}

		$rc = '';
		$context = $core->site->path;

		foreach ($entries as $entry)
		{
			$url = $entry->url;
			$author = wd_entities($entry->author);

			if ($entry->author_url)
			{
				$author = '<a class="author" href="' . wd_entities($entry->author_url) . '">' . $author . '</a>';
			}
			else
			{
				$author = '<strong class="author">' . $author . '</strong>';
			}

			$excerpt = wd_shorten(strip_tags((string) html_entity_decode($entry, ENT_COMPAT, ICanBoogie\CHARSET)), 140);

			$target_url = $entry->node->url;
			$target_title = wd_entities(wd_shorten($entry->node->title));

			$image = wd_entities($entry->author_icon);

			$entry_class = $entry->status == 'spam' ? 'spam' : '';
			$url_edit = "$context/admin/comments/$entry->commentid/edit";
			$url_delete = "$context/admin/comments/$entry->commentid/delete";

			$date = wd_format_date($entry->created, 'dd MMM');

			$rc .= <<<EOT
<div class="record $entry_class">
	<div class="options">
		<img src="$image&amp;s=48" alt="" />

		<!--span class="more-auto small">
			<a href="$url_edit">Éditer</a>,
			<a href="#delete" class="danger">Supprimer</a>,
			<a href="#spam" class="warn">Spam</a>
		</span-->
	</div>

	<div class="contents">
		<div class="head">
		$author
		<span class="date light">$date</span>
		</div>

		<div class="body"><a href="$url">$excerpt</a></div>

		<div class="actions light">
			<a href="$url_edit">Éditer</a>, <a href="$url_delete" class="danger">Supprimer</a> − <a href="$target_url" class="target" title="Afficher le nœud associé">$target_title</a>
		</div>
	</div>
</div>
EOT;
		}

		$rc .= <<<EOT
<div class="go-to-list"><a href="$context/admin/comments">Tous les commentaires</a></div>
EOT;

		return $rc;
	}

	/*
	 * MARKUPS
	 */

	static public function comments(array $args, \WdPatron $patron, $template)
	{
		global $core;

		if (array_key_exists('by', $args))
		{
			throw new Exception('"by" is no longer supported, use "order": \1', array($args));
		}

		extract($args);

		#
		# build sql query
		#

		$arr = $core->models['comments']->where('status = "approved"');

		if ($node)
		{
			$arr->where(array('nid' => $node));
		}

		if ($noauthor)
		{
			$arr->where('(SELECT uid FROM {prefix}nodes WHERE nid = comment.nid) != IFNULL(uid, 0)');
		}

		if ($order)
		{
			$arr->order($order);
		}

		if ($limit)
		{
			$arr->limit($limit * $page, $limit);
		}

		$entries = $arr->all;

		if (!$entries && !$parseempty)
		{
			return;
		}

		return $patron($template, $entries);
	}

	static public function form(array $args, \WdPatron $patron, $template)
	{
		global $core, $page;

		#
		# Obtain the form to use to add a comment from the 'forms' module.
		#

		$module = $core->modules['comments'];
		$form_id = $core->site->metas['comments.form_id'];

		if (!$form_id)
		{
			throw new Exception\Config($module);
		}

		if (!$core->user->has_permission(ICanBoogie\Module::PERMISSION_CREATE, 'comments'))
		{
			return new \Brickrouge\AlertMessage
			(
				<<<EOT
You don't have permission the create comments,
<a href="{$core->site->path}/admin/users.roles">the <q>Visitor</q> role should be modified.</a>
EOT
, array(), 'error'
			);
		}

		$form = $core->models['forms'][$form_id];

		if (!$form)
		{
			throw new Exception
			(
				'Uknown form with Id %nid', array
				(
					'%nid' => $form_id
				)
			);
		}

		Event::fire
		(
			'nodes_load', array
			(
				'nodes' => array($form)
			),

			$patron
		);

		#
		# Traget Id for the comment
		#

		$select = $args['select'];

		$nid = is_object($select) ? $select->nid : $select;

		$form->form->hiddens[Comment::NID] = isset($page->node) ? $page->node->nid : $page->nid;
		$form->form->add_class('wd-feedback-comments');

		return $template ? $patron($template, $form) : $form;
	}

	public static function on_view_render(Event $event, \Icybee\Views\View $view)
	{
		if ($event->id != 'articles/view')
		{
			return;
		}

		$list = \view_WdEditorElement::render('comments/list');

		$submit = \view_WdEditorElement::render('comments/submit');

		$event->rc .= PHP_EOL . $list . PHP_EOL . $submit;
	}
}