window.addEvent('domready', function() {

	var group = document.id(document.body).getElement('.form-primary .group--i18n')
	, languageControl
	, nativeControl

	if (!group) return

	languageControl = group.getElement('[name="language"]')
	nativeControl = group.getElement('[name="nativeid"]')

	if (languageControl && nativeControl)
	{
		function checkLanguage()
		{
			var value = languageControl.get('value')
			, native = languageControl.get('data-native-language')

			nativeControl.getParent('.control-group')[(!value || value == native) ? 'addClass' : 'removeClass']('hidden')
		}

		languageControl.addEvent('change', checkLanguage)

		checkLanguage()
	}
})
