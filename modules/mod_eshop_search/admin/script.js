window.addEvent("domready",function(){
	$$('.eshop_switch').each(function(el){
		el.setStyle('display','none');
		var style = (el.value == 1) ? 'on' : 'off';
		var eshop = new Element('div',{'class' : 'eshop-'+style});
		eshop.inject(el, 'after');
		eshop.addEvent("click", function(){
			if(el.value == 1){
				eshop.setProperty('class','eshop-off');
				el.value = 0;
			} else {
				eshop.setProperty('class','eshop-on');
				el.value = 1;
			}
		});
	});
});
