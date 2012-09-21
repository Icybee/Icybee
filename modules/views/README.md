The "Views" module (views)
==========================

Allows dynamic data from modules to be displayed in content zones.

Modules usually define three view types: `home`, `list` and `view`. The `home` view displays a
small number of records on an home page. The `list` view displays a list of records and comes with
a pagination to browse through older or newer records. Finally, the `view` view displays the
detail of a record.

Author: Olivier Laviale [@olvlv](https://twitter.com/olvlvl)




Events
------

### Icybee\Modules\Views\Collection\CollectEvent

Fired after the views defined by the enabled modules have been collected. Allows third parties to
alter the collection.



### Icybee\Modules\Views\ActiveRecordProvider\BeforeAlterConditionsEvent

Fired before `alter_conditions` is invoked.



### Icybee\Modules\Views\ActiveRecordProvider\AlterConditionsEvent

Fired after `alter_conditions` was invoked.



### Icybee\Modules\Views\ActiveRecordProvider\BeforeAlterQueryEvent

Fired before `alter_query` is invoked.



### Icybee\Modules\Views\ActiveRecordProvider\AlterQueryEvent

Fired after `alter_query` was invoked.



### Icybee\Modules\Views\ActiveRecordProvider\BeforeAlterContextEvent

Fired before `alter_context` is invoked.



### Icybee\Modules\Views\ActiveRecordProvider\AlterContextEvent

Fired after `alter_context` was invoked.



### Icybee\Modules\Views\ActiveRecordProvider\AlterResultEvent

Fired after `extract_result` was invoked.




Events callbacks
----------------


### ICanBoogie\Modules\Pages\SaveOperation::process

Updates the target page of a view.




Prototype methods
-----------------

### ICanBoogie\ActiveRecord\Node::url

Returns the relative URL of a record for the specified view type.



### ICanBoogie\ActiveRecord\Node::absolute_url

Returns the URL of a record for the specified view type.



### ICanBoogie\ActiveRecord\Node::get_url

Returns the relative URL of a record.



### ICanBoogie\ActiveRecord\Node::get_absolute_url

Returns the URL of a record.



### ICanBoogie\ActiveRecord\Site::resolve_view_target

Returns the target page associated with a view.



### ICanBoogie\ActiveRecord\Site::resolve_view_url

Returns the URL of a view.



### ICanBoogie\Core::get_views

Returns the view collection.




Markups
-------

### call-view

Displays a view.

```html
<h2>Last articles</h2>
<wdp:call-view name="articles/home" />
```