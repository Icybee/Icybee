The "Images" module
===================

Injecting images
----------------

The "Images" modules (resources.images) extends contents module with the ability to inject an
image in their records.

The module alters the "config" block for the user to enable/disable/configure the feature for each
content module.

The config feature has a global scope (should be a group scope in the future, when the scope land
in the Sites module):

- `resources_images.inject.<flat_target_module_id>` (bool|null) true if enabled, undefined
otherwise.
- `resources_images.inject.<flat_target_module_id>.required` (bool) true if the association is
required, false otherwise.
- `resources_images.inject.<flat_target_module_id>.default` (int) identifier of a default
image to use when no image is associate to a record. This only apply when `required` is false. 
	

The module alters the "edit" block for the user to select the image to inject with the content
record.