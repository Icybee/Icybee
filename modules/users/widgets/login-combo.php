<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Widget\Users;

use Brickrouge\A;

use Brickrouge\Element;

class LoginCombo extends Element
{
	protected $elements=array();

	public function __construct(array $attributes=array())
	{
		$login = new Login;
		$password = new NonceRequest;

		$password->children['email'][Element::DESCRIPTION] = new A(t('Cancel', array(), array('scope' => 'button')));

		$this->elements['login'] = $login;
		$this->elements['password'] = $password;

		parent::__construct
		(
			'div', $attributes + array
			(
				'id' => 'login',
				'class' => 'widget-login-combo'
			)
		);
	}

	protected function render_inner_html()
	{
		return parent::render_inner_html() . <<<EOT
<div class="wrapper">{$this->elements['login']}</div>
<div class="wrapper" style="height: 0">{$this->elements['password']}</div>
EOT;
	}
}