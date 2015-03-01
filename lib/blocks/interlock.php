<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use Brickrouge\Element;

/**
 * An interlock block, displayed instead of the edit block when another user has locked the record
 * to edit.
 */
class InterlockBlock extends Element
{
	/**
	 * The module managing the record.
	 *
	 * @var Module
	 */
	protected $module;

	/**
	 * Element attributes.
	 *
	 * @var array
	 */
	protected $attributes;

	protected $lock;

	public function __construct(Module $module, array $attributes, array $params)
	{
		$this->module = $module;
		$this->attributes = $attributes;

		$this->parse_params($params);

		parent::__construct('div', [ 'class' => 'block-alert block--interlock' ]);
	}

	protected function parse_params(array $params)
	{
		$this->lock = $params['lock'];
	}

	public function render_inner_html()
	{
		global $core;

		$lock = $this->lock;
		$luser = $core->models['users'][$lock['uid']];
		$luser_url = \ICanBoogie\Routing\contextualize("/admin/users/{$luser->uid}/edit");

		$time = round((strtotime($lock['until']) - time()) / 60);
		$message = $time ? "Le verrou devrait disparaitre dans $time minutes." : "Le verrou devrait disparaitre dans moins d'une minutes.";

		return <<<EOT
<h1 class="block-title">Édition impossible</h1>

<form method="get" action="">
	<input type="hidden" name="retry" value="1" />

	<p>Impossible d'éditer l'enregistrement parce qu'il est en cours d'édition par
	<a title="Username: $luser->username" href="$luser_url">$luser->name</a>.</p>

	<div class="form-actions">
	<button class="btn btn-success">Réessayer</button> <span class="small light">$message</span>
	</div>
</form>
EOT;
	}
}
