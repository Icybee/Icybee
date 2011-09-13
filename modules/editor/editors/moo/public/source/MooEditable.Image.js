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
		if (this.popup)
		{
			this.popup.close();
		}

		this.fireEvent('close', this);

		return this;
	},

	open: function()
	{
		var defaultImage =

			'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ' +
			'bWFnZVJlYWR5ccllPAAAAAZQTFRF////IiIiHNlGNAAAAAF0Uk5TAEDm2GYAAAAeSURBVHjaYmBk' +
			'YMCPKJVnZBi1YtSKUSuGphUAAQYAxEkBVsmDp6QAAAAASUVORK5CYII=';

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

		this.node.addEvent
		(
			'load', function(ev)
			{
				if (this.popup)
				{
					this.popup.reposition();
				}
			}
			.bind(this)
		);

		this.previousImage = this.node.get('src');

		//
		// We create the adjust element if it's not created yet
		//

		if (this.popup)
		{
			this.popup.attachAnchor(this.node);
			this.popup.adjust.setValues(this.node);
			this.popup.open();
		}
		else
		{
			if (!this.fetchAdjustOperation)
			{
				this.fetchAdjustOperation = new Request.Widget
				(
					'adjust-thumbnail/popup', this.setupPopup.bind(this)
				);
			}

			this.fetchAdjustOperation.get({ selected: this.node.get('data-nid') || this.node.src });
		}
	},

	setupPopup: function(popElement)
	{
		this.popup = new Widget.Popup.Adjust
		(
			popElement,
			{
				anchor: this.node,
				iframe: this.editor.iframe
			}
		);

		this.popup.addEvent
		(
			'closeRequest', function(ev)
			{
				var mode = ev.mode;
				var src = this.node.src;

				if (mode == 'cancel')
				{
					this.node.src = src = this.previousImage;
				}
				else if (mode == 'none')
				{
					src = null;
				}

				if (!src || src.substring(0, 5) == 'data:')
				{
					this.node.destroy();

					delete this.node;
				}

				this.close();
			}
			.bind(this)
		);

		this.popup.open();
		this.popup.adjust.setValues(this.node);

		this.popup.adjust.addEvent
		(
			'change', function(ev)
			{
				this.node.src = this.editor.baseHREF + ev.url;
				this.node.set('data-nid', ev.nid);

				if (ev.options.lightbox)
				{
					this.node.set('data-lightbox', true);
				}
				else
				{
					this.node.removeAttribute('data-lightbox');
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