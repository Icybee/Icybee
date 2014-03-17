# Icybee

Icybee is a CMS for PHP5.4+

If you want to try the CMS for yourself please [download the demonstration](http://icybee.org/icybee-demo-latest.zip)
and follow the [installation guide](https://github.com/Icybee/Icybee/wiki/Installation-guide-for-Icybee%27s-epic-demonstration).





## Features





### Ready out of the box

Out of the box you have everything you need to manage your news, images, files. You can edit
complex contents thanks to the many editors included. Create a website. Create some pages. Choose
where to display your lists and details. You can even choose the pattern of the URL of your
records.





### Multisite, multilanguage

Your website should be translated, or you want to manage multiple websites using the same instance
of Icybee ? Just create a new website using the administration UI and choose the relation between
your websites.





### Views, CSS and JavaScript

Modules that manage contents–such as Articles, News, Press release, Job offers...–comes with all
the views, CSS and JavaScript required to display their content. As such, adding news support to
your website can be done in seconds.





An online demo
--------------

A demonstration is also available online but you won't have access to the administration
interface yet. Still you can check it out at the following address:

<http://demo.icybee.org>





What can be done with it ?
--------------------------

Here are some of the last references:

- <http://www.transitionspro.fr>
- <http://www.berger-levrault.fr>
- <http://www.atalian.cz> (a cluster of 10 websites, each with multiple languages)
- <http://www.atalian.com>
- <http://weirdog.com>
- <http://demo.icybee.org>

You can also check what its predecessor was capable of and dream about what Icybee is now
capable of, after twelve months of development:

<http://www.wdpublisher.com/references/>





----------





## Requirements

The package requires PHP 5.4 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
	"minimum-stability": "dev",

	"require": {
		"icybee/icybee": "2.x"
	},

	"scripts": {
		"post-autoload-dump": "ICanBoogie\\AutoConfig\\Generator::on_autoload_dump"
	}
}
```





## Usage

```php
<?php

// index.php

require 'vendor/autoload.php';

$core = Icybee\start();
$request = $core();
$response = $request();
$response();
```





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/Icybee), its repository can
be cloned with the following command line:

	$ git clone https://github.com/Icybee/Icybee.git





## Documentation

You can generate the documentation for the package and its dependencies with the `make doc`
command. The documentation is generated in the `docs` directory. [ApiGen](http://apigen.org/) is
required. The directory can later be cleaned with the `make clean` command.





## License

Icybee is licensed under the New BSD License - See the [LICENSE](LICENSE) file for details.