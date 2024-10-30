(function ( $ ) {
	'use strict';
	$(document).ready(function () {
		$(".jlt-addon-not-installed").click(function () {
			var current_event = $(this);
			$.ajax({
				url    : JLT_Admin_JS.ajax_url,
				type   : "post",
				data   : {
					action  : "jlt_install_plugin",
					security: JLT_Admin_JS.ajax_jlt_addons_security,
					plugin  : current_event.data('plugin')
				},
				success: function ( status ) {
					switch ( status ) {
						case "0":
							console.log("Something Went Wrong");
							break;
						case "1":
							location.reload();
							break;
						case "-1":
							console.log("Nonce missing")
					}
				},
				error  : function ( err ) {
					console.log("Something Went Wrong");
				}
			})
		})

		$(".jlt-addon-not-activated").click(function () {
			var current_event = $(this);
			$.ajax({
				url    : JLT_Admin_JS.ajax_url,
				type   : "post",
				data   : {
					action  : "jlt_activate_plugin",
					security: JLT_Admin_JS.ajax_jlt_addons_security,
					plugin  : current_event.data('plugin')
				},
				success: function ( status ) {
					switch ( status ) {
						case "0":
							console.log("Something Went Wrong");
							break;
						case "1":
							console.log("Plugin activated");
							location.reload();
							break;
						case "-1":
							console.log("Nonce missing")
					}
				},
				error  : function ( err ) {
					console.log("Something Went Wrong");
				}
			});
		});

		$(".jlt-dash-deactivate-addon").click(function () {
			var current_event = $(this);
			$.ajax({
				url    : JLT_Admin_JS.ajax_url,
				type   : "post",
				data   : {
					action  : "jlt_deactivate_plugin",
					security: JLT_Admin_JS.ajax_jlt_addons_security,
					plugin  : current_event.data('plugin')
				},
				success: function ( status ) {
					switch ( status ) {
						case "0":
							console.log("Something Went Wrong");
							break;
						case "1":
							console.log("Plugin deactivated");
							location.reload();
							break;
						case "-1":
							console.log("Nonce missing")
					}
				},
				error  : function ( err ) {
					console.log("Something Went Wrong");
				}
			});
		})
	});
})(jQuery);