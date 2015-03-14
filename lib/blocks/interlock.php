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
 *
 * @property-read \ICanBoogie\Core $app
 * @property-read \ICanBoogie\Module\ModelCollection $models
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

	protected function get_models()
	{
		return $this->app->models;
	}

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
		/* @var $lock_user \Icybee\Modules\Users\User */

		$lock = $this->lock;
		$lock_user = $this->models['users'][$lock['uid']];
		$lock_user_url = \ICanBoogie\Routing\contextualize("/admin/users/{$lock_user->uid}/edit");

		$time = round((strtotime($lock['until']) - time()) / 60);
		$message = $time ? "Le verrou devrait disparaitre dans $time minutes." : "Le verrou devrait disparaitre dans moins d'une minutes.";

		return <<<EOT
<h1 class="block-title">Édition impossible</h1>

<form method="get" action="">
	<input type="hidden" name="retry" value="1" />

	<p>Impossible d'éditer l'enregistrement parce qu'il est en cours d'édition par
	<a title="Username: $lock_user->username" href="$lock_user_url">$lock_user->name</a>.</p>

	<div class="form-actions">
	<button class="btn btn-success">Réessayer</button> <span class="small light">$message</span>
	</div>
</form>
EOT;
	}
}
