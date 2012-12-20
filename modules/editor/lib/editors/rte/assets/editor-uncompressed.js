/*
---

name: MooEditable

description: Class for creating a WYSIWYG editor, for contentEditable-capable browsers.

license: MIT-style license

authors:
- Lim Chee Aun
- Radovan Lozej
- Ryan Mitchell
- Olivier Refalo
- T.J. Leahy

requires:
- Core/Class.Extras
- Core/Element.Event
- Core/Element.Dimensions

inspiration:
- Code inspired by Stefan's work [Safari Supports Content Editing!](http://www.xs4all.nl/~hhijdra/stefan/ContentEditable.html) from [safari gets contentEditable](http://walkah.net/blog/walkah/safari-gets-contenteditable)
- Main reference from Peter-Paul Koch's [execCommand compatibility](http://www.quirksmode.org/dom/execCommand.html)
- Some ideas and code inspired by [TinyMCE](http://tinymce.moxiecode.com/)
- Some functions inspired by Inviz's [Most tiny wysiwyg you ever seen](http://forum.mootools.net/viewtopic.php?id=746), [mooWyg (Most tiny WYSIWYG 2.0)](http://forum.mootools.net/viewtopic.php?id=5740)
- Some regex from Cameron Adams's [widgEditor](http://widgeditor.googlecode.com/)
- Some code from Juan M Martinez's [jwysiwyg](http://jwysiwyg.googlecode.com/)
- Some reference from MoxieForge's [PunyMCE](http://punymce.googlecode.com/)
- IE support referring Robert Bredlau's [Rich Text Editing](http://www.rbredlau.com/drupal/node/6)

provides: [MooEditable, MooEditable.Selection, MooEditable.UI, MooEditable.Actions]

...
*/

