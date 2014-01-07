'use strict';

var THEME_OPTIONS_AJAX = (function (app, $, window) {

	var _fields, _button, _spinner, _message;

	var _assign = function(){
		_fields = $('#toa-fields input');
		_button = $('#toa-button');
		_spinner = $('#toa-spinner');
		_message = $('#toa-message');
	};

	var _handlers = function(){
		_button.on('click', function toaSaveButton(){
			_spinner.show();
			jQuery.post(
			  ajaxurl, 
			  {
			    'action': 'ajax_action',
			    'fields': $.map(_fields, function ajaxMap(input){
			    	var $input = $(input);
			    	return {
			    		key: $input.attr('id'),
			    		value: $input.val()
			    	}
			    })
			  }, 
			  function(response){
			  	response = JSON.parse(response);

			  	_spinner.hide();

			  	if(response.code === 200){
			  		_message.show();	    		
			  	} 
			  }
			);
		});
	};

	app.init = function(){
		_assign();
		_handlers();
	}

	return app;
}(THEME_OPTIONS_AJAX || {}, jQuery, window));

jQuery(document).ready(THEME_OPTIONS_AJAX.init);