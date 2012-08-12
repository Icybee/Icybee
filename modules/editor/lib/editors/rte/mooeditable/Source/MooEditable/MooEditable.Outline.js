/**
 *
 */

!function() {

//	var rulesByStylesheets = []

	MooEditable.Actions.outline = {

		title : 'Outline blocks',

		load : function(editor) {

			/*
			editor.addEvent('editorClick', function(ev, editor) {
				console.log('editorClick', arguments)
			})
			*/

			/*
			var styleSheets = editor.doc.styleSheets;
			var regex = /([\w-]+)?\.([\w-]+)/;

			function parseStylesheet(styleSheet) {
				var href = styleSheet.href;

				if (rulesByStylesheets[href] != undefined) {
					return rulesByStylesheets[href];
				}

				var rules = styleSheets[x].cssRules;
				var globalRules = {};
				var elementsRules = {};

				for (var i = 0, j = rules.length; i < j; i++) {

					var rule = rules[i];

					if (rule.type != CSSRule.STYLE_RULE)
						continue;

					var selectorText = rule.selectorText;

					if (selectorText[0] == '#')
						continue;
					if (selectorText.indexOf('.') == -1)
						continue;

					var parts = selectorText.split(',');

					for ( var a = 0, b = parts.length; a < b; a++) {

						var part = parts[a];//.trim();
						var match = regex.exec(part);
						var tagName = match[1];

						if (tagName === undefined) {

							globalRules[match[2]] = match[0];
						} else {
							if (tagName.match(/html|body/i))
								continue;

							if (!elementsRules[tagName]) {
								elementsRules[tagName] = {};
							}

							elementsRules[tagName][match[2]] = match[0];
						}
					}
				}

				return rulesByStylesheets[href] = [ globalRules, elementsRules ];
			}

			var globalRules = {}
			, elementsRules = {}
			, x
			, y

			for (x = 0, y = styleSheets.length; x < y; ++x) {

				var a = parseStylesheet(styleSheets[x])

				Object.merge(globalRules, a[0])
				Object.merge(elementsRules, a[1])
			}

//			console.log('attach: %a, globalRules: %a, elementsRules: %a', editor, globalRules, elementsRules);
*/
		},

		command : function() {
			this.doc.body.toggleClass('mooeditable-outline');
		}
	};

} ()