(function(){

var blockEls = /^(H[1-6]|HR|P|DIV|ADDRESS|PRE|FORM|TABLE|LI|OL|UL|TD|CAPTION|BLOCKQUOTE|CENTER|DL|DT|DD|SCRIPT|NOSCRIPT|STYLE|ARTICLE|ASIDE|DETAILS|FIGCAPTION|FIGURE|FOOTER|HEADER|HGROUP|NAV)$/i;
var urlRegex = /^(https?|ftp|rmtp|mms):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i;
var protectRegex = /<(script|noscript|style)[\u0000-\uFFFF]*?<\/(script|noscript|style)>/g;

this.MooEditable = new Class({

	Implements: [Events, Options],

	options: {
		toolbar: true,
		cleanup: true,
		paragraphise: true,
		xhtml : true,
		semantics : true,
		actions: 'bold italic underline strikethrough | insertunorderedlist insertorderedlist indent outdent | undo redo | createlink unlink | urlimage | toggleview',
		handleSubmit: true,
		handleLabel: true,
		disabled: false,
		baseCSS: 'html{ height: 100%; cursor: text; } body{ font-family: sans-serif; }',
		extraCSS: '',
		externalCSS: '',
		html: '<!DOCTYPE html><html id="{HTMLID}" lang="{LANGUAGE}"><head><meta charset="UTF-8">{BASEHREF}<style>{BASECSS} {EXTRACSS}</style>{EXTERNALCSS}</head><body></body></html>',
		rootElement: 'p',
		baseURL: '',
		dimensions: null,
		htmlId: 'wysiwyg'
	},

	initialize: function(el, options){

		// check for content editable and design mode support
		if (!("contentEditable" in document.body) && !("designMode" in document)){
			return;
		}

		this.setOptions(options);
		this.textarea = document.id(el);
		this.textarea.store('MooEditable', this);
		this.actions = this.options.actions.clean().split(' ');
		this.keys = {};
		this.dialogs = {};
		this.protectedElements = [];
		this.actions.each(function(action){
			var act = MooEditable.Actions[action];
			if (!act) return;
			if (act.options){
				var key = act.options.shortcut;
				if (key) this.keys[key] = action;
			}
			if (act.dialogs){
				Object.each(act.dialogs, function(dialog, name){
					dialog = dialog.attempt(this);
					dialog.name = action + ':' + name;
					if (typeOf(this.dialogs[action]) != 'object') this.dialogs[action] = {};
					this.dialogs[action][name] = dialog;
				}, this);
			}
			if (act.events){
				Object.each(act.events, function(fn, event){
					this.addEvent(event, fn);
				}, this);
			}
		}.bind(this));
		this.render();
	},

	toElement: function(){
		return this.textarea;
	},

	render: function(){
		var self = this;

		// Dimensions
		var dimensions = this.options.dimensions || this.textarea.getSize();

		// Build the container
		this.container = new Element('div', {
			id: (this.textarea.id) ? this.textarea.id + '-mooeditable-container' : null,
			'class': 'mooeditable-container'/*,
			styles: {
				width: dimensions.x
			}*/
		});

		// Override all textarea styles
		this.textarea.addClass('mooeditable-textarea').setStyle('height', dimensions.y);

		// Build the iframe
		this.iframe = new IFrame({
			'class': 'mooeditable-iframe',
			frameBorder: 0,
			src: 'javascript:""', // Workaround for HTTPs warning in IE6/7
			styles: {
				height: Math.max(100, dimensions.y)
			}
		});

		// Build resizer
		this.resizer = new Element('div.mooeditable-resizer')

		this.toolbar = new MooEditable.UI.Toolbar({
			onItemAction: function(){
				var args = Array.from(arguments);
				var item = args[0];
				self.action(item.name, args);
			}
		});
		this.attach.delay(1, this);

		// Update the event for textarea's corresponding labels
		if (this.options.handleLabel && this.textarea.id) $$('label[for="'+this.textarea.id+'"]').addEvent('click', function(e){
			if (self.mode != 'iframe') return;
			e.preventDefault();
			self.focus();
		});

		// Update & cleanup content before submit
		if (this.options.handleSubmit){
			this.form = this.textarea.getParent('form');
			if (this.form) {
				this.form.addEvent('submit', function(){
					if (self.mode == 'iframe') self.saveContent();
				});
			}
		}

		this.fireEvent('render', this);
	},

	attach: function(){
		var self = this;

		// Assign view mode
		this.mode = 'iframe';

		// Editor iframe state
		this.editorDisabled = false;

		// Put textarea inside container
		this.container.wraps(this.textarea);

		this.textarea.setStyle('display', 'none');

		this.iframe.setStyle('display', '').inject(this.textarea, 'before');
		this.resizer.inject(this.textarea, 'after');

		Object.each(this.dialogs, function(action, name){
			Object.each(action, function(dialog){
				document.id(dialog).inject(self.iframe, 'before');
				var range;
				dialog.addEvents({
					open: function(){
						range = self.selection.getRange();
						self.editorDisabled = true;
						self.toolbar.disable(name);
						self.fireEvent('dialogOpen', this);
					},
					close: function(){
						self.toolbar.enable();
						self.editorDisabled = false;
						self.focus();
						if (range) self.selection.setRange(range);
						self.fireEvent('dialogClose', this);
					}
				});
			});
		});

		// contentWindow and document references
		this.win = this.iframe.contentWindow;
		this.doc = this.win.document;

		// Deal with weird quirks on Gecko
		if (Browser.firefox) this.doc.designMode = 'On';

		var externalCSS = '';

		if (this.options.externalCSS)
		{
			if (typeOf(this.options.externalCSS) == 'array')
			{
				this.options.externalCSS.each
				(
					function(href)
					{
						externalCSS += '<link rel="stylesheet" href="' + href + '">\n';
					}
				);
			}
			else
			{
				externalCSS = '<link rel="stylesheet" href="' + this.options.externalCSS + '">';
			}
		}

		this.baseHREF = document.location.protocol + '//' + document.location.hostname;

		// Build the content of iframe
		var docHTML = this.options.html.substitute({
			BASECSS: this.options.baseCSS,
			EXTRACSS: this.options.extraCSS,
			EXTERNALCSS: externalCSS,
			//BASEHREF: (this.options.baseURL) ? '<base href="' + this.options.baseURL + '" />': '',
			BASEHREF: '<base href="' + this.baseHREF + '" />',
			HTMLID: this.options.htmlId,
			LANGUAGE: this.textarea.get('lang') || document.html.get('lang')
		});
		this.doc.open();
		this.doc.write(docHTML);
		this.doc.close();

		// Turn on Design Mode
		// IE fired load event twice if designMode is set
		(Browser.ie) ? this.doc.body.contentEditable = true : this.doc.designMode = 'On';

		// Mootoolize window, document and body
		Object.append(this.win, new Window);
		Object.append(this.doc, new Document);
		if (Browser.Element){
			var winElement = this.win.Element.prototype;
			for (var method in Element){ // methods from Element generics
				if (!method.test(/^[A-Z]|\$|prototype|mooEditable/)){
					winElement[method] = Element.prototype[method];
				}
			}
		} else {
			document.id(this.doc.body);
		}

		this.setContent(this.textarea.get('value'));

		// Bind all events
		this.doc.addEvents({
			mouseup: this.editorMouseUp.bind(this),
			mousedown: this.editorMouseDown.bind(this),
			mouseover: this.editorMouseOver.bind(this),
			mouseout: this.editorMouseOut.bind(this),
			mouseenter: this.editorMouseEnter.bind(this),
			mouseleave: this.editorMouseLeave.bind(this),
			contextmenu: this.editorContextMenu.bind(this),
			click: this.editorClick.bind(this),
			dblclick: this.editorDoubleClick.bind(this),
			keypress: this.editorKeyPress.bind(this),
			keyup: this.editorKeyUp.bind(this),
			keydown: this.editorKeyDown.bind(this),
			focus: this.editorFocus.bind(this),
			blur: this.editorBlur.bind(this)
		});
		this.win.addEvents({
			focus: this.editorFocus.bind(this),
			blur: this.editorBlur.bind(this),
			load: function(){
				this.actions.each(function(action){
					var act = MooEditable.Actions[action];
					if (!act || !act.load) return;
					act.load(this);
				}, this);
			}.bind(this)
		});
		['cut', 'copy', 'paste'].each(function(event){
			self.doc.body.addListener(event, self['editor' + event.capitalize()].bind(self));
		});
		this.textarea.addEvent('keypress', this.textarea.retrieve('mooeditable:textareaKeyListener', this.keyListener.bind(this)));

		// Fix window focus event not firing on Firefox 2
		if (Browser.firefox2) this.doc.addEvent('focus', function(){
			self.win.fireEvent('focus').focus();
		});

		// IE9 is also not firing focus event
		if (this.doc.addEventListener) this.doc.addEventListener('focus', function(){
			self.win.fireEvent('focus');
		}, true);

		// styleWithCSS, not supported in IE and Opera
		if (!Browser.ie && !Browser.opera){
			var styleCSS = function(){
				self.execute('styleWithCSS', false, false);
				self.doc.removeEvent('focus', styleCSS);
			};
			this.win.addEvent('focus', styleCSS);
		}

		if (this.options.toolbar){
			document.id(this.toolbar).inject(this.container, 'top');
			this.toolbar.render(this.actions);
		}

		if (this.options.disabled) this.disable();

		this.selection = new MooEditable.Selection(this.win);

		this.oldContent = this.getContent();

		this.fireEvent('attach', this);

		return this;
	},

	detach: function(){
		this.saveContent();
		this.textarea.setStyle('display', '').removeClass('mooeditable-textarea').inject(this.container, 'before');
		this.textarea.removeEvent('keypress', this.textarea.retrieve('mooeditable:textareaKeyListener'));
		this.container.dispose();
		this.fireEvent('detach', this);
		return this;
	},

	enable: function(){
		this.editorDisabled = false;
		this.toolbar.enable();
		return this;
	},

	disable: function(){
		this.editorDisabled = true;
		this.toolbar.disable();
		return this;
	},

	editorFocus: function(e){
		this.oldContent = '';
		this.fireEvent('editorFocus', [e, this]);
	},

	editorBlur: function(e){
		this.oldContent = this.saveContent().getContent();
		this.fireEvent('editorBlur', [e, this]);
	},

	editorMouseUp: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		if (this.options.toolbar) this.checkStates();

		this.fireEvent('editorMouseUp', [e, this]);
	},

	editorMouseDown: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		this.fireEvent('editorMouseDown', [e, this]);
	},

	editorMouseOver: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		this.fireEvent('editorMouseOver', [e, this]);
	},

	editorMouseOut: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		this.fireEvent('editorMouseOut', [e, this]);
	},

	editorMouseEnter: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		/*@olvlvl: disabled, is it usefull to check the text on mousenter ?
		if (this.oldContent && this.getContent() != this.oldContent){
			this.focus();
			this.fireEvent('editorPaste', [e, this]);
		}
		*/

		this.fireEvent('editorMouseEnter', [e, this]);
	},

	editorMouseLeave: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		this.fireEvent('editorMouseLeave', [e, this]);
	},

	editorContextMenu: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		this.fireEvent('editorContextMenu', [e, this]);
	},

	editorClick: function(e){
		// make images selectable and draggable in Safari
		if (Browser.safari || Browser.chrome){
			var el = e.target;
			if (Element.get(el, 'tag') == 'img'){

				// safari doesnt like dragging locally linked images
				if (this.options.baseURL){
					if (el.getProperty('src').indexOf('http://') == -1){
						el.setProperty('src', this.options.baseURL + el.getProperty('src'));
					}
				}

				this.selection.selectNode(el);
				this.checkStates();
			}
		}

		this.fireEvent('editorClick', [e, this]);
	},

	editorDoubleClick: function(e){
		this.fireEvent('editorDoubleClick', [e, this]);
	},

	editorKeyPress: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		this.keyListener(e);

		this.fireEvent('editorKeyPress', [e, this]);
	},

	editorKeyUp: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		var c = e.code;
		// 33-36 = pageup, pagedown, end, home; 45 = insert
		if (this.options.toolbar && (/^enter|left|up|right|down|delete|backspace$/i.test(e.key) || (c >= 33 && c <= 36) || c == 45 || e.meta || e.control)){
			if (Browser.ie6){ // Delay for less cpu usage when you are typing
				clearTimeout(this.checkStatesDelay);
				this.checkStatesDelay = this.checkStates.delay(500, this);
			} else {
				this.checkStates();
			}
		}

		this.fireEvent('editorKeyUp', [e, this]);
	},

	editorKeyDown: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		if (e.key == 'enter'){
			if (this.options.paragraphise){
				if (e.shift && (Browser.safari || Browser.chrome)){
					var s = this.selection;
					var r = s.getRange();

					// Insert BR element
					var br = this.doc.createElement('br');
					r.insertNode(br);

					// Place caret after BR
					r.setStartAfter(br);
					r.setEndAfter(br);
					s.setRange(r);

					// Could not place caret after BR then insert an nbsp entity and move the caret
					if (s.getSelection().focusNode == br.previousSibling){
						var nbsp = this.doc.createTextNode('\u00a0');
						var p = br.parentNode;
						var ns = br.nextSibling;
						(ns) ? p.insertBefore(nbsp, ns) : p.appendChild(nbsp);
						s.selectNode(nbsp);
						s.collapse(1);
					}

					// Scroll to new position, scrollIntoView can't be used due to bug: http://bugs.webkit.org/show_bug.cgi?id=16117
					this.win.scrollTo(0, Element.getOffsets(s.getRange().startContainer).y);

					e.preventDefault();
				} else if (Browser.firefox || Browser.safari || Browser.chrome){
					var node = this.selection.getNode();
					var isBlock = Element.getParents(node).include(node).some(function(el){
						return el.nodeName.test(blockEls);
					});
					if (!isBlock) this.execute('insertparagraph');
				}
			} else {
				if (Browser.ie){
					var r = this.selection.getRange();
					var node = this.selection.getNode();
					if (r && node.get('tag') != 'li'){
						this.selection.insertContent('<br>');
						this.selection.collapse(false);
					}
					e.preventDefault();
				}
			}
		}

		if (Browser.opera){
			var ctrlmeta = e.control || e.meta;
			if (ctrlmeta && e.key == 'x'){
				this.fireEvent('editorCut', [e, this]);
			} else if (ctrlmeta && e.key == 'c'){
				this.fireEvent('editorCopy', [e, this]);
			} else if ((ctrlmeta && e.key == 'v') || (e.shift && e.code == 45)){
				this.fireEvent('editorPaste', [e, this]);
			}
		}

		this.fireEvent('editorKeyDown', [e, this]);
	},

	editorCut: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		this.fireEvent('editorCut', [e, this]);
	},

	editorCopy: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		this.fireEvent('editorCopy', [e, this]);
	},

	editorPaste: function(e){
		if (this.editorDisabled){
			e.stop();
			return;
		}

		this.fireEvent('editorPaste', [e, this]);
	},

	keyListener: function(e){
		var key = (Browser.Platform.mac) ? e.meta : e.control;
		if (!key || !this.keys[e.key]) return;
		e.preventDefault();
		var item = this.toolbar.getItem(this.keys[e.key]);
		item.action(e);
	},

	focus: function(){
		(this.mode == 'iframe' ? this.win : this.textarea).focus();
		this.fireEvent('focus', this);
		return this;
	},

	action: function(command, args){
		var action = MooEditable.Actions[command];
		if (action.command && typeOf(action.command) == 'function'){
			action.command.apply(this, args);
		} else {
			this.focus();
			this.execute(command, false, args);
			if (this.mode == 'iframe') this.checkStates();
		}
	},

	execute: function(command, param1, param2){
		if (this.busy) return;
		this.busy = true;
		this.doc.execCommand(command, param1, param2);
		this.saveContent();
		this.busy = false;
		return false;
	},

	toggleView: function(){
		this.fireEvent('beforeToggleView', this);
		if (this.mode == 'textarea'){
			this.mode = 'iframe';
			this.iframe.setStyle('display', '');
			this.setContent(this.textarea.value);
			this.textarea.setStyle('display', 'none');
		} else {
			this.saveContent();
			this.mode = 'textarea';
			this.textarea.setStyle('display', '');
			this.textarea.setStyle('visibility', 'visible');
			this.iframe.setStyle('display', 'none');
		}
		this.fireEvent('toggleView', this);
		this.focus.delay(10, this);
		return this;
	},

	getContent: function(){
		var protect = this.protectedElements;
		var html = this.doc.body.get('html').replace(/<!-- mooeditable:protect:([0-9]+) -->/g, function(a, b){
			return protect[b.toInt()];
		});
		return this.cleanup(this.ensureRootElement(html));
	},

	setContent: function(content){
		var protect = this.protectedElements;
		content = content.replace(protectRegex, function(a){
			protect.push(a);
			return '<!-- mooeditable:protect:' + (protect.length-1) + ' -->';
		});

		// because webkit trashes img src relative URL
//		content = content.replace(/src="\//gi, 'src="' + this.baseHREF + '/');

		  content = content.replace(/src="([^"]+)/gi, function(match, src) {

			  if (!src.match(/^http:\/\//))
			  {
				  if (src[0] != '/')
				  {
					  src = '/' + src
				  }

				  src = this.baseHREF + src

//				  console.log('restore:', src)
			  }

//			  console.log(src)

			  return 'src="' + src
		  }.bind(this))

		this.doc.body.set('html', this.ensureRootElement(content));
		return this;
	},

	saveContent: function(){
		var value;
		value = (this.mode == 'iframe') ? this.getContent() : this.textearea.innerHTML;
		value = value.replace(new RegExp('="' + this.baseHREF, 'gi'), '="'); //@olvlvl: transform absolute URLs into relative ones
		this.textarea.set('value', value);
		return this;
	},

	ensureRootElement: function(val){
		if (this.options.rootElement){
			var el = new Element('div', {html: val.trim()});
			var start = -1;
			var create = false;
			var html = '';
			var length = el.childNodes.length;
			for (var i=0; i<length; i++){
				var childNode = el.childNodes[i];
				var nodeName = childNode.nodeName;
				if (!nodeName.test(blockEls) && nodeName !== '#comment'){
					if (nodeName === '#text'){
						if (childNode.nodeValue.trim()){
							if (start < 0) start = i;
							html += childNode.nodeValue;
						}
					} else {
						if (start < 0) start = i;
						html += new Element('div').adopt($(childNode).clone()).get('html');
					}
				} else {
					create = true;
				}
				if (i == (length-1)) create = true;
				if (start >= 0 && create){
					var newel = new Element(this.options.rootElement, {html: html});
					el.replaceChild(newel, el.childNodes[start]);
					for (var k=start+1; k<i; k++){
						el.removeChild(el.childNodes[k]);
						length--;
						i--;
						k--;
					}
					start = -1;
					create = false;
					html = '';
				}
			}
			val = el.get('html').replace(/\n\n/g, '');
		}
		return val;
	},

	checkStates: function(){
		var element = this.selection.getNode();
		if (!element) return;
		if (typeOf(element) != 'element') return;

		this.actions.each(function(action){
			var item = this.toolbar.getItem(action);
			if (!item) return;
			item.deactivate();

			var states = MooEditable.Actions[action]['states'];
			if (!states) return;

			// custom checkState
			if (typeOf(states) == 'function'){
				states.attempt([document.id(element), item], this);
				return;
			}

			try{
				if (this.doc.queryCommandState(action)){
					item.activate();
					return;
				}
			} catch(e){}

			if (states.tags){
				var el = element;
				do {
					var tag = el.tagName.toLowerCase();
					if (states.tags.contains(tag)){
						item.activate(tag);
						break;
					}
				}
				while ((el = Element.getParent(el)) != null);
			}

			if (states.css){
				var el = element;
				do {
					var found = false;
					for (var prop in states.css){
						var css = states.css[prop];
						if (el.style[prop.camelCase()].contains(css)){
							item.activate(css);
							found = true;
						}
					}
					if (found || el.tagName.test(blockEls)) break;
				}
				while ((el = Element.getParent(el)) != null);
			}
		}.bind(this));
	},

	cleanup: function(source){

//		source = this.cleanHtml(source)

//		console.log('shoudl cleanup:', source)

//		return source









		if (!this.options.cleanup) return source.trim();

		do {
			var oSource = source;

			// replace base URL references: ie localize links
			if (this.options.baseURL){
				//source = source.replace('="' + this.options.baseURL, '="');
				source = source.replace('="' + this.options.baseURL, '="/');
			}

			// Webkit cleanup
			source = source.replace(/<br class\="webkit-block-placeholder">/gi, "<br />");
			source = source.replace(/<span class="Apple-style-span">(.*)<\/span>/gi, '$1');
			source = source.replace(/ class="Apple-style-span"/gi, '');
			source = source.replace(/<span style="">/gi, '');

			// Remove padded paragraphs
			source = source.replace(/<p>\s*<br ?\/?>\s*<\/p>/gi, '<p>\u00a0</p>');
			source = source.replace(/<p>(&nbsp;|\s)*<\/p>/gi, '<p>\u00a0</p>');
			if (!this.options.semantics){
				source = source.replace(/\s*<br ?\/?>\s*<\/p>/gi, '</p>');
			}

			// Replace improper BRs (only if XHTML : true)
			if (this.options.xhtml){
				source = source.replace(/<br>/gi, "<br />");
			}

			if (this.options.semantics){
				//remove divs from <li>
				if (Browser.ie){
					source = source.replace(/<li>\s*<div>(.+?)<\/div><\/li>/g, '<li>$1</li>');
				}
				//remove stupid apple divs
				if (Browser.safari || Browser.chrome){
					source = source.replace(/^([\w\s]+.*?)<div>/i, '<p>$1</p><div>');
					source = source.replace(/<div>(.+?)<\/div>/ig, '<p>$1</p>');
				}

				//<p> tags around a list will get moved to after the list
				if (!Browser.ie){
					//not working properly in safari?
					source = source.replace(/<p>[\s\n]*(<(?:ul|ol)>.*?<\/(?:ul|ol)>)(.*?)<\/p>/ig, '$1<p>$2</p>');
					source = source.replace(/<\/(ol|ul)>\s*(?!<(?:p|ol|ul|img).*?>)((?:<[^>]*>)?\w.*)$/g, '</$1><p>$2</p>');
				}

				source = source.replace(/<br[^>]*><\/p>/g, '</p>'); // remove <br>'s that end a paragraph here.
				// j'ai désactivé la ligne parce que je n'aime pas ce comportement, je préfèrerai
				// que le P disparaisse autour de l'image seulement si elle flotte.
				//source = source.replace(/<p>\s*(<img[^>]+>)\s*<\/p>/ig, '$1\n'); // if a <p> only contains <img>, remove the <p> tags

				//format the source
				source = source.replace(/<p([^>]*)>(.*?)<\/p>(?!\n)/g, '<p$1>$2</p>\n'); // break after paragraphs
				source = source.replace(/<\/(ul|ol|p)>(?!\n)/g, '</$1>\n'); // break after </p></ol></ul> tags
				source = source.replace(/><li>/g, '>\n\t<li>'); // break and indent <li>
				source = source.replace(/([^\n])<\/(ol|ul)>/g, '$1\n</$2>'); //break before </ol></ul> tags
				source = source.replace(/([^\n])<img/ig, '$1\n<img'); // move images to their own line
				source = source.replace(/^\s*$/g, ''); // delete empty lines in the source code (not working in opera)
			}

			// Remove leading and trailing BRs
			source = source.replace(/<br ?\/?>$/gi, '');
			source = source.replace(/^<br ?\/?>/gi, '');

			// Remove useless BRs
			if (this.options.paragraphise) source = source.replace(/(h[1-6]|p|div|address|pre|li|ol|ul|blockquote|center|dl|dt|dd)><br ?\/?>/gi, '$1>');

			// Remove BRs right before the end of blocks
			source = source.replace(/<br ?\/?>\s*<\/(h1|h2|h3|h4|h5|h6|li|p)/gi, '</$1');

			// Semantic conversion
			source = source.replace(/<span style="font-weight: bold;">(.*)<\/span>/gi, '<strong>$1</strong>');
			source = source.replace(/<span style="font-style: italic;">(.*)<\/span>/gi, '<em>$1</em>');
			source = source.replace(/<b\b[^>]*>(.*?)<\/b[^>]*>/gi, '<strong>$1</strong>');
			source = source.replace(/<i\b[^>]*>(.*?)<\/i[^>]*>/gi, '<em>$1</em>');
			source = source.replace(/<u\b[^>]*>(.*?)<\/u[^>]*>/gi, '<span style="text-decoration: underline;">$1</span>');
			source = source.replace(/<strong><span style="font-weight: normal;">(.*)<\/span><\/strong>/gi, '$1');
			source = source.replace(/<em><span style="font-weight: normal;">(.*)<\/span><\/em>/gi, '$1');
			source = source.replace(/<span style="text-decoration: underline;"><span style="font-weight: normal;">(.*)<\/span><\/span>/gi, '$1');
			source = source.replace(/<strong style="font-weight: normal;">(.*)<\/strong>/gi, '$1');
			source = source.replace(/<em style="font-weight: normal;">(.*)<\/em>/gi, '$1');

			// Replace uppercase element names with lowercase
			source = source.replace(/<[^> ]*/g, function(match){return match.toLowerCase();});

			// Replace uppercase attribute names with lowercase
			source = source.replace(/<[^>]*>/g, function(match){
				   match = match.replace(/ [^=]+=/g, function(match2){return match2.toLowerCase();});
				   return match;
			});

			// Put quotes around unquoted attributes
			source = source.replace(/<[^!][^>]*>/g, function(match){
				   match = match.replace(/( [^=]+=)([^"][^ >]*)/g, "$1\"$2\"");
				   return match;
			});

			//make img tags xhtml compatible <img>,<img></img> -> <img/>
			if (this.options.xhtml){
				source = source.replace(/<img([^>]+)(\s*[^\/])>(<\/img>)*/gi, '<img$1$2 />');
			}

			//remove double <p> tags and empty <p> tags
			source = source.replace(/<p>(?:\s*)<p>/g, '<p>');
			source = source.replace(/<\/p>\s*<\/p>/g, '</p>');

			// Replace <br>s inside <pre> automatically added by some browsers
			source = source.replace(/<pre[^>]*>.*?<\/pre>/gi, function(match){
				return match.replace(/<br ?\/?>/gi, '\n');
			});

			// Final trim
			source = source.trim();
		}
		while (source != oSource);

		/* weirdog: remove empty elements */

//		source = source.replace(/<([^\s]+)>\s+<\/\1>/g, '');

		source = this.cleanHtml(source)

		/* /weirdog */

		return source;
	}

});

