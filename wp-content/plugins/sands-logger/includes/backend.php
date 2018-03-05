<?php
class SandsLoggerOptionsPage extends SandsLogger {
	function __construct() {
		if (is_admin ()) {
			add_action ( 'admin_menu', array (
					&$this,
					'admin_menu' 
			) );
			add_action ( 'admin_init', function () {
				$this->sands_logger_register_settings ();
			} );
			
		}
	}

	function sands_logger_register_settings() {
		register_setting ( 'sands-logger', 'sands-logger-active' );
		register_setting ( 'sands-logger', 'sands-logger-email' );
		register_setting ( 'sands-logger', 'sands-logger-retain-log-files' );
		register_setting ( 'sands-logger', 'sands-logger-service' );
		register_setting ( 'sands-logger', 'sands-logger-internal-url' );
		register_setting ( 'sands-logger', 'sands-logger-console-errors' );
		register_setting ( 'sands-logger', 'sands-logger-activate-le-logging' );
		register_setting ( 'sands-logger', 'sands-logger-logentries-token' );
	}
	function admin_menu() {
		add_menu_page ( 'Sands Logger Settings', 'Sands Logger', 'manage_options', __FILE__, array($this, 'settings_page'), plugins_url ( '..//images/icon.png', __FILE__ ) );
	}
	function settings_page() {
		if (! current_user_can ( 'manage_options' )) {
			wp_die ( __ ( 'You do not have sufficient permissions to access this page.' ) );
		}
		$this->_logService = get_option ( 'sands-logger-service', 'internal' );
		include ('options.tpl.php');
	}
}
new SandsLoggerOptionsPage;