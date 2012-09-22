<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Forms;

use Brickrouge\Element;
use Brickrouge\Text;

class ContactForm extends \Brickrouge\Form
{
	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			\ICanBoogie\array_merge_recursive
			(
				$attributes, array
				(
					self::RENDERER => 'Simple',

					Element::CHILDREN => array
					(
						'gender' => new Element
						(
							Element::TYPE_RADIO_GROUP, array
							(
								self::LABEL => 'Salutation',
								Element::OPTIONS => array('salutation.Misses', 'salutation.Mister'),
								Element::REQUIRED => true,

								'class' => 'inline-inputs'
							)
						),

						'firstname' => new Text
						(
							array
							(
								self::LABEL => 'Firstname',
								Element::REQUIRED => true
							)
						),

						'lastname' => new Text
						(
							array
							(
								self::LABEL => 'Lastname',
								Element::REQUIRED => true
							)
						),

						'company' => new Text
						(
							array
							(
								self::LABEL => 'Company'
							)
						),

						'email' => new Text
						(
							array
							(
								self::LABEL => 'E-mail',
								Element::REQUIRED => true,
								Element::VALIDATOR => array('Brickrouge\Form::validate_email')
							)
						),

						'message' => new Element
						(
							'textarea', array
							(
								self::LABEL => 'Your message',
								Element::REQUIRED => true
							)
						)
					)
				)
			),

			'div'
		);
	}

	static public function get_defaults()
	{
		global $core;

		return array
		(
			'notify_destination' => $core->user->email,
			'notify_from' => 'Contact <no-reply@' . preg_replace('#^www#', '', $_SERVER['SERVER_NAME']) .'>',
			'notify_subject' => 'Formulaire de contact',
			'notify_template' => <<<EOT
Un message a été posté depuis le formulaire de contact :

Nom : #{@gender.index('Mme', 'Mlle', 'M')} #{@lastname} #{@firstname}
<wdp:if test="@company">Société : #{@company}</wdp:if>
E-Mail : #{@email}

Message : #{@message}
EOT
		);
	}
}