MooEditable.Selection = new Class({

	initialize: function(win){
		this.win = win;
	},

	getSelection: function(){
		this.win.focus();
		return (this.win.getSelection) ? this.win.getSelection() : this.win.document.selection;
	},

	getRange: function(){
		var s = this.getSelection();

		if (!s) return null;

		try {
			return s.rangeCount > 0 ? s.getRangeAt(0) : (s.createRange ? s.createRange() : null);
		} catch(e) {
			// IE bug when used in frameset
			return this.doc.body.createTextRange();
		}
	},

	setRange: function(range){
		if (range.select){
			Function.attempt(function(){
				range.select();
			});
		} else {
			var s = this.getSelection();
			if (s.addRange){
				s.removeAllRanges();
				s.addRange(range);
			}
		}
	},

	selectNode: function(node, collapse){
		var r = this.getRange();
		var s = this.getSelection();

		if (r.moveToElementText){
			Function.attempt(function(){
				r.moveToElementText(node);
				r.select();
			});
		} else if (s.addRange){
			collapse ? r.selectNodeContents(node) : r.selectNode(node);
			s.removeAllRanges();
			s.addRange(r);
		} else {
			s.setBaseAndExtent(node, 0, node, 1);
		}

		return node;
	},

	isCollapsed: function(){
		var r = this.getRange();
		if (r.item) return false;
		return r.boundingWidth == 0 || this.getSelection().isCollapsed;
	},

	collapse: function(toStart){
		var r = this.getRange();
		var s = this.getSelection();

		if (r.select){
			r.collapse(toStart);
			r.select();
		} else {
			toStart ? s.collapseToStart() : s.collapseToEnd();
		}
	},

	getContent: function(){
		var r = this.getRange();
		var body = new Element('body');

		if (this.isCollapsed()) return '';

		if (r.cloneContents){
			body.appendChild(r.cloneContents());
		} else if (r.item != undefined || r.htmlText != undefined){
			body.set('html', r.item ? r.item(0).outerHTML : r.htmlText);
		} else {
			body.set('html', r.toString());
		}

		var content = body.get('html');
		return content;
	},

	getText : function(){
		var r = this.getRange();
		var s = this.getSelection();
		return this.isCollapsed() ? '' : r.text || (s.toString ? s.toString() : '');
	},

	getNode: function(){
		var r = this.getRange();

		if (!Browser.ie || Browser.version >= 9){
			var el = null;

			if (r){
				el = r.commonAncestorContainer;

				// Handle selection a image or other control like element such as anchors
				if (!r.collapsed)
					if (r.startContainer == r.endContainer)
						if (r.startOffset - r.endOffset < 2)
							if (r.startContainer.hasChildNodes())
								el = r.startContainer.childNodes[r.startOffset];

				while (typeOf(el) != 'element') el = el.parentNode;
			}

			return document.id(el);
		}

		return document.id(r.item ? r.item(0) : r.parentElement());
	},

	insertContent: function(content){
		if (Browser.ie){
			var r = this.getRange();
			if (r.pasteHTML){
				r.pasteHTML(content);
				r.collapse(false);
				r.select();
			} else if (r.insertNode){
				r.deleteContents();
				if (r.createContextualFragment){
					 r.insertNode(r.createContextualFragment(content));
				} else {
					var doc = this.win.document;
					var fragment = doc.createDocumentFragment();
					var temp = doc.createElement('div');
					fragment.appendChild(temp);
					temp.outerHTML = content;
					r.insertNode(fragment);
				}
			}
		} else {
			this.win.document.execCommand('insertHTML', false, content);
		}
	}

});

