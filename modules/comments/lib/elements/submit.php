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

use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

/**
 * The form used to submit comments.
 */
class SubmitForm extends Form
{
	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('submit.js');
	}

	public function __construct(array $attributes=array())
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
			\ICanBoogie\array_merge_recursive
			(
				$attributes, array
				(
					Form::RENDERER => 'Simple',
					Form::VALUES => $values,
					Form::HIDDENS => array
					(
						Operation::DESTINATION => 'comments',
						Operation::NAME => 'save'
					),

					Element::CHILDREN => array
					(
						Comment::AUTHOR => new Text
						(
							array
							(
								Element::LABEL => 'Name',
								Element::REQUIRED => true
							)
						),

						Comment::AUTHOR_EMAIL => new Text
						(
							array
							(
								Element::LABEL => 'E-mail',
								Element::REQUIRED => true,
								Element::VALIDATOR => array('Brickrouge\Form::validate_email')
							)
						),

						Comment::AUTHOR_URL => new Text
						(
							array
							(
								Element::LABEL => 'Website'
							)
						),

						Comment::CONTENTS => new Element
						(
							'textarea', array
							(
								Element::REQUIRED => true,
								Element::LABEL_MISSING => 'Message',

								'class' => 'span6',
								'rows' => 8
							)
						),

						Comment::NOTIFY => new Element
						(
							Element::TYPE_RADIO_GROUP, array
							(
								Form::LABEL => "Shouhaitez-vous être informé d'une réponse à votre message ?",

								Element::OPTIONS => array
								(
									'yes' => "Bien sûr !",
									'author' => "Seulement si c'est l'auteur du billet qui répond.",
									'no' => "Pas la peine, je viens tous les jours."
								),

								Element::DEFAULT_VALUE => 'no',

								'class' => 'inputs-list'
							)
						)
					),

					Element::WIDGET_CONSTRUCTOR => 'SubmitComment',

					'action' => '#view-comments-submit',
					'class' => 'widget-submit-comment'
				)
			),

			'div'
		);
	}

	public function alter_notify(\Icybee\Modules\Forms\NotifyParams $properties)
	{
		global $core;

		$properties->bind = $core->models['comments'][$properties->rc['key']];
	}

	static public function get_defaults()
	{
		global $core;

		if (isset($_GET['type']) && $_GET['type'] == 'notify')
		{
			return array
			(
				'from' => 'no-reply@' . $_SERVER['SERVER_NAME'],
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