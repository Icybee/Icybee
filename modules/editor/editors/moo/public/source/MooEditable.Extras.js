/*
---

name: MooEditable.Extras

description: Extends MooEditable to include more (simple) toolbar buttons.

license: MIT-style license

authors:
- Lim Chee Aun
- Ryan Mitchell

requires:
# - MooEditable
# - MooEditable.UI
# - MooEditable.UI.MenuList

provides:
- MooEditable.Actions.formatBlock
- MooEditable.Actions.justifyleft
- MooEditable.Actions.justifyright
- MooEditable.Actions.justifycenter
- MooEditable.Actions.justifyfull
- MooEditable.Actions.removeformat
- MooEditable.Actions.insertHorizontalRule

...
*/

MooEditable.Locale.define({
	blockFormatting: 'Format de block',
	paragraph: 'Paragraphe',
	heading1: 'En-tête 1',
	heading2: 'En-tête 2',
	heading3: 'En-tête 3',
	heading4: 'En-tête 4',
	alignLeft: 'Align Left',
	alignRight: 'Align Right',
	alignCenter: 'Align Center',
	alignJustify: 'Align Justify',
	removeFormatting: 'Remove Formatting',
	insertHorizontalRule: 'Insert Horizontal Rule'
});

Object.append(MooEditable.Actions, {

	formatBlock: {
		title: MooEditable.Locale.get('blockFormatting'),
		type: 'menu-list',
		options: {
			list: [
				{text: MooEditable.Locale.get('paragraph'), value: 'p'},
				{text: MooEditable.Locale.get('heading2'), value: 'h2', style: 'font-size:18px; font-weight:bold;'},
				{text: MooEditable.Locale.get('heading3'), value: 'h3', style: 'font-size:14px; font-weight:bold;'},
				{text: MooEditable.Locale.get('heading4'), value: 'h4', style: 'font-size:12px; font-weight:bold;'}
			]
		},
		states: {
			tags: ['p', /*'h1',*/ 'h2', 'h3', 'h4']
		},
		command: function(menulist, name){
			var argument = '<' + name + '>';
			this.focus();
			this.execute('formatBlock', false, argument);
		}
	},

	justifyleft:{
		title: MooEditable.Locale.get('alignLeft'),
		states: {
			css: {'text-align': 'left'}
		},
		command: function(button, ev)
		{
			var node = this.selection.getNode();
			var tagName = node.tagName;

			if (tagName == 'IMG' || tagName == 'OBJECT')
			{
				node.align = 'left';
			}
			else if (tagName == 'P')
			{
				//node.setStyle('text-align', 'left');
				node.align = '';
			}
		}
	},

	justifyright:{
		title: MooEditable.Locale.get('alignRight'),
		states: {
			css: {'text-align': 'right'}
		},
		command: function(button, ev)
		{
			var node = this.selection.getNode();
			var tagName = node.tagName;

			if (tagName == 'IMG' || tagName == 'OBJECT')
			{
				node.align = 'right';
			}
			else if (tagName == 'P')
			{
				//node.setStyle('text-align', 'right');
				node.align = 'right';
			}
		}
	},

	justifycenter:{
		title: MooEditable.Locale.get('alignCenter'),
		states: {
			tags: ['center'],
			css: {'text-align': 'center'}
		}
	},

	justifyfull:{
		title: MooEditable.Locale.get('alignJustify'),
		states: {
			css: {'text-align': 'justify'}
		}
	},

	removeformat: {
		title: MooEditable.Locale.get('removeFormatting')
	},

	insertHorizontalRule: {
		title: MooEditable.Locale.get('insertHorizontalRule'),
		states: {
			tags: ['hr']
		},
		command: function(){
			this.selection.insertContent('<hr>');
		}
	}

});
