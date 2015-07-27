<?php

namespace Icybee\Binding;

/**
 * {@link \ICanBoogie\Core} prototype bindings.
 */
trait CoreBindings
{
	use \ICanBoogie\Binding\ActiveRecord\CoreBindings;
	use \ICanBoogie\Binding\CLDR\CoreBindings
	use \ICanBoogie\Binding\Event\CoreBindings;
	use \ICanBoogie\Binding\Render\CoreBindings;
	use \ICanBoogie\Binding\Routing\CoreBindings;
	use \ICanBoogie\Module\CoreBindings;

	use \Icybee\Modules\Editor\Binding\CoreBindings;
	use \Icybee\Modules\Users\Binding\CoreBindings;
	use \Icybee\Modules\Sites\Binding\CoreBindings;
}
