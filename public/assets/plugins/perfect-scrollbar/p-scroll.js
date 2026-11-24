(function($) {
	"use strict";
	
	//P-scrolling
	
	
	['.chat-scroll', '.Notification-scroll'].forEach(function (selector) {
	  const element = document.querySelector(selector);
	  if (element) {
		new PerfectScrollbar(element, {
		  useBothWheelAxes: true,
		  suppressScrollX: true,
		});
	  }
	});
	
	
	
})(jQuery);
