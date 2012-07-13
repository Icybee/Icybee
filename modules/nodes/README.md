The "Nodes" module (nodes)
==========================

The "Nodes" module (nodes) introduces the "Node" content type to the CMS
[Icybee](http://icybee.org). Most modules introducing content types inherit from this
module, this includes the "Contents" module (contents) as well as the "Pages" module
(pages).




Event callback: ICanBoogie\Modules\System\Modules\ActivateOperation::process
----------------------------------------------------------------------------

Updates default admin routes.




Event callback: ICanBoogie\Modules\System\Modules\DeactivateOperation::process
----------------------------------------------------------------------------

Updates default admin routes.




Event callback: ICanBoogie\Modules\Users\DeleteOperation::process:before
------------------------------------------------------------------------

Checks if the user being deleted is used by any node. If the user is used and error
with the `uid` key is added to the errors collector.




Markup: node:navigation
-----------------------

The markup creates a navigation block with links to the list, the next record and the
previous record.