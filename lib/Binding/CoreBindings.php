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
 * {@link \ICanBoogie\Core} prototype bindings.
 *
 * @property \Icybee\Document $document
 */
trait CoreBindings
{
	use \ICanBoogie\Binding\ActiveRecord\CoreBindings;
	use \ICanBoogie\Binding\CLDR\CoreBindings
	use \ICanBoogie\Binding\Event\CoreBindings;
	use \ICanBoogie\Binding\Render\CoreBindings;
	use \ICanBoogie\Binding\Routing\CoreBindings;
	use \ICanBoogie\Binding\I18n\CoreBindings;
	use \ICanBoogie\Module\CoreBindings;

	use \Icybee\Modules\Editor\Binding\CoreBindings;
	use \Icybee\Modules\Users\Binding\CoreBindings;
	use \Icybee\Modules\Sites\Binding\CoreBindings;
	use \Icybee\Modules\Registry\Binding\CoreBindings;
	use \Icybee\Modules\Cache\Binding\CoreBindings;
	use \Icybee\Modules\Files\Binding\CoreBindings;
}
