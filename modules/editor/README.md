The "Editor" module (editor)
----------------------------

The "Editor" module (editor) provides an API for editors.

Editors can be used in many places: to edit the body of an article, the description of an image
the body of a page, to pick an image to use as banner... Editors can be used to edit text, images,
forms, views or any complex data.

Editors are composed of two parts, the _editor_ and the _editor element_. The _editor_ is used to
serialize/unserialize the edited content, as well as to render it. The _editor element_ provides
the UI to edit the content.

The following editors are provided:

* `rte` - A Rich Text Editor.
* `textmark` - An editor for the TextMark/Markdown syntax.
* `image` - Lets you use a managed image.
* `node` - Lets you use a node.
* `patron` - An editor for the Patron template engine.
* `php` - An editor for PHP code.
* `raw` - Lets you use HTML code.
* `widgets` - An editor that lets you sort and pick widgets.

Author: Olivier Laviale [@olvlvl](https://twitter.com/olvlvl)

Note: This module is part of the [Icybee](http://icybee.org) package.




Config: editors
---------------

The `editors` config is used to define the available editors.




Event: ICanBoogie\Modules\Editor\Collection::alert
--------------------------------------------------

One can listen to the `ICanBoogie\Modules\Editor\Collection::alert` event to alter the editors
collection.




Prototype: ICanBoogie\Core\get_editors
--------------------------------------

The `editors` getter is added to the core object and allow an easy access to the editors
collection:

```php
<?php

$editors = \ICanBoogie\Modules\Editor\Collection::get();
// or
$editors = $core->editors;
```