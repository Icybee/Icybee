# Pages

The Pages module (`pages`) introduces the "Page" content type to the CMS
[Icybee](http://icybee.org). Pages are used to created the website tree, display contents and
views. The module provides a request dispatcher for this purpose.





## Requirement

The package requires PHP 5.3 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
	"minimum-stability": "dev",
	"require":
	{
		"icybee/module-pages": "*"
	}
}
```





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/module-pages), its repository can be
cloned with the following command line:

	$ git clone git://github.com/Icybee/module-pages.git pages





## Documentation

The documentation for the package and its dependencies can be generated with the `make doc`
command. The documentation is generated in the `docs` directory using [ApiGen](http://apigen.org/).
The package directory can later by cleaned with the `make clean` command.





### Prototype methods





#### `Icybee\Modules\Sites\Site::get_home`

The `home` getter is added to instances of `Icybee\Modules\Sites\Site`. It returns the home
page of the instance:

```php
<?php

echo "Home page URL: " . $core->site->home->url;
```





#### `ICanBoogie\Core::volatile_get_page`

The `page` getter is added to instances of `ICanBoogie\Core`. It returns the page currently being
displayed. The getter is a shortcut to `$core->request->context->page`.





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite. The package
directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://travis-ci.org/Icybee/modules-pages.png?branch=master)](https://travis-ci.org/Icybee/modules-pages)





## License

The module is licensed under the New BSD License - See the LICENSE file for details.