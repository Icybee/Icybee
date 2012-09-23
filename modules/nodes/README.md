The "Nodes" module (nodes)
==========================

The "Nodes" module (nodes) introduces the "Node" content type to the CMS
[Icybee](http://icybee.org). Most modules introducing content types inherit from this
type, this includes the [Contents](https://github.com/Icybee/Icybee/tree/master/modules/contents)
module (contents) as well as the [Pages](https://github.com/Icybee/Icybee/tree/master/modules/pages)
module (pages).




Markups
-------

### node:navigation

The markup creates a navigation block with links to the list, the next record and the
previous record.




Events callbacks
----------------

### ICanBoogie\Modules\System\Modules\ActivateOperation::process

Updates default admin routes.



### ICanBoogie\Modules\System\Modules\DeactivateOperation::process

Updates default admin routes.



### ICanBoogie\Modules\Users\DeleteOperation::process:before

Checks if the user being deleted is used by any node. If the user is used and error
with the `uid` key is added to the errors collector.