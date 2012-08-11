/*
---

script: MooEditable.Image.js

description: Extends MooEditable to insert image with manipulation options.

license: MIT-style license

authors:
- Olivier Laviale

requires:
# - MooEditable
# - MooEditable.UI
# - MooEditable.Actions

provides: [MooEditable.UI.ImageDialog, MooEditable.Actions.image]

usage: |
	Add the following tags in your html
	<link rel="stylesheet" href="MooEditable.css">
	<link rel="stylesheet" href="MooEditable.Image.css">
	<script src="mootools.js"></script>
	<script src="MooEditable.js"></script>
	<script src="MooEditable.Image.js"></script>

	<script>
	window.addEvent('domready', function(){
		var mooeditable = $('textarea-1').mooEditable({
			actions: 'bold italic underline strikethrough | image | toggleview'
		});
	});
	</script>

...
*/

!function() {

var defaultImage =

	'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ' +
	'bWFnZVJlYWR5ccllPAAAAAZQTFRF////IiIiHNlGNAAAAAF0Uk5TAEDm2GYAAAAeSURBVHjaYmBk' +
	'YMCPKJVnZBi1YtSKUSuGphUAAQYAxEkBVsmDp6QAAAAASUVORK5CYII='

MooEditable.UI.ImageDialog = new Class
({
	Extends: MooEditable.UI.Dialog,

	initialize: function(editor)
	{
		this.editor = editor;
		this.unique = Math.random();

		this.dummy_el = new Element
		(
			'div',
			{
				styles:
				{
					'display': 'none'
				}
			}
		);
	},

	toElement: function()
	{
		return this.dummy_el;
	},

	click: function()
	{
		this.fireEvent('click', arguments);

		return this;
	},

	close: function()
	{
		if (this.popover)
		{
			this.popover.hide();
		}

		this.fireEvent('close', this);

		return this;
	},

	open: function()
	{
		//
		// get the node to edit, if none, a new one is created with a default image
		//

		this.node = this.editor.selection.getNode();

		if (!this.node || this.node.get('tag') != 'img')
		{
			this.node = this.editor.doc.createElement('img');
			this.node.src = 'data:image/gif;base64,' + defaultImage;

			this.editor.selection.getRange().insertNode(this.node);
		}

		this.node.addEvent('load', function(ev) {

			if (this.popover) this.popover.reposition()

		}.bind(this))

		this.previousImage = this.node.get('src');

		//
		// We create the adjust element if it's not created yet
		//

		if (this.popover)
		{
			this.popover.attachAnchor(this.node);
			this.popover.adjust.setValues(this.node);
			this.popover.show();
		}
		else
		{
			if (!this.fetchAdjustOperation)
			{
				this.fetchAdjustOperation = new Request.Widget
				(
					'adjust-thumbnail/popup', this.setupPopover.bind(this)
				);
			}

			this.fetchAdjustOperation.get({ selected: this.node.get('data-nid') || this.node.src });
		}
	},

	setupPopover: function(popElement)
	{
		this.popover = new Icybee.Widget.AdjustPopover
		(
			popElement,
			{
				anchor: this.node,
				iframe: this.editor.iframe
			}
		)

		this.popover.iframe = this.editor.iframe // FIXME-20120201: because 'iframe' was missing from options.

		this.popover.addEvent
		(
			'action', function(ev)
			{
				var action = ev.action
				, src = this.node.src

				if (action == 'cancel')
				{
					this.node.src = src = this.previousImage
				}
				else if (action == 'remove')
				{
					src = null
				}

				if (!src || src.substring(0, 5) == 'data:')
				{
					this.node.destroy()

					delete this.node
				}

				this.close()
			}
			.bind(this)
		);

		this.popover.show();
		this.popover.adjust.setValues(this.node);

		this.popover.adjust.addEvent
		(
			'change', function(ev)
			{
				var options = ev.options;

				this.node.src = this.editor.baseHREF + ev.url;
				this.node.set('data-nid', ev.nid);

				if (options.lightbox)
				{
					this.node.set('data-lightbox', true);
				}
				else
				{
					this.node.removeAttribute('data-lightbox');
				}

				if (options.w && options.method != 'surface')
				{
					this.node.set('width', options.w);
				}
				else
				{
					this.node.removeAttribute('width');
				}

				if (options.h && options.method != 'surface')
				{
					this.node.set('height', options.h);
				}
				else
				{
					this.node.removeAttribute('height');
				}
			}
			.bind(this)
		);
	}
});

MooEditable.Actions.image =
{
	title: 'Add/Edit Image',

	options:
	{
		shortcut: 'm'
	},

	dialogs:
	{
		prompt: function(editor)
		{
			return new MooEditable.UI.ImageDialog(editor);
		}
	},

	command: function()
	{
		this.dialogs.image.prompt.open();
	}
};

} ()