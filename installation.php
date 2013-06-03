<?php
/*
Thank you for downloading ErrorKit!

Here follows a simple guide to install ErrorKit on you server within minutes.

1: Database (if you don't want to log your errors into a database skip this step but remember to set log_errors_to_database to false on line 19)
	1.1: Database credentials
		Please enter the required information for your database:
*/

define('db_type',''); //The database type, for example mysql or odbc
define('db_host',''); //The database host
define('db_user', ''); // The username for the database
define('db_password', ''); //The password for the database
define('db_name', ''); //The name of the database

/*
	1.2: Table setup
		Use the following SQL to create the necessary tables for ErrorKIt

		CREATE TABLE IF NOT EXISTS `EKErrors` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`errorno` int(20) NOT NULL,
		`errorstring` longtext NOT NULL,
		`errorfile` varchar(10000),
		`errorline` int(10),
		`function` varchar(1000),
		`unixtime` int(100),
		PRIMARY KEY (`id`)
		);
*/

/*
2: Settings
	2.1: Debug mode
*/
define('debug_mode',true); //If debug_mode is true addititional debug information will be displayed to the user
define('ignore_logging_when_debuging', false); //Set this to false if you don't want ErrorKit to log errors to the database while debugging which can be useful if a lot of errors encounters when developing

/*
	2.2: Error logging
*/
define('log_errors_to_database', true); //If you don't want to log any errors at all to the database you should set this constant to false

/*
	2.2: Ignore errors
		This can be useful if a certain error occurs when the program is actually working but PHP is giving you a notice that something could go wrong. Error code 8 for example will be called if only a GET-parameter is missing which sometimes can be irrelevant for the user
*/
$ignore_showing_errors = array(8); //Specify the errors that you don't want ErrorKit to display to the user. All errors will however be displayed in debug mode

$ignore_logging_errors = array(8); //Specify the errors that you don't want ErrorKit to log to the database

/*
	2.3: Ignore functions
		If you want disable ErrorKit for certain functions.
*/
$ignore_functions = array(); //Functions which ErrorKit will ignore.

/*
3: Error HTML(optional)
This is the HTML that is displayd to the user when ErrorKit detects an error
	3.1: Default Error Mesage
*/
define('default_error_message','An error occurred.');

/*
	3.2: Error HTML
		This will be diplayed when an internal server error occurs or when EKTriggerError is called with a standard error code(EKError). %error_message% will be replaced with the error message
*/
define('error_html', '<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap-combined.min.css" rel="stylesheet"><script src="http://code.jquery.com/jquery-latest.js"></script><script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js"></script><div class="modal hide fade"  id="errormodal" ><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3>An error has occurred</h3></div><div class="modal-body"><p>%error_message%</p></div><div class="modal-footer"><a href="#" data-dismiss="modal" aria-hidden="true" class="btn">Close</a></div></div><script type="text/javascript">$("#errormodal").modal("show");</script>');

/*
	3.3: Warning HTML
		This will be diplayed when an warning occurs or when EKTriggerError is called with a standard warning code(EKWarning). %warning_message% will be replaced with the warning message
*/
define('warning_html', '<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap-combined.min.css" rel="stylesheet"><script src="http://code.jquery.com/jquery-latest.js"></script><script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js"></script><div class="modal hide fade"  id="errormodal" ><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3>An error has occurred</h3></div><div class="modal-body"><p>%warning_message%</p></div><div class="modal-footer"><a href="#" data-dismiss="modal" aria-hidden="true" class="btn">Close</a></div></div><script type="text/javascript">$("#errormodal").modal("show");</script>');

/*
	3.4: Notice HTML
		This will be diplayed when an notice occurs or when EKTriggerError is called with a standard notice code(EKNotice). %notice_message% will be replaced with the notice message
*/
define('notice_html', '<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap-combined.min.css" rel="stylesheet"><script src="http://code.jquery.com/jquery-latest.js"></script><script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js"></script><div class="modal hide fade"  id="errormodal" ><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3>An error has occurred</h3></div><div class="modal-body"><p>%notice_message%</p></div><div class="modal-footer"><a href="#" data-dismiss="modal" aria-hidden="true" class="btn">Close</a></div></div><script type="text/javascript">$("#errormodal").modal("show");</script>');

/*
	3.4: Mutiple error HTML
		This will be diplayed when multiple errors occurs which will be displayed together when the script terminates. %error_messages% will be replaced with error messages
*/
define('multiple_errors_html','<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap-combined.min.css" rel="stylesheet"><script src="http://code.jquery.com/jquery-latest.js"></script><script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js"></script><div class="modal hide fade"  id="errormodal" ><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3>Errors have occurred</h3></div><div class="modal-body"><p>%error_messages%</p></div><div class="modal-footer"><a href="#" data-dismiss="modal" aria-hidden="true" class="btn">Close</a></div></div><script type="text/javascript">$("#errormodal").modal("show");</script>');

define('divider_html', '<hr noshade size="1">'); //The divider is used to separate the errors in the multiple_errors_html string when multiple errors occure

?>