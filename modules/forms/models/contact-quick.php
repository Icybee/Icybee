<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge;

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
					Form::RENDERER => 'Simple',

					Element::CHILDREN => array
					(
						'email' => new Text
						(
							array
							(
								Element::LABEL => 'Votre e-mail',
								Element::REQUIRED => true,
								Element::VALIDATOR => array('Brickrouge\Form::validate_email')
							)
						),

						'message' => new Element
						(
							'textarea', array
							(
								Form::LABEL_MISSING => 'Message',
								Element::REQUIRED => true
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
			Element::CHILDREN => array
			(
				'config[destination]' => new Text
				(
					array
					(
						Form::LABEL => 'Addresse de destination',
						Element::GROUP => 'config',
						Element::DEFAULT_VALUE => $email
					)
				),

				'config' => new \WdEMailNotifyElement
				(
					array
					(
						Form::LABEL => 'Paramètres du message électronique',
						Element::GROUP => 'config',
						Element::DEFAULT_VALUE => array
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