// Avoiding Locale dependency
// Wrapper functions to be used internally and for plugins, defaults to en-US
var phrases = {};
MooEditable.Locale = {

	define: function(key, value){
		if (typeOf(window.Locale) != 'null') return Locale.define('en-US', 'MooEditable', key, value);
		if (typeOf(key) == 'object') Object.merge(phrases, key);
		else phrases[key] = value;
	},

	get: function(key){
		if (typeOf(window.Locale) != 'null') return Locale.get('MooEditable.' + key);
		return key ? phrases[key] : '';
	}

};

MooEditable.Locale.define({
	ok: 'OK',
	cancel: 'Cancel',
	bold: 'Bold',
	italic: 'Italic',
	underline: 'Underline',
	strikethrough: 'Strikethrough',
	unorderedList: 'Unordered List',
	orderedList: 'Ordered List',
	indent: 'Indent',
	outdent: 'Outdent',
	undo: 'Undo',
	redo: 'Redo',
	removeHyperlink: 'Remove Hyperlink',
	addHyperlink: 'Add Hyperlink',
	selectTextHyperlink: 'Please select the text you wish to hyperlink.',
	enterURL: 'Enter URL',
	enterImageURL: 'Enter image URL',
	addImage: 'Add Image',
	toggleView: 'Toggle View'
});

