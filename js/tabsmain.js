function init(){
		
		var stretchers = document.getElementsByClassName('stretcher'); //div that stretches
		var toggles = document.getElementsByClassName('tab'); //h3s where I click on

		//accordion effect
		var myAccordion = new fx.Accordion(
			toggles, stretchers, {opacity: true, height: true, duration: 400}
		);

		//hash functions
		var found = false;
		toggles.each(function(h3, i){
			var div = Element.find(h3, 'nextSibling'); //element.find is located in prototype.lite
			if (window.location.href.indexOf(h3.title) > 0) {
				myAccordion.showThisHideOpen(div);
				found = true;
			}
		});
		if (!found) myAccordion.showThisHideOpen(stretchers[0]);
	}