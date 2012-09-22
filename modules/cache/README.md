The "cache" module
==================

The "cache" module provides a common API and UI to manage caches.

On its own, the module can manage the caches of the [ICanBoogie](http://icanboogie.org/)
framework: I18n catalogs, configurations and modules indexes.

Namespace: `Icybee\Modules\Cache`  
Author: Olivier Laviale [@olvlvl](https://twitter.com/olvlvl)




Creating your own cache manager
-------------------------------

You can use any kind of cache with the "cache" module, your manager only has to extends the
`..\CacheManager` class or implement the `..\CacheManagerInterface` interface and provide
the methods to clear/enable/disable the cache as well as return its statistics.

The following properties must also be provided:

- (string) `title`: Title of the cache. The title is translated within the `cache.title` scope.
- (string) `description`: Description of the cache. The description is translated within
the `cache.description` scope.
- (string) `group`: Caches are displayed by groups. The group of the cache can be defined using
this property. The group is translated within the `cache.group` scope.
- (bool) `state`: Whether the cache is enabled.
- (int|bool) `size_limit`: Size limit of the cache, or `false` if not applicable.
- (int|bool) `time_limit`: Time limit of the entries in the cache, or `false` if not applicable.
- (string|null) `config_preview`: A preview of the cache configuration, or `null` if not applicable.
- (string) `editor`: The configuration editor, or `null` if not applicable.

Note: Because the `config_preview` and `editor` properties are seldom used, it is advised to use
getters to return their values:

```php
<?php

use ICanBoogie\PropertyNotDefined;

class CacheManager implements Icybee\Modules\Cache\CacheManagerInterface
{
	public function __get($property)
	{
		if ($property == 'config_preview')
		{
			return // ...
		}
		else if ($property == 'editor')
		{
			return // ...
		}
		
		throw new PropertyNotDefined(array($property, $this));
	}
}
```



### Registering your cache manager

Cache managers are registered on the `..\Collection::collect` event. For instance, this is how
the "views" module registers its cache manager using the `hooks` configuration:

```php
<?php

namespace Icybee\Modules\Views;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\Modules\Cache\Collection::collect' => $hooks . 'on_cache_collection_collect',
		
		// ...
	),
	
	// ...
);
```




Events
------

### Icybee\Modules\Cache\Collection::collect

Third parties may use the event of class `..\Collection\CollectEvent` to
register their cache manager. The event is fired during the construct of the cache collection.

The following code is an example of how the `icybee.views` cache is added to the cache collection:

```php
<?php

namespace Icybee\Modules\Views;

use Icybee\Modules\Cache\Collection as CacheCollection;

static public function on_cache_collection_collect(CacheCollection\CollectEvent $event, CacheCollection $collection)
{
	$event->collection['icybee.views'] = new CacheManager;
}
```



Prototypes
----------

### ICanBoogie\Core\volatile_get_caches

The module adds the `caches` magic property to the core object. The property is used to get the
cache collection.

```php
<?php

$core->caches['core.modules']->clear();
```




Operations
----------

Cache operations require the cache identifier to be defined as key of the operation. For instance,
to clear the `core.modules` cache the operation `POST /api/cache/core.modules/clear` is used. 



### ..\ClearOperation

Clears the specified cache.



### ..\ConfigureOperation

Configures the specified cache.



### ..\DisableOperation

Disables the specified cache.



### ..\EditorOperation

Returns the configuration editor.

The editor is obtained through the `editor` property of the cache.



### ..\EnableOperation

Enables the specified cache.

The cache is cleared before it is enabled.



### ..\StatOperation

Returns the usage (memory, files) of the specified cache.




Events callbacks
----------------

### Icybee\Modules\Modules\ActivateOperation::process

The caches of the framework are cleared when modules are activated.



### Icybee\Modules\Modules\DeactivateOperation:process

The caches of the framework are cleared when modules are deactivated.  