MooEditable.UI = {};

MooEditable.UI.Toolbar= new Class({

	Implements: [Events, Options],

	options: {
		/*
		onItemAction: function(){},
		*/
		'class': ''
	},

	initialize: function(options){
		this.setOptions(options);
		this.el = new Element('div', {'class': 'mooeditable-ui-toolbar ' + this.options['class']});
		this.items = {};
		this.content = null;
	},

	toElement: function(){
		return this.el;
	},

	render: function(actions){
		if (this.content){
			this.el.adopt(this.content);
		} else {
			this.content = actions.map(function(action){
				if (action == '|') {
					return this.addSeparator();
				}
				else if (action == '/') {
					return this.addLineSeparator();
				}
				return this.addItem(action);
			}.bind(this));
		}
		return this;
	},

	addItem: function(action){
		var self = this;
		var act = MooEditable.Actions[action];
		if (!act) return;
		var type = act.type || 'button';
		var options = act.options || {};
		var item = new MooEditable.UI[type.camelCase().capitalize()](Object.append(options, {
			name: action,
			'class': action + '-item toolbar-item',
			title: act.title,
			onAction: self.itemAction.bind(self),
			tabindex: -1
		}));
		this.items[action] = item;
		document.id(item).inject(this.el);
		return item;
	},

	getItem: function(action){
		return this.items[action];
	},

	addSeparator: function(){
		return new Element('span.toolbar-separator').inject(this.el);
	},

	addLineSeparator: function(){
		return new Element('div.toolbar-line-separator').inject(this.el);
	},

	itemAction: function(){
		this.fireEvent('itemAction', arguments);
	},

	disable: function(except){
		Object.each(this.items, function(item){
			(item.name == except) ? item.activate() : item.deactivate().disable();
		});
		return this;
	},

	enable: function(){
		Object.each(this.items, function(item){
			item.enable();
		});
		return this;
	},

	show: function(){
		this.el.setStyle('display', '');
		return this;
	},

	hide: function(){
		this.el.setStyle('display', 'none');
		return this;
	}

});

MooEditable.UI.Button = new Class({

	Implements: [Events, Options],

	options: {
		/*
		onAction: function(){},
		*/
		title: '',
		name: '',
		text: 'Button',
		'class': '',
		shortcut: '',
		mode: 'icon'
	},

	initialize: function(options){
		this.setOptions(options);
		this.name = this.options.name;
		this.render();
	},

	toElement: function(){
		return this.el;
	},

	render: function(){
		var self = this;
		var key = (Browser.Platform.mac) ? 'Cmd' : 'Ctrl';
		var shortcut = (this.options.shortcut) ? ' ( ' + key + '+' + this.options.shortcut.toUpperCase() + ' )' : '';
		var text = this.options.title || name;
		var title = text + shortcut;
		this.el = new Element('button', {
			'class': 'mooeditable-ui-button ' + self.options['class'],
			title: title,
			tabindex: -1,
			html: '<span class="button-icon"></span><span class="button-text">' + text + '</span>',
			events: {
				click: self.click.bind(self),
				mousedown: function(e){ e.preventDefault(); }
			}
		});
		if (this.options.mode != 'icon') this.el.addClass('mooeditable-ui-button-' + this.options.mode);

		this.active = false;
		this.disabled = false;

		// add hover effect for IE
		if (Browser.ie) this.el.addEvents({
			mouseenter: function(e){ this.addClass('hover'); },
			mouseleave: function(e){ this.removeClass('hover'); }
		});

		return this;
	},

	click: function(e){
		e.preventDefault();
		if (this.disabled) return;
		this.action(e);
	},

	action: function(){
		this.fireEvent('action', [this].concat(Array.from(arguments)));
	},

	enable: function(){
		if (this.active) this.el.removeClass('onActive');
		if (!this.disabled) return;
		this.disabled = false;
		this.el.removeClass('disabled').set({
			disabled: false,
			opacity: 1
		});
		return this;
	},

	disable: function(){
		if (this.disabled) return;
		this.disabled = true;
		this.el.addClass('disabled').set({
			disabled: true,
			opacity: 0.4
		});
		return this;
	},

	activate: function(){
		if (this.disabled) return;
		this.active = true;
		this.el.addClass('onActive');
		return this;
	},

	deactivate: function(){
		this.active = false;
		this.el.removeClass('onActive');
		return this;
	}

});

MooEditable.UI.Dialog = new Class({

	Implements: [Events, Options],

	options:{
		/*
		onOpen: function(){},
		onClose: function(){},
		*/
		'class': '',
		contentClass: ''
	},

	initialize: function(html, options){
		this.setOptions(options);
		this.html = html;

		var self = this;
		this.el = new Element('div', {
			'class': 'mooeditable-ui-dialog ' + self.options['class'],
			html: '<div class="dialog-content ' + self.options.contentClass + '">' + html + '</div>',
			styles: {
				'display': 'none'
			},
			events: {
				click: self.click.bind(self)
			}
		});
	},

	toElement: function(){
		return this.el;
	},

	click: function(){
		this.fireEvent('click', arguments);
		return this;
	},

	open: function(){
		this.el.setStyle('display', '');
		this.fireEvent('open', this);
		return this;
	},

	close: function(){
		this.el.setStyle('display', 'none');
		this.fireEvent('close', this);
		return this;
	}

});

MooEditable.UI.AlertDialog = function(alertText){
	if (!alertText) return;
	var html = alertText + ' <button class="dialog-ok-button">' + MooEditable.Locale.get('ok') + '</button>';
	return new MooEditable.UI.Dialog(html, {
		'class': 'mooeditable-alert-dialog',
		onOpen: function(){
			var button = this.el.getElement('.dialog-ok-button');
			(function(){
				button.focus();
			}).delay(10);
		},
		onClick: function(e){
			e.preventDefault();
			if (e.target.tagName.toLowerCase() != 'button') return;
			if (document.id(e.target).hasClass('dialog-ok-button')) this.close();
		}
	});
};

