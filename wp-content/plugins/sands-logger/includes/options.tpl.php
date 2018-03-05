
<style>
fieldset {
	margin: 1em;
	padding: 1em;
	border: 8px solid #fff;
}
</style>
<div class="wrap">
	<h2>Sands Logger configuration</h2>
	<form action="options.php" method="post">
<?php settings_fields('sands-logger');
$shallWeUseTheConsole = get_option( 'sands-logger-console-errors' );
$shallWeActivateTheLogEntriesLogging = get_option( 'sands-logger-activate-le-logging' );
$logentriesToken = get_option( 'sands-logger-logentries-token' );
?>
    <div class="description">Generic logger settings.</div>
		<fieldset>
			<table class="form-table">
				<tr valign="top">
					<td>
						<h3>Send log mails to:</h3> <input type="text"
						name="sands-logger-email"
						value="<?php echo esc_attr( $this->getLogEmail() ); ?>" />
					</td>
					<td>
						<h3>Retain Log files for (days):</h3> <input type="text"
						name="sands-logger-retain-log-files"
						value="<?php echo esc_attr( $this->getLogRetainFilePeriod() ); ?>" />
					</td>
				</tr>
				<tr valign="top">
					<td>
						here we need to put the specific configuration for logentries and cloudwatch
					</td>
				</tr>
				<tr valign="top">
						<th scope="row">Display the messages in the error console</th>
						<td>
						<?php printf ( '<input value="selected" type="checkbox" id="sands-logger-console-errors" name="sands-logger-console-errors" %s />', ((isset ( $shallWeUseTheConsole ) && (!empty($shallWeUseTheConsole)) )) ? "checked" : '' ); ?>
						</td>
				</tr>
				<tr valign="top">
						<th scope="row">Enable LogEntriesLogging</th>
						<td>
						<?php printf ( '<input value="selected" type="checkbox" id="sands-logger-activate-le-logging" name="sands-logger-activate-le-logging" %s />', ((isset ( $shallWeActivateTheLogEntriesLogging) && (!empty($shallWeActivateTheLogEntriesLogging)) )) ? "checked" : '' ); ?>
						</td>
				</tr>
				<tr valign="top">
					<td>
						<h3>LogEntries Token:</h3> <input type="text"
						name="sands-logger-logentries-token"
						value="<?php echo esc_attr( $logentriesToken); ?>" />
					</td>
				</tr>
			</table>
		</fieldset>
    <?php submit_button();?>
  </form>
</div>