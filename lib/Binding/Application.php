<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Binding;

/**
 * {@link \ICanBoogie\Application} prototype bindings.
 *
 * @property \Icybee\Element\Document $document
 */
trait Application
{
	use \ICanBoogie\Binding\ActiveRecord\ApplicationBindings;
	use \ICanBoogie\Binding\CLDR\ApplicationBindings;
	use \ICanBoogie\Binding\Event\ApplicationBindings;
	use \ICanBoogie\Binding\Render\ApplicationBindings;
	use \ICanBoogie\Binding\Routing\ApplicationBindings;
	use \ICanBoogie\Binding\I18n\ApplicationBindings;
	use \ICanBoogie\Binding\Module\ApplicationBindings;

	use \Icybee\Modules\Editor\Binding\ApplicationBindings;
	use \Icybee\Modules\Users\Binding\ApplicationBindings;
	use \Icybee\Modules\Sites\Binding\ApplicationBindings;
	use \Icybee\Modules\Registry\Binding\ApplicationBindings;
	use \Icybee\Modules\Cache\Binding\ApplicationBindings;
	use \Icybee\Modules\Files\Binding\ApplicationBindings;
}