MooEditable.UI.PromptDialog = function(questionText, answerText, fn){
	if (!questionText) return;
	var html = '<label class="dialog-label">' + questionText
		+ ' <input type="text" class="text dialog-input" value="' + answerText + '">'
		+ '</label> <button class="dialog-button dialog-ok-button">' + MooEditable.Locale.get('ok') + '</button>'
		+ '<button class="dialog-button dialog-cancel-button">' + MooEditable.Locale.get('cancel') + '</button>';
	return new MooEditable.UI.Dialog(html, {
		'class': 'mooeditable-prompt-dialog',
		onOpen: function(){
			var input = this.el.getElement('.dialog-input');
			(function(){
				input.focus();
				input.select();
			}).delay(10);
		},
		onClick: function(e){
			e.preventDefault();
			if (e.target.tagName.toLowerCase() != 'button') return;
			var button = document.id(e.target);
			var input = this.el.getElement('.dialog-input');
			if (button.hasClass('dialog-cancel-button')){
				input.set('value', answerText);
				this.close();
			} else if (button.hasClass('dialog-ok-button')){
				var answer = input.get('value');
				input.set('value', answerText);
				this.close();
				if (fn) fn.attempt(answer, this);
			}
		}
	});
};

MooEditable.Actions = {

	bold: {
		title: MooEditable.Locale.get('bold'),
		options: {
			shortcut: 'b'
		},
		states: {
			tags: ['b', 'strong'],
			css: {'font-weight': 'bold'}
		},
		events: {
			beforeToggleView: function(){
				if(Browser.firefox){
					var value = this.textarea.get('value');
					var newValue = value.replace(/<strong([^>]*)>/gi, '<b$1>').replace(/<\/strong>/gi, '</b>');
					if (value != newValue) this.textarea.set('value', newValue);
				}
			},
			attach: function(){
				if(Browser.firefox){
					var value = this.textarea.get('value');
					var newValue = value.replace(/<strong([^>]*)>/gi, '<b$1>').replace(/<\/strong>/gi, '</b>');
					if (value != newValue){
						this.textarea.set('value', newValue);
						this.setContent(newValue);
					}
				}
			}
		}
	},

	italic: {
		title: MooEditable.Locale.get('italic'),
		options: {
			shortcut: 'i'
		},
		states: {
			tags: ['i', 'em'],
			css: {'font-style': 'italic'}
		},
		events: {
			beforeToggleView: function(){
				if (Browser.firefox){
					var value = this.textarea.get('value');
					var newValue = value.replace(/<embed([^>]*)>/gi, '<tmpembed$1>')
						.replace(/<em([^>]*)>/gi, '<i$1>')
						.replace(/<tmpembed([^>]*)>/gi, '<embed$1>')
						.replace(/<\/em>/gi, '</i>');
					if (value != newValue) this.textarea.set('value', newValue);
				}
			},
			attach: function(){
				if (Browser.firefox){
					var value = this.textarea.get('value');
					var newValue = value.replace(/<embed([^>]*)>/gi, '<tmpembed$1>')
						.replace(/<em([^>]*)>/gi, '<i$1>')
						.replace(/<tmpembed([^>]*)>/gi, '<embed$1>')
						.replace(/<\/em>/gi, '</i>');
					if (value != newValue){
						this.textarea.set('value', newValue);
						this.setContent(newValue);
					}
				}
			}
		}
	},

	underline: {
		title: MooEditable.Locale.get('underline'),
		options: {
			shortcut: 'u'
		},
		states: {
			tags: ['u'],
			css: {'text-decoration': 'underline'}
		},
		events: {
			beforeToggleView: function(){
				if(Browser.firefox || Browser.ie){
					var value = this.textarea.get('value');
					var newValue = value.replace(/<span style="text-decoration: underline;"([^>]*)>/gi, '<u$1>').replace(/<\/span>/gi, '</u>');
					if (value != newValue) this.textarea.set('value', newValue);
				}
			},
			attach: function(){
				if(Browser.firefox || Browser.ie){
					var value = this.textarea.get('value');
					var newValue = value.replace(/<span style="text-decoration: underline;"([^>]*)>/gi, '<u$1>').replace(/<\/span>/gi, '</u>');
					if (value != newValue){
						this.textarea.set('value', newValue);
						this.setContent(newValue);
					}
				}
			}
		}
	},

	strikethrough: {
		title: MooEditable.Locale.get('strikethrough'),
		options: {
			shortcut: 's'
		},
		states: {
			tags: ['s', 'strike'],
			css: {'text-decoration': 'line-through'}
		}
	},

	insertunorderedlist: {
		title: MooEditable.Locale.get('unorderedList'),
		states: {
			tags: ['ul']
		}
	},

	insertorderedlist: {
		title: MooEditable.Locale.get('orderedList'),
		states: {
			tags: ['ol']
		}
	},

	indent: {
		title: MooEditable.Locale.get('indent'),
		states: {
			tags: ['blockquote']
		}
	},

	outdent: {
		title: MooEditable.Locale.get('outdent')
	},

	undo: {
		title: MooEditable.Locale.get('undo'),
		options: {
			shortcut: 'z'
		}
	},

	redo: {
		title: MooEditable.Locale.get('redo'),
		options: {
			shortcut: 'y'
		}
	},

	unlink: {
		title: MooEditable.Locale.get('removeHyperlink')
	},

	createlink: {
		title: MooEditable.Locale.get('addHyperlink'),
		options: {
			shortcut: 'l'
		},
		states: {
			tags: ['a']
		},
		dialogs: {
			alert: MooEditable.UI.AlertDialog.pass(MooEditable.Locale.get('selectTextHyperlink')),
			prompt: function(editor){
				return MooEditable.UI.PromptDialog(MooEditable.Locale.get('enterURL'), 'http://', function(url){
					editor.execute('createlink', false, url.trim());
				});
			}
		},
		command: function(){
			var selection = this.selection;
			var dialogs = this.dialogs.createlink;
			if (selection.isCollapsed()){
				var node = selection.getNode();
				if (node.get('tag') == 'a' && node.get('href')){
					selection.selectNode(node);
					var prompt = dialogs.prompt;
					prompt.el.getElement('.dialog-input').set('value', node.get('href'));
					prompt.open();
				} else {
					dialogs.alert.open();
				}
			} else {
				var text = selection.getText();
				var prompt = dialogs.prompt;
				if (urlRegex.test(text)) prompt.el.getElement('.dialog-input').set('value', text);
				prompt.open();
			}
		}
	},

	urlimage: {
		title: MooEditable.Locale.get('addImage'),
		options: {
			shortcut: 'm'
		},
		dialogs: {
			prompt: function(editor){
				return MooEditable.UI.PromptDialog(MooEditable.Locale.get('enterImageURL'), 'http://', function(url){
					editor.execute('insertimage', false, url.trim());
				});
			}
		},
		command: function(){
			this.dialogs.urlimage.prompt.open();
		}
	},

	toggleview: {
		title: MooEditable.Locale.get('toggleView'),
		command: function(){
			(this.mode == 'textarea') ? this.toolbar.enable() : this.toolbar.disable('toggleview');
			this.toggleView();
		}
	}

};

MooEditable.Actions.Settings = {};

Element.Properties.mooeditable = {

	get: function(){
		return this.retrieve('MooEditable');
	}

};

Element.implement({

	mooEditable: function(options){
		var mooeditable = this.get('mooeditable');
		if (!mooeditable) mooeditable = new MooEditable(this, options);
		return mooeditable;
	}

});

var resizingTarget = null
	, resizingTargetH = null
	, resizingStartY = null

function resizerOnMouseMove(ev) {

	var h = Math.max(resizingTargetH + ev.client.y - resizingStartY, 100)
	, mirror

	if (resizingTarget.tagName == 'IFRAME')
	{
		mirror = resizingTarget.getNext()
	}
	else
	{
		mirror = resizingTarget.getPrevious()
	}

	resizingTarget.setStyle('height', h)
	mirror.setStyle('height', h)
}

function resizerDone(ev) {

	window.removeEvent('mousemove', resizerOnMouseMove)
	window.removeEvent('mouseup', resizerDone)

	if (resizingTarget.tagName == 'IFRAME')
	{
		resizingTarget.setStyle('visibility', '')
	}

	resizingTarget = null
}

