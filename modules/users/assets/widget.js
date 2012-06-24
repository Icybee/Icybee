
Brickrouge.Widget.Login = new Class({

	Extends: Brickrouge.Form,

	options: {

		useXHR: true
	},

	onSuccess: function(response)
	{
		if (response.location)
		{
			window.location = response.location
		}
		else
		{
			window.location.reload()
		}
	}
})

Brickrouge.Widget.NonceRequest = new Class({

	Extends: Brickrouge.Form,

	options: {

		useXHR: true
	}
})

Brickrouge.Widget.LoginCombo = new Class({

	initialize: function(el, options)
	{
		this.element = el = $(el)

		var forms = el.getElements('form')
		, login = forms[0]
		, nonce = forms[1]
		, loginSlide = new Fx.Slide(login, { duration: 'short', wrapper: login.getParent(), resetHeight: true })
		, nonceSlide = new Fx.Slide(nonce, { duration: 'short', wrapper: nonce.getParent(), resetHeight: true })
		, shake

		function nonceIn()
		{
			nonce.get('widget').clearAlert();

			loginSlide.slideOut().chain(nonceSlide.slideIn.bind(nonceSlide))

			return nonceSlide;
		};

		function nonceOut()
		{
			nonceSlide.slideOut().chain(loginSlide.slideIn.bind(loginSlide))

			return loginSlide
		};

		login.getElement('a').addEvent('click', function(ev) {

			ev.stop()

			nonceIn()
		})

		nonce.getElement('a').addEvent('click', function(ev) {

			ev.stop()

			nonceOut()
		})

		shake = (function (target, amplitude, duration)
		{
			target = $(target)
			target.setStyle('position', 'relative')

			var fx = new Fx.Tween(target, { property: 'left', duration: duration/5 })

			return function()
			{
				fx.start(-amplitude).chain
				(
					function () { this.start(amplitude) },
					function () { this.start(-amplitude) },
					function () { this.start(amplitude) },
					function () { this.start(0) }
				)
			}

		}) (el.getParent('shakable') || el, 50, 200)

		login.get('widget').addEvent('failure', shake)

		nonce.get('widget').addEvent('success', function() {

			nonceOut.delay(4000)
		})
	}
})
