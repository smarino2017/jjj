<?php
/**
 * Plugin Name: SandS Logger
 * Plugin URI: http://sandsmedia.com
 * Description: Manage logs as we like
 * Version: 0.1
 * Author: S&S Media, Robert and Beppe
 * Author URI: http://sandsmedia.com
 * Requires at least: 4.5
 * Tested up to: 4.5
 *
 * Text Domain: sands-logger
 * Domain Path: /i18n/languages/
 *
 * @author S&S Media
 */
define( 'SANDS_LOGGER', __FILE__ );
include_once (dirname ( SANDS_LOGGER ) . DIRECTORY_SEPARATOR . 'includes/backend.php');
require_once (ABSPATH . 'wp-admin/includes/file.php');
class SandsLogger {
	private $_logService;
	private $_logUrl;
	private $_logUsername;
	private $_logPassword;
	private $_logPublicKey;
	private $_logPrivateKey;
	private $_logDirectory = 'sands_logger';
	protected $_logEmail;
	protected $_logRetainFilePeriod;
	protected $_logTargetFile;
	
	
	protected function getLogEmail() {
		if (! $this->_logEmail) {
			$this->_logEmail = get_option ( 'sands-logger-email', 'rmunsky@sandsmedia.com' );
		}
		return $this->_logEmail;
	}
	protected function setLogEmail($_logEmail) {
		$this->_logEmail = $_logEmail;
		return $this;
	}
	protected function getLogRetainFilePeriod() {
		if (! $this->_logRetainFilePeriod) {
			$this->_logRetainFilePeriod = get_option ( 'sands-logger-retain-log-files', '7' );
		}
		return $this->_logRetainFilePeriod;
	}
	protected function setLogRetainFilePeriod($_logRetainFilePeriod) {
		$this->_logRetainFilePeriod = $_logRetainFilePeriod;
		return $this;
	}
	private function _connectToLogService() {
		// get the log credential
		// return the connection
		// for now to nothing
		return true;
	}
	private function _prepareTheMessage($message, $priority, $identifier) {
		$access = date ( "Y/m/d H:i:s" );
		// normalize if object or array
		if (isset ( $message ) && ! empty ( $message )) {
			if (is_object ( $message ) || is_array ( $message )) {
				$message = print_r ( $message, true );
			}
		}
		if (! $identifier) {
			$identifier = 'not specified';
		}
		$message = $access . ' - ' . $priority . ' - ' . $identifier . ' - ' . $message . ' ' . PHP_EOL;
		return $message;
	}
	private function _sendLogViaMail($message, $priority, $identifier) {
		try {
			$headers = 'From: Sands Logger <webdev@sandsmedia.com>' . "\r\n";
			wp_mail ( $this->getLogEmail (), $priority . ' - ' . $_SERVER ['SERVER_NAME'] . ' - ' . $identifier, $message, $headers );
		} catch ( Exception $e ) {
			error_log ( $e->getMessage () );
		}
	}
	public function sendLogViaLogentries($message) {
		$shallWeUseTheConsole= get_option('sands-logger-activate-le-logging', false);
		if ($shallWeUseTheConsole) {
				require_once dirname(__FILE__) . '/includes/le/le_php-master/logentries.php';
				$token = get_option( 'sands-logger-logentries-token' );
				if(isset($token) && !empty($token) && is_string($token)){
					putenv('LOGENTRIES_TOKEN='.$token);
					$ENV_TOKEN = getenv('LOGENTRIES_TOKEN');
					$log = LeLogger::getLogger($ENV_TOKEN, $Persistent, $SSL, $Severity, $DATAHUB_ENABLED, $DATAHUB_IP_ADDRESS, $DATAHUB_PORT, $HOST_ID, $HOST_NAME, $HOST_NAME_ENABLED);
					$log->Info($message);
				}
		}
		return true;
	}
	private function _sendLogViaCloudwatch($message) {
		//TODO here you have to call the logentries object and bla bla
		return true;
	}
	private function _copyIntoField($message, $entity, $field) {
		//load field content
		$currentContent = get_field($field, $entity);
		//apppend the new message
		$currentContent .= $message;
		//update the field
		// we need to call wp_update_post otherwise the custom field will not be updated...
// 		$my_post = array (
// 				'ID' => $entity
// 		);
// 		wp_update_post ( $my_post );
		if(update_field($field, $currentContent, $entity)){
		//if(update_post_meta($entity, $field, $currentContent)){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * recursively create a long directory path
	 */
	private function _makeSandsLoggerDir($path) {
		if (! file_exists ( $path )) {
			mkdir ( $path, 0777, true );
		}
	}
	public function _cleanUpLogs() {
		$now = time ();
		if (! $this->_logRetainFilePeriod) {
			$this->_logRetainFilePeriod = get_option ( 'sands-logger-retain-log-files', 7 );
		}
		
		$interval = strtotime('-'.$this->_logRetainFilePeriod. ' days');//files older than 24hours
		$logDirPath = get_home_path () . $this->_logDirectory . DIRECTORY_SEPARATOR ;
		$deletionWorked = false;
		foreach (glob($logDirPath."*") as $file){
			//delete if older
			if (filemtime($file) <= $interval ){
				if(unlink($file)){
					$deletionWorked = true;
				}
			} 
		}
		return $deletionWorked; 
	}
	
	public function debug($message = null, $identifier = '', $options = array()) {
		$this->sandsLog($message, $priority = 'LOG_DEBUG', $identifier, $options);
	}
	
	public function info($message = null, $identifier = '', $options = array()) {
		$this->sandsLog($message, $priority = 'LOG_INFO', $identifier, $options);
	}
	
	public function warn($message = null, $identifier = '', $options = array()) {
		$this->sandsLog($message, $priority = 'LOG_WARNING', $identifier, $options);
	}
	
	public function error($message = null, $identifier = '', $options = array()) {
		$this->sandsLog($message, $priority = 'LOG_ERROR', $identifier, $options);
	}	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $message
	 * @param unknown_type $priority
	 * @param unknown_type $identifier
	 * @param unknown_type $options
	 * @deprecated please use debug, warn, info or error instead
	 */
	public function sandsLog($message = null, $priority = 'LOG_DEBUG', $identifier = '', $options = array()) {
		if ($message == null) {
			error_log('SANDS LOGGER: You cannot send an empty message in the sands logger');
// 			debug_print_backtrace();
		}
		
		$message = $this->_prepareTheMessage ( $message, $priority, $identifier );
		
		// check if we have options and if we do something with that stuff
		$options = array_filter ( $options );
		if (! empty ( $options )) {
			
			// VIA MAIL?
			if (isset ( $options ['sendMail'] ) && ! empty ( $options ['sendMail'] ) && $options ['sendMail'] === 'true') {
				$this->_sendLogViaMail ( $message, $priority, $identifier );
			}
			
			// via LOGENTRIES?
			if (isset ( $options ['logEntries'] ) && ! empty ( $options ['logEntries'] ) && $options ['logEntries'] === 'true') {
				$this->_sendLogViaLogentries ( $message );
			}
			
			// via AMAZON CLOUD WATCH
			if (isset ( $options ['cloudWatch'] ) && ! empty ( $options ['cloudWatch'] ) && $options ['cloudWatch'] === 'true') {
				$this->_sendLogViaCloudwatch ( $message );
			}
			
			// copy into a custom field (useful for the order history)
			if (isset ( $options ['intoField'] ) && ! empty ( $options ['intoField'] )) {
				if(! empty ( $options ['intoField']['entity'] ) && ! empty ( $options ['intoField']['field'] )){
					if(!$this->_copyIntoField ( $message, $options ['intoField']['entity'], $options ['intoField']['field'])){
						$shallWeUseTheConsole= get_option('sands-logger-console-errors', false);
						if ($shallWeUseTheConsole) {
							error_log('SANDS LOGGER: not able to update logs in field '.$options ['intoField']['field'].' for entity '.$options ['intoField']['entity']);
						} 
					}
				}
			}
		}else{
			//TODO: force logs according to what in the settings, like if there are no options set and in the setting there's a force on cloudwatch, then always send to cloudwatch
		}
		
		//TODO: think about this http://php.net/manual/en/function.set-error-handler.php is it the case to take all inside this plugin? Maybe really not...
		
		// store it always locally and always fires an error_log (so we don't loose anything)
		try {
			$target = $this->getLogFileTarget();
			$today = date ( "j.n.Y" );
			$logDirectory = 'sands_logger';
			file_put_contents ( $target, $message, FILE_APPEND );
			$link = get_home_path () . $logDirectory . DIRECTORY_SEPARATOR . 'sands_logger_current';
			if (! is_link ( $link )) {
				symlink ( $target, $link );
			}else{
				if ($today != basename(readlink($link))) {
					unlink ( $link );
					symlink ( $target, $link );
				} else {
					//there's no real need for this
					//symlink ( $target, $link );
				}
			}
			$shallWeUseTheConsole= get_option('sands-logger-console-errors', false);
			if ($shallWeUseTheConsole) {
				error_log ( 'SANDS LOGGER: ' . $message );
			} 
		} catch ( Exception $e ) {
			$shallWeUseTheConsole= get_option('sands-logger-console-errors', false);
			if ($shallWeUseTheConsole) {
				error_log ( 'SANDS LOGGER: ' . $e->getMessage () );
			} 
		}
	}
	
	public function getLogFileTarget (){
		$today = date ( "j.n.Y" );
		$logDirectory = $this->_logDirectory;
		$this->_makeSandsLoggerDir ( $logDirectory );
		$target = get_home_path () . $logDirectory . DIRECTORY_SEPARATOR . $today.'.log';
		$this->_logTargetFile = $target;
		return $this->_logTargetFile;
	}
}


