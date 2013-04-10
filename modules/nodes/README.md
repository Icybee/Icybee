# The Nodes module (nodes)

The Nodes module (nodes) introduces the "Node" content type to the CMS
[Icybee](http://icybee.org). Most modules introducing content types inherit from it, this
includes the [Contents](https://github.com/Icybee/Icybee/tree/master/modules/contents)
module (contents) as well as the [Pages](https://github.com/Icybee/Icybee/tree/master/modules/pages)
module (pages).





## Requirement

The package requires PHP 5.3 or later.  
The package also requires an installation of [Icybee](http://icybee.org).





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
	"minimum-stability": "dev",
	"require":
	{
		"icybee/module-nodes": "*"
	}
}
```





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/module-nodes), its repository can be
cloned with the following command line:

	$ git clone git://github.com/Icybee/module-nodes.git nodes





## Documentation

The package is documented as part of the [Icybee](http://icybee.org/) CMS
[documentation](http://icybee.org/docs/). The documentation for the package and its
dependencies can be generated with the `make doc` command. The documentation is generated in
the `docs` directory using [ApiGen](http://apigen.org/). The package directory can later by
cleaned with the `make clean` command.





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite. The package
directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://travis-ci.org/Icybee/modules-nodes.png?branch=master)](https://travis-ci.org/Icybee/modules-nodes)






## License

The module is licensed under the New BSD License - See the LICENSE file for details.





## Provided classes

The module provides several classes that might be of interest if you want to write a sub-module,
although you might rather create a sub-module of the Contents module (`contents`). Of course,
there if the ActiveRecord class and its model class, but also blocks, operations and a view
provider.





## Event hooks





### `ICanBoogie\Modules\System\Modules\ActivateOperation::process`

Updates default admin routes.





### `ICanBoogie\Modules\System\Modules\DeactivateOperation::process`

Updates default admin routes.





### `Icybee\Modules\Users\User::collect_dependencies`

Register the nodes depending on the specified record. All node types are registered whatever their
module (constructor). 





### ICanBoogie\Modules\Users\DeleteOperation::process:before

Checks if the user being deleted is used by any node. If the user is used and error
with the `uid` key is added to the error collector.





## Patron markups





### node:navigation

The markup creates a navigation block with links to the list, the next record and the
previous record.

```html
<p:node:navigation />
```





## Dashboard panels

The module provides two dashboard panels. One displays a summary of the number of records per node
category. The other displays a list of the last nodes updated by the user.