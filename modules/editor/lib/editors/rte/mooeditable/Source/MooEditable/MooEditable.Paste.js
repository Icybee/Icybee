/*
---

script: MooEditable.Paste.js

description: Extends MooEditable to insert raw text.

license: MIT-style license

authors:
- Radovan Lozej

requires:
# - MooEditable
# - MooEditable.UI
# - MooEditable.Actions

provides: [MooEditable.UI.PasteDialog, MooEditable.Actions.paste]

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

MooEditable.UI.PasteDialog = new Class
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

	close: function()
	{
		if (this.adjust)
		{
			this.adjust.destroy();

			this.adjust = null;
		}

		this.fireEvent('close', this);

		return this;
	},

	open: function()
	{
		if (this.adjust)
		{
			return this.close();
		}

		var editor = this.editor;
		var button = editor.toolbar.items.paste.el;
		var button_coordinates = button.getCoordinates();

		//console.log('should open adjust. editor: %a, button: %a, coords: %a', editor, button, button_coordinates);

		adjust = new Element
		(
			'div',
			{
				'class': 'mooeditable-ui-dialog mooeditable-paste-dialog',
				styles:
				{
					position: 'absolute',
					width: '64ex',
					//'background-color': 'red',
					left: button_coordinates.left + button_coordinates.width / 2,
					'margin-left': '-32ex',
					top: button_coordinates.bottom + 20
				}
			}
		);

		this.adjust = adjust;

		var ta;
		var ok;

		var action_frame = new Element
		(
			'div',
			{
				'class': 'mooeditable-ui-dialog-action-frame'
			}
		);

		action_frame.adopt
		(
			ok = new Element
			(
				'button.btn-primary',
				{
					type: 'button',
					html: 'Coller'
				}
			)
		);

		var contents = new Element
		(
			'div',
			{
				'class': 'mooeditable-ui-dialog-contents-frame'
			}
		);

		contents.adopt
		(
			ta = new Element
			(
				'textarea',
				{
					rows: 10
				}
			)
		);

		adjust.adopt(contents, action_frame);

		ok.addEvent
		(
			'click', function(ev)
			{
				var value = ta.get('value');

				if (value)
				{
					value = value.replace(/\r?\n\r?\n/gi, '</p><p>');
					value = value.replace(/\r?\n/gi, '<br />');
					value = '<p>' + value + '</p>';

					//console.log('shall paste: %a, selection: %a', value, editor.selection);

					editor.selection.insertContent(value);
				}

				this.close();
			}
			.bind(this)
		);


		document.body.appendChild(adjust);

		ta.focus();
	}
});

MooEditable.Actions.paste =
{
	title: 'Paste text, stripping formating',

	/*
	options:
	{
		shortcut: 'm'
	},
	*/

	dialogs:
	{
		prompt: function(editor)
		{
			return new MooEditable.UI.PasteDialog(editor);
		}
	},

	command: function()
	{
		this.dialogs.paste.prompt.open();
	}
};