window.addEvent('mousedown:relay(.mooeditable-resizer)', function(ev, el) {

	ev.preventDefault()

	resizingTarget = el.getPrevious()

	if (resizingTarget.getStyle('display') == 'none')
	{
		resizingTarget = resizingTarget.getPrevious()
	}

	resizingTargetH = resizingTarget.getSize().y
	resizingStartY = ev.client.y

	if (resizingTarget.tagName == 'IFRAME')
	{
		resizingTarget.setStyle('visibility', 'hidden')
	}

	window.addEvents({
		mousemove: resizerOnMouseMove,
		mouseup: resizerDone
	})
})

})();/*
---

name: MooEditable.UI.MenuList

description: UI Class to create a menu list (select) element.

license: MIT-style license

authors:
- Lim Chee Aun

requires:
# - MooEditable
# - MooEditable.UI

provides: [MooEditable.UI.MenuList]

...
*/

MooEditable.UI.MenuList = new Class({

	Implements: [Events, Options],

	options: {
		/*
		onAction: function(){},
		*/
		title: '',
		name: '',
		'class': '',
		list: []
	},

	initialize: function(options){
		this.setOptions(options);
		this.name = this.options.name;
		this.render();
	},

	toElement: function(){
		return this.el;
	},

	render: function(){
		var self = this;
		var html = '';
		this.options.list.each(function(item){
			html += '<option value="{value}" style="{style}">{text}</option>'.substitute(item);
		});
		this.el = new Element('select', {
			'class': self.options['class'],
			title: self.options.title,
			tabindex: -1,
			html: html,
			styles: { 'height' : '21px' },
			events: {
				change: self.change.bind(self)
			}
		});

		this.disabled = false;

		// add hover effect for IE
		if (Browser.ie) this.el.addEvents({
			mouseenter: function(e){ this.addClass('hover'); },
			mouseleave: function(e){ this.removeClass('hover'); }
		});

		return this;
	},

	change: function(e){
		e.preventDefault();
		if (this.disabled) return;
		var name = e.target.value;
		this.action(name);
	},

	action: function(){
		this.fireEvent('action', [this].concat(Array.from(arguments)));
	},

	enable: function(){
		if (!this.disabled) return;
		this.disabled = false;
		this.el.set('disabled', false).removeClass('disabled').set({
			disabled: false,
			opacity: 1
		});
		return this;
	},

	disable: function(){
		if (this.disabled) return;
		this.disabled = true;
		this.el.set('disabled', true).addClass('disabled').set({
			disabled: true,
			opacity: 0.4
		});
		return this;
	},

	activate: function(value){
		if (this.disabled) return;
		var index = 0;
		if (value) this.options.list.each(function(item, i){
			if (item.value == value) index = i;
		});
		this.el.selectedIndex = index;
		return this;
	},

	deactivate: function(){
		this.el.selectedIndex = 0;
		this.el.removeClass('onActive');
		return this;
	}

});
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
};/*
---

name: MooEditable.CleanPaste

description: Extends MooEditable to insert text copied from other editors like word without all that messy style-information.

updates in previous version: Improved Internet Explorer handling to break text on to new lines. Improved handling of some styles from newer versions of MS Word to remove extra style tags that were remaining. (David)

updates in this version: Fixed CleanPaste in Safari (Jo)

license: MIT-style license

authors:
- AndrŽ Fiedler <kontakt@visualdrugs.net>
- David Bennett <david@fuzzylime.co.uk>
- Jo Carter <jocarter@holler.co.uk>

requires:
- MooEditable
- MooEditable.Selection
- More/Class.Refactor

usage:
  Add the following tags in your html
  <link rel="stylesheet" href="MooEditable.css">
  <script src="mootools.js"></script>
  <script src="MooEditable.js"></script>
  <script src="MooEditable.CleanPaste.js"></script>

  <script>
  window.addEvent('domready', function (){
	var mooeditable = $('textarea-1').mooEditable();
  });
  </script>

provides: [MooEditable.CleanPaste]

...
*/

