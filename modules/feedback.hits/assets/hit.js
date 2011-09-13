window.addEvent
(
	'load', function()
	{
		if (!feedback_hits_nid)
		{
			return;
		}

		var op = new Request.API({ url: 'feedback.hits/' + feedback_hits_nid + '/hit' });

		op.send();
	}
);