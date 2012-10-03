var WdContentsEditor = new Class
({
	Implements: [ Options ],

	options:
	{
		contentsName: 'contents',
		selectorName: 'editor'
	},

	initialize: function(el, options)
	{
		this.element = $(el)
		this.setOptions(options)
		this.setOptions(this.element.get('dataset'))

		var selector = this.element.getFirst('.editor-options').getElement('select')

		if (selector)
		{
			selector.addEvent('change', function(ev) {

				this.change(ev.target.get('value'))

			}.bind(this))
		}

		this.form = this.element.getParent('form')
	},

	change: function(editor)
	{
		this.element.set('tween', { property: 'opacity', duration: 'short', link: 'cancel' })
		this.element.get('tween').start(.5)

		var op = new Request.Element ({

			url: '/api/editor/' + editor + '/change',
			onSuccess: this.handleResponse.bind(this)

		})

		var key = this.form['#key']
		, constructor = this.form['#destination'].value
		, textarea = this.element.getElement('textarea')

		op.get ({

			contents_name: this.options.contentsName,
			selector_name: this.options.selectorName,

			contents: textarea ? textarea.value : '',

			nid: key ? key.value : null,
			constructor: constructor

		})
	},

	handleResponse: function(el)
	{
		el.inject(this.element, 'after')

		this.element.destroy()

		this.initialize(el)

		document.fireEvent('editors')
		Brickrouge.updateDocument(el)
	}
});

window.addEvent('brickrouge.update', function() {

	$$('div.editor-wrapper').each(function(el) {

		if (el.retrieve('loader'))
		{
			return
		}

		var loader = new WdContentsEditor(el)

		el.store('loader', loader)
	})
})
