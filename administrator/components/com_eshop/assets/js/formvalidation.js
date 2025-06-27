(function (document, Joomla) {
	Joomla.submitbutton = function (pressbutton) {
		var form = document.adminForm;
		var cancelTask = Joomla.getOptions('cancelTask', '');
		if (pressbutton === cancelTask) {
			Joomla.submitform(pressbutton, form);
		} else if(document.formvalidator.isValid(form)) {
			Joomla.submitform(pressbutton, form);
		}
	};
})(document, Joomla);