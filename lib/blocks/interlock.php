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

		parent::__construct('div', array('class' => 'block-alert block-alert--interlock'));
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
		$url = $core->request->path;

		$time = round((strtotime($lock['until']) - time()) / 60);
		$message = $time ? "Le verrou devrait disparaitre dans $time minutes." : "Le verrou devrait disparaitre dans moins d'une minutes.";

		return <<<EOT
<h2>Édition impossible</h2>

<p>Impossible d'éditer l'enregistrement parce qu'il est en cours d'édition par
<em>$luser->name</em> <span class="small">($luser->username)</span>.</p>

<form method="get" action="">
	<input type="hidden" name="retry" value="1" />

	<div class="form-actions">
	<button class="btn-success">Réessayer</button> <span class="small light">$message</span>
	</div>
</form>
EOT;
	}
}