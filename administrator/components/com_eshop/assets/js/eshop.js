if("undefined"===typeof Eshop) {
	var Eshop={};	
}
Eshop.updateStateList = function(countryId, stateInputId) {	
	// First of all, we need to empty the state dropdown	
	var list = document.getElementById(stateInputId);
	// empty the list
	for (i = 1; i < list.options.length; i++) {
		list.options[i] = null;
	}
	list.length = 1;
	var stateNames = stateList[countryId];
	if (stateNames) {
		var arrStates = stateNames.split(',');
		i = 0;
		var state = '';
		var stateName = '';
		for ( var j = 0; j < arrStates.length; j++) {
			state = arrStates[j];
			stateName = state.split(':');
			opt = new Option();
			opt.value = stateName[0];
			opt.text = stateName[1];
			list.options[i++] = opt;
		}
		list.length = i;
	}
}