MooEditable = Class.refactor(MooEditable, {

	// @FIXED: Removed because inferred by above and breaks MooEditable completely with MooTools 1.3.
	// Extends: MooEditable,

	attach: function () {
		var ret = this.previous();
		this.doc.body.addListener('paste', this.cleanPaste.bind(this));
		return ret;
	},

	cleanPaste: function (e) {
		var txtPastet = e.clipboardData && e.clipboardData.getData ?
			e.clipboardData.getData('text/html') : // Standard
			window.clipboardData && window.clipboardData.getData ?
			window.clipboardData.getData('Text') : // MS
			false;

		// @FIXED: If !MS and data is not html - try this (ie. pasting plain text)
		if ((!txtPastet || '' === txtPastet.trim()) && e.clipboardData && e.clipboardData.getData) {
		  txtPastet = e.clipboardData.getData('Text');
		}

		if (!!txtPastet) { // IE and Safari
		  if (window.clipboardData) {
			this.selection.insertContent(this.cleanHtml(txtPastet, 1)); // IE
		  }
		  else {
			this.selection.insertContent(this.cleanHtml(txtPastet)); // Safari
		  }

//			  new Event(e).stop(); // @olvlvl: issue an error in Safari
		  e.preventDefault()
		  e.stopPropagation()
		}
		else { // no clipboard data available
			this.selection.insertContent('<span id="INSERTION_MARKER">&nbsp;</span>');
			this.txtMarked = this.doc.body.get('html');
			this.doc.body.set('html', '');
			this.replaceMarkerWithPastedText.delay(5, this);
		}
		return this;
	},

	replaceMarkerWithPastedText: function () {
		var txtPastetClean = this.cleanHtml(this.doc.body.get('html'));
		this.doc.body.set('html', this.txtMarked);
		this.selection.selectNode(this.doc.body.getElementById('INSERTION_MARKER'));
		this.selection.insertContent(txtPastetClean);
		return this;
	},

	cleanHtml: function (html, isie) {

		html = html.replace(/<(\w+)([^>]*)>\s+/g, "<$1$2>\n") //@olvlv: reduce the number of blanks after opening tag
		html = html.replace(/>\s+</g, ">\n<") //@olvlv: reduce the number of blanks between close and opening tags

		if (isie) {
			if (!this.options.paragraphise) {
				html = html.replace(/\n/g, "<br />");
			}
			else {
				html = "<p>" + html + "<\/p>";
//			  html = html.replace(/\n/g, "<\/p><p>");
				html = html.replace(/\n\n/g, "<\/p><p>") //@olvlvl: insert new P only on double newline
				html = html.replace(/<p>\s<\/p>/gi, '');
			}
		}
		else {

			// @FIXED: Safari pastes in styles with ' not " - fixed to not be broken in safari
		// @FIXED: Word pastes in Safari

		// remove body and html tag
		html = html.replace(/<html[^>]*?>(.*)/gim, "$1");
		html = html.replace(/<\/html>/gi, '');
		html = html.replace(/<body[^>]*?>(.*)/gi, "$1");
		html = html.replace(/<\/body>/gi, '');

		// remove style, meta and link tags
		html = html.replace(/<style[^>]*?>[\s\S]*?<\/style[^>]*>/gi, '');
		html = html.replace(/<(?:meta|link)[^>]*>\s*/gi, '');

		// remove XML elements and declarations
		html = html.replace(/<\\?\?xml[^>]*>/gi, '');

		// remove w: tags with contents.
		html = html.replace(/<w:[^>]*>[\s\S]*?<\/w:[^>]*>/gi, '');

		// remove tags with XML namespace declarations: <o:p><\/o:p>
		html = html.replace(/<o:p>\s*<\/o:p>/g, '');
		html = html.replace(/<o:p>[\s\S]*?<\/o:p>/g, '&nbsp;');
		html = html.replace(/<\/?\w+:[^>]*>/gi, '');

		// remove comments [SF BUG-1481861].
		html = html.replace(/<\!--[\s\S]*?-->/g, '');
		html = html.replace(/<\!\[[\s\S]*?\]>/g, '');

		// remove mso-xxx styles.
		html = html.replace(/\s*mso-[^:]+:[^;"']+;?/gi, '');

		// remove styles.
		html = html.replace(/<(\w[^>]*) style='([^\']*)'([^>]*)/gim, "<$1$3");
		html = html.replace(/<(\w[^>]*) style="([^\"]*)"([^>]*)/gim, "<$1$3");

		// remove margin styles.
		html = html.replace(/\s*margin: 0cm 0cm 0pt\s*;/gi, '');
		html = html.replace(/\s*margin: 0cm 0cm 0pt\s*"/gi, "\"");

		html = html.replace(/\s*text-indent: 0cm\s*;/gi, '');
		html = html.replace(/\s*text-indent: 0cm\s*"/gi, "\"");

		html = html.replace(/\s*text-align: [^\s;]+;?"/gi, "\"");

		html = html.replace(/\s*page-break-before: [^\s;]+;?"/gi, "\"");

		html = html.replace(/\s*font-variant: [^\s;]+;?"/gi, "\"");

		html = html.replace(/\s*tab-stops:[^;"']*;?/gi, '');
		html = html.replace(/\s*tab-stops:[^"']*/gi, '');

		// remove font face attributes.
		html = html.replace(/\s*face="[^"']*"/gi, '');
		html = html.replace(/\s*face=[^ >]*/gi, '');

		html = html.replace(/\s*font-family:[^;"']*;?/gi, '');
		html = html.replace(/\s*font-size:[^;"']*;?/gi, '');

		/* @olvlvl: we don't want that, at least not in editing. Anyways it doesn't work when '-' is in the class name
		// remove class attributes
		html = html.replace(/<(\w[^>]*) class=([^ |>]*)([^>]*)/gi, "<$1$3");
		*/

		html = html.replace(/class="MsoNormal"/ig, '')

		// remove "display:none" attributes.
		html = html.replace(/<(\w+)[^>]*\sstyle="[^"']*display\s?:\s?none[\s \S]*?<\/\1>/ig, '')

		// remove empty styles.
		html = html.replace(/\s*style='\s*'/gi, '');
		html = html.replace(/\s*style="\s*"/gi, '');

		html = html.replace(/<span\s*[^>]*>\s*&nbsp;\s*<\/span>/gi, '&nbsp;');
		html = html.replace(/<span\s*[^>]*><\/span>/gi, '');


		/* @olvlv: we don't want that
		// remove align attributes
		html = html.replace(/<(\w[^>]*) align=([^ |>]*)([^>]*)/gi, "<$1$3");
		*/

		// remove lang attributes
		html = html.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3");


//		  html = html.replace(/<span([^>]*)>([\s\S]*?)<\/span>/gi, '$2');
//		  html = html.replace(/<font\s*>([\s\S]*?)<\/font>/gi, '$1');
//		  html = html.replace(/<(u|i|strike)>&nbsp;<\/\1>/gi, '&nbsp;');

		//@olvlv: Combo for SPAN and FONT removal, Fix FONT removal (<font size="4"> was not removed)
		html = html.replace(/<(font|i|span|strike|u)(\s*>|\s+[^>]+>)/gi, '')
		html = html.replace(/<\/(font|i|span|strike|u)>/gi, '')

		html = html.replace(/<h\d>\s*<\/h\d>/gi, '');

		// remove language attributes
		html = html.replace(/<(\w[^>]*) language=([^ |>]*)([^>]*)/gi, "<$1$3");

		// remove onmouseover and onmouseout events (from MS word comments effect)
		html = html.replace(/<(\w[^>]*) onmouseover="([^\"']*)"([^>]*)/gi, "<$1$3");
		html = html.replace(/<(\w[^>]*) onmouseout="([^\"']*)"([^>]*)/gi, "<$1$3");

		// the original <Hn> tag sent from word is something like this: <Hn style="margin-top:0px;margin-bottom:0px">
		html = html.replace(/<h(\d)([^>]*)>/gi, '<h$1>');

		// word likes to insert extra <font> tags, when using IE. (Weird).
//			html = html.replace(/<(h\d)><font[^>]*>([\s\S]*?)<\/font><\/\1>/gi, '<$1>$2<\/$1>'); //@olvlv is this necessarty since FONT has been removed earlier ?
		html = html.replace(/<(h\d)><em>([\s\S]*?)<\/em><\/\1>/gi, '<$1>$2<\/$1>');

		// i -> em, b -> strong - doesn't match nested tags e.g <b><i>some text</i></b> - not possible in regexp
		// @see - http://stackoverflow.com/questions/1721223/php-regexp-for-nested-div-tags etc.
		html = html.replace(/<b\b[^>]*>(.*?)<\/b[^>]*>/gi, '<strong>$1</strong>');
		html = html.replace(/<i\b[^>]*>(.*?)<\/i[^>]*>/gi, '<em>$1</em>');

		// remove "bad" tags
		html = html.replace(/<\s+[^>]*>/gi, '');

		// remove empty <span>s (ie. no attributes, no reason for span in pasted text)
		// done twice for nested spans
//			html = html.replace(/<span>([\s\S]*?)<\/span>/gi, '$1'); @olvlv: already done earlier
//			html = html.replace(/<span>([\s\S]*?)<\/span>/gi, '$1');

		// remove empty <div>s (see span)
//			html = html.replace(/<div>([\s\S]*?)<\/div>/gi, '$1'); @olvlvl: these remove ALL DIVs, that's not what we want
//			html = html.replace(/<div>([\s\S]*?)<\/div>/gi, '$1');

		// remove empty tags (three times, just to be sure - for nested empty tags).
		// This also removes any empty anchors
		/*
		html = html.replace(/<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/g, '');
		html = html.replace(/<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/g, '');
		html = html.replace(/<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/g, '');
		*/

		html = html.trim();

		/*@olvlvl: we don't care about that
		// Convert <p> to <br />
		if (!this.options.paragraphise) {
			html.replace(/<p>/gi, '<br />');
			html.replace(/<\/p>/gi, '');
		}
		*/
		/*@olvlvl: this is stupid, the HTML might start with an H1 element or a table !
		// Check if in paragraph - this fixes FF3.6 and it's <br id=""> issue
		else {
		  var check = html.substr(0,2);
		  if ('<p' !== check) {
			html = '<p>' + html + '</p>';
			// Replace breaks with paragraphs
			html = html.replace(/\n/g, "<\/p><p>");
			html = html.replace(/<br[^>]*>/gi, '<\/p><p>');
		  }
		}
		*/

		// Make it valid xhtml
		html = html.replace(/<br>/gi, '<br />');

		// Remove leading and trailing BRs
		html = html.replace(/^(\s*<br \/>\s*)+|(\s*<br \/>\s*)+$/gi, '');

		// remove <br>'s that end a paragraph here.
		html = html.replace(/(\s*<br \/>\s*)+<\/p>/gim, '</p>');

		// remove empty paragraphs - with just a &nbsp; (or whitespace) in (and tags again for good measure)
//			html = html.replace(/<p>&nbsp;<\/p>/gi,'');
		html = html.replace(/<(\w+)[^>]*>\s*&nbsp;\s*<\/\1>/gi, '') //@olvlv P are not the only one that can end up with
		html = html.replace(/<p>\s<\/p>/gi, '');
		html = html.replace
		(
			/<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/g, function(captured, tagName) {

				return tagName.match(/^iframe$/i) ? captured : '';
			}
		)

		html = html.trim();
	  }

	  html = html.replace(/src="([^"]+)/gi, function(match, src) {

		  if (!src.match(/^(http:\/\/|\/)/))
		  {
			  src = this.baseHREF + '/' + src // FIXME-20120221: we could use baseHREF but we don't want an absolute URL
		  }

		  return 'src="' + src
	  }.bind(this))

	  return html;
	}
});/**
 *
 */

MooEditable.Actions.outline = {

	title : 'Outline blocks',

	command : function() {
		this.doc.body.toggleClass('mooeditable-outline')
	}
}/*
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

} ();window.addEvent('brickrouge.update', function() {

	$$('textarea.moo').each(function(el) {

		if (el.retrieve('mooeditable')) return

		var options = el.get('dataset')

		if (options.externalCss)
		{
			options.externalCSS = JSON.decode(options.externalCss)
		}

		if (options.baseUrl)
		{
			options.baseURL = options.baseUrl
		}

		el.mooEditable(options)

		el.store('mooeditable', true)
	})
})