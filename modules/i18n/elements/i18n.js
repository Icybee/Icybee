window.addEvent
(
	'domready', function()
	{
		$$('.wd-i18n').each
		(
			function(el)
			{
				var nativeLanguage = el.get('data-native');

				var el_language = el.getElement('[name=language]');
				var el_language_container = el_language.getParent('label');
				var el_language_description = el_language_container.getNext();

				if (!el_language_description.match('div.element-description'))
				{
					el_language_description = null;
				}

				var el_native = el.getElement('[name=tnid]');

				if (el_native)
				{
					var el_native_container = el_native.getParent('label');
					var el_native_description = el_native_container.getNext('.element-description');
				}
				else
				{
					var el_native_container = el_language_container.getNext('label');
				}

				function check_language()
				{
					var language = el_language.get('value');

					var display = '';

					if (!language || language == nativeLanguage)
					{
						display = 'none';
					}

					el_native_container.setStyle('display', display);

					if (el_native_description)
					{
						el_native_description.setStyle('display', display);
					}
				}

				el_language.addEvent('change', check_language);

				check_language();

				/*
				 * Add special support for language inheritance. If a 'parentid' input element
				 * is available in the form of our element, we use it to inherit the language.
				 *
				 * When the language is inherited, the 'language' element is set to the inherited
				 * language and locked in a read only state.
				 */

				var el_parentid = $(el_language.form.elements.parentid);

				if (el_parentid)
				{
					var el_language_override = new Element('input', { type: 'hidden', name: 'language' });

					function check_parent_language()
					{
						var value = el_parentid.get('value');

						if (!value)
						{
							el_language.disabled = false;
							el_language_override.dispose();

							return;
						}

						var operation = new Request.API
						({
							url: 'components/i18n/nodes/' + value + '/language',

							onSuccess: function(response)
							{
								var language = response.rc;

								el_language.set('value', language);
								el_language_override.set('value', language);

								if (language)
								{
									el_language.disabled = true;
									el_language_override.inject(el_language, 'after');
								}
								else
								{
									el_language.disabled = false;
									el_language_override.dispose();
								}
							}
						});

						operation.get();
					}

					el_parentid.addEvent('change', check_parent_language);

					check_parent_language();
				}
			}
		);
	}
);