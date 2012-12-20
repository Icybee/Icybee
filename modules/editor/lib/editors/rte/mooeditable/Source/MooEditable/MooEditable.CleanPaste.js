/*
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
});