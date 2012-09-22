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

use Icybee\Modules\Comments\Comment;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

/**
 * A block to edit comments.
 */
class EditBlock extends \Icybee\EditBlock
{
	protected function get_children()
	{
		$values = $this->values;

		return array
		(
			Comment::AUTHOR => new Text
			(
				array
				(
					Form::LABEL => 'Author',
					Element::REQUIRED => true
				)
			),

			Comment::AUTHOR_EMAIL => new Text
			(
				array
				(
					Form::LABEL => 'E-mail',
					Element::REQUIRED => true
				)
			),

			Comment::AUTHOR_URL => new Text
			(
				array
				(
					Form::LABEL => 'URL'
				)
			),

			Comment::AUTHOR_IP => new Text
			(
				array
				(
					Form::LABEL => 'Adresse IP',
					Element::DESCRIPTION => "Status spam: <em>en cours de vérification</em>.",

					'disabled' => true
				)
			),

			Comment::CONTENTS => new Element
			(
				'textarea', array
				(
					Form::LABEL => 'Message',
					Element::REQUIRED => true,

					'rows' => 10
				)
			),

			Comment::NOTIFY => new Element
			(
				Element::TYPE_RADIO_GROUP, array
				(
					Form::LABEL => 'Notification',
					Element::DEFAULT_VALUE => 'no',
					Element::REQUIRED => true,
					Element::OPTIONS => array
					(
						'yes' => 'Bien sûr !',
						'author' => "Seulement si c'est l'auteur du billet qui répond",
						'no' => 'Pas la peine, je viens tous les jours',
						'done' => 'Notification envoyée'
					),

					Element::DESCRIPTION => (($values[Comment::NOTIFY] == 'done') ? "Un
					message de notification a été envoyé." : null),

					'class' => 'inputs-list'
				)
			),

			Comment::STATUS => new Element
			(
				'select', array
				(
					Form::LABEL => 'Status',
					Element::REQUIRED => true,
					Element::OPTIONS => array
					(
						null => '',
						'pending' => 'Pending',
						'approved' => 'Aprouvé',
						'spam' => 'Spam'
					)
				)
			)
		);
	}
}