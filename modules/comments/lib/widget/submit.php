<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\ActiveRecord\Comment;
use ICanBoogie\Operation;
use BrickRouge\Element;
use BrickRouge\Form;

class feedback_comments_WdForm extends Form
{
	public function __construct($tags, $dummy=null)
	{
		global $core;

		$user = $core->user;
		$is_member = !$user->is_guest;
		$values = array();

		if ($is_member)
		{
			$values[Comment::AUTHOR] = $user->name;
			$values[Comment::AUTHOR_EMAIL] = $user->email;
		}

		parent::__construct
		(
			wd_array_merge_recursive
			(
				$tags, array
				(
					Form::T_RENDERER => 'Simple',
					Form::T_VALUES => $values,
					Form::T_HIDDENS => array
					(
						Operation::DESTINATION => 'comments',
						Operation::NAME => 'save'
					),

					Element::T_CHILDREN => array
					(
						Comment::AUTHOR => new Element
						(
							Element::E_TEXT, array
							(
								Element::T_LABEL => 'Name',
								Element::T_REQUIRED => true,
// 								'readonly' => $is_member
							)
						),

						Comment::AUTHOR_EMAIL => new Element
						(
							Element::E_TEXT, array
							(
								Element::T_LABEL => 'E-mail',
								Element::T_REQUIRED => true,
								Element::T_VALIDATOR => array('BrickRouge\Form::validate_email'),
// 								'readonly' => $is_member
							)
						),

						Comment::AUTHOR_URL => new Element
						(
							Element::E_TEXT, array
							(
								Element::T_LABEL => 'Website'
							)
						),

						Comment::CONTENTS => new Element
						(
							'textarea', array
							(
								Element::T_REQUIRED => true,
								Element::T_LABEL_MISSING => 'Message',
								'rows' => 8
							)
						),

						Comment::NOTIFY => new Element
						(
							Element::E_RADIO_GROUP, array
							(
								Element::T_OPTIONS => array
								(
									'yes' => "Bien sûr !",
									'author' => "Seulement si c'est l'auteur du billet qui répond.",
									'no' => "Pas la peine, je viens tous les jours."
								),

								Element::T_DEFAULT => 'no',
								Element::T_LABEL => "Shouhaitez-vous être informé d'une réponse à votre message&nbsp;?",
								Element::T_LABEL_POSITION => 'above',
								Element::T_LABEL_SEPARATOR => false,

								'class' => 'inputs-list'
							)
						)
					),

					'action' => '#view-comments-submit',
					'class' => 'stacked'
				)
			),

			'div'
		);
	}

	public function __toString()
	{
		global $core;

		$core->document->js->add('submit.js');

		return parent::__toString();
	}

	public function alter_notify($properties)
	{
		global $core;

		$comment = $core->models['comments'][$properties->rc['key']];

		#
		# if the comment is approved we change the location to the comment location, otherwise
		# the location is changed to the location of the form element.
		#

// 		$operation->location = ($comment->status == 'approved') ? $comment->url : $_SERVER['REQUEST_URI'] . '#' . $operation->record->slug;
		$properties->bind = $comment;
	}

	static public function get_defaults()
	{
		global $core;

		if (isset($_GET['type']) && $_GET['type'] == 'notify')
		{
			return array
			(
				'from' => 'no-reply@' . $_SERVER['HTTP_HOST'],
				'subject' => 'Notification de réponse au billet : #{@node.title}',
				'bcc' => $core->user->email,
				'template' => <<<EOT
Bonjour,

Vous recevez cet e-mail parce que vous surveillez le billet "#{@node.title}" sur #{\$core.site.title}.
Ce billet a reçu une réponse depuis votre dernière visite. Vous pouvez utiliser le lien suivant
pour voir les réponses qui ont été faites :

#{@absolute_url}

Aucune autre notification ne vous sera envoyée.

À bientôt sur #{\$core.site.title}.
EOT
			);
		}

		return array
		(
			'notify_subject' => 'Un nouveau commentaire a été posté sur #{$core.site.title}',
			'notify_from' => 'Comments <comments@#{$server.http.host}>',
			'notify_template' => <<<EOT
Bonjour,

Vous recevez ce message parce qu'un nouveau commentaire a été posté sur le site #{\$core.site.title} :

URL : #{@absolute_url}
Auteur : #{@author} <#{@author_email}>

#{@strip_tags()=}

EOT
		);
	}
}