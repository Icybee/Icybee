<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge;

class quick_contact_WdForm extends Form
{
	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			wd_array_merge_recursive
			(
				$tags, array
				(
					self::T_RENDERER => 'Simple',

					Element::T_CHILDREN => array
					(
						'email' => new Text
						(
							array
							(
								Element::T_LABEL => 'Votre e-mail',
								Element::T_REQUIRED => true,
								Element::T_VALIDATOR => array('BrickRouge\Form::validate_email')
							)
						),

						'message' => new Element
						(
							'textarea', array
							(
								Form::T_LABEL_MISSING => 'Message',
								Element::T_REQUIRED => true
							)
						)
					),

					'class' => 'stacked'
				)
			)
		);
	}

	static public function getConfig()
	{
		global $core;

		$email = $core->user->email;

		return array
		(
			Element::T_CHILDREN => array
			(
				'config[destination]' => new Text
				(
					array
					(
						Form::T_LABEL => 'Addresse de destination',
						Element::T_GROUP => 'config',
						Element::T_DEFAULT => $email
					)
				),

				'config' => new \WdEMailNotifyElement
				(
					array
					(
						Form::T_LABEL => 'Paramètres du message électronique',
						Element::T_GROUP => 'config',
						Element::T_DEFAULT => array
						(
							'from' => 'Contact <no-reply@wdpublisher.com>',
							'subject' => 'Formulaire de contact',
							'template' => <<<EOT
Un message a été posté depuis le formulaire de contact :

E-Mail : #{@email}

#{@message}
EOT
						)
					)
				)
			)
		);
	}
}