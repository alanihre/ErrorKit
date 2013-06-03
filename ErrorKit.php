<?php

require_once('installation.php');

define('EKError', E_USER_ERROR);
define('EKWarning', E_USER_WARNING);
define('EKNotice', E_USER_NOTICE);

@ini_set('display_errors', 0);

/**
 * EKTriggerError function.
 *
 * @access public
 * @param string $error_message (default: default_error_message)
 * @param int $error_type (default: EKError)
 * @param string $HTMLToPrint (default: NULL)
 * @param bool $shouldExit (default: true)
 * @return void
 */
function EKTriggerError($error_message=default_error_message, $error_type=EKError, $debugInfo=NULL, $HTMLToPrint=NULL,$shouldExit=true){
	switch ($error_type){
	case EKError:{
			$backtrace = debug_backtrace();
			$errfile = $backtrace[count($backtrace)-1]['file'];
			$errline = $backtrace[count($backtrace)-1]['line'];
			EKErrorHandler(EKError, $error_message, $errfile, $errline, NULL, $debugInfo);
			break;
		}
	case EKWarning:{
			$backtrace = debug_backtrace();
			$errfile = $backtrace[count($backtrace)-1]['file'];
			$errline = $backtrace[count($backtrace)-1]['line'];
			EKErrorHandler(EKWarning, $error_message, $errfile, $errline, NULL, $debugInfo);
			break;
		}
	case EKNotice:{
			$backtrace = debug_backtrace();
			$errfile = $backtrace[count($backtrace)-1]['file'];
			$errline = $backtrace[count($backtrace)-1]['line'];
			EKErrorHandler(EKNotice, $error_message, $errfile, $errline, NULL, $debugInfo);
			break;
		}
	default:{
			EKCustomErrorHandler($error_message, $error_type, $debugInfo, $HTMLToPrint, $shouldExit);
			break;
		}
	}
}

function InternalDatabaseError($mysqlError){
	if(debug_mode == true){
		$errorString = 'Database error: ' . $mysqlError;
		die(str_replace('%error_message%',$errorString,error_html));
	}else
		die(str_replace('%error_message%',default_error_message,error_html));
}

function EKConnectToDatabase(){
if(log_errors_to_database == true){
	$dsn = db_type.':dbname='.db_name.';host='.db_host;
	try{
		$ErrorKitDatabase = new PDO($dsn, db_user, db_password);
		$ErrorKitDatabase->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
		return $ErrorKitDatabase;
	}catch (PDOException $e){
		InternalDatabaseError($e);
		return false;
	}
}
}
$ErrorKitDatabase = EKConnectToDatabase();

$errorArray = array();
function EKScriptShutdown(){
	global $errorArray;
	$errorsString = '';
	if(count($errorArray) == 0)
		return;
	if(count($errorArray) == 1){
		if($errorArray[0][1] == EKWarning)
			$outputErrorString = str_replace('%warning_message%', $errorArray[0][0], warning_html);
		elseif($errorArray[0][1] == EKNotice)
			$outputErrorString = str_replace('%notice_message%', $errorArray[0][0], notice_html);
		else
			$outputErrorString = str_replace('%error_message%', $errorArray[0][0], error_html);
		echo $outputErrorString;
	}else{
		for($i=0;$i<count($errorArray);$i++){
			if($i == 0){
				$errorsString .= $errorArray[0][0];
			}else{
				$errorsString .= divider_html . $errorArray[$i][0];
			}
		}
		$outputErrorString = str_replace('%error_messages%', $errorsString, multiple_errors_html);
		echo $outputErrorString;
	}
}

function EKDisplayError($error_message, $error_type=EKError, $error_line=NULL, $error_file=NULL, $debugInfo=NULL, $function=NULL){
	global $errorArray, $ignore_showing_errors;
	if(in_array($error_type, $ignore_showing_errors)){
		return false;
	}
	if(debug_mode == true){
		if($function != NULL){
			if($debugInfo != NULL){
				$error_message = $error_message . '<br><strong>Debug info:</strong><br>Error: ' . $debugInfo . '<br>Line: ' . $error_line . '<br>File: ' . $error_file . '<br>Function: ' . $function;
				$errstr = $debugInfo;
			}else{
				$error_message = $error_message . '<br><strong>Debug info:</strong><br>Error: ' . $error_message . '<br>Line: ' . $error_line . '<br>File: ' . $error_file . '<br>Function: ' . $function;
			}
		}else{
			if($debugInfo != NULL){
				$error_message = $error_message . '<br><strong>Debug info:</strong><br>Error: ' . $debugInfo . '<br>Line: ' . $error_line . '<br>File: ' . $error_file;
				$errstr = $debugInfo;
			}else{
				$error_message = $error_message . '<br><strong>Debug info:</strong><br>Error: ' . $error_message . '<br>Line: ' . $error_line . '<br>File: ' . $error_file;
			}
		}
	}
	$error = array($error_message, $error_type);
	array_push($errorArray, $error);
}

function EKLogToDatabase($errno, $errstr, $errfile, $errline, $function=NULL){
	global $ErrorKitDatabase, $ignore_logging_errors;
	if(log_errors_to_database == false)
		return false;
	if(in_array($errno, $ignore_logging_errors))
		return false;
	if(ignore_logging_when_debuging == true && debug_mode == true)
		return false;
	
	$unixtime = strtotime("now");
			
	$ErrorKitDatabase->beginTransaction();

	if(isset($function) && $function != NULL){
		$query = $ErrorKitDatabase->prepare("INSERT INTO `EKErrors` (`errorno`, `errorstring`, `errorfile`, `errorline`, `unixtime`, `function`) VALUES (?, ?, ?, ?, ?, ?)");
	}else{
		$query = $ErrorKitDatabase->prepare("INSERT INTO `EKErrors` (`errorno`, `errorstring`, `errorfile`, `errorline`, `unixtime`) VALUES (?, ?, ?, ?, ?)");
	}
	
	if(!$query){
		$errorInfo =  $ErrorKitDatabase->errorInfo();
		InternalDatabaseError($errorInfo[2]);
	}
	
	if(isset($function) && $function != NULL){
		$query->bindValue(1, $errno, PDO::PARAM_INT);
		$query->bindValue(2, $errstr, PDO::PARAM_STR);
		$query->bindValue(3, $errfile, PDO::PARAM_STR);
		$query->bindValue(4, $errline, PDO::PARAM_INT);
		$query->bindValue(5, $unixtime, PDO::PARAM_INT);
		$query->bindValue(6, $function, PDO::PARAM_STR);
	}else{
		$query->bindValue(1, $errno, PDO::PARAM_INT);
		$query->bindValue(2, $errstr, PDO::PARAM_STR);
		$query->bindValue(3, $errfile, PDO::PARAM_STR);
		$query->bindValue(4, $errline, PDO::PARAM_INT);
		$query->bindValue(5, $unixtime, PDO::PARAM_INT);
	}
	$query->execute();
	$fetch = $query->fetch();
	$ErrorKitDatabase->commit();
}

function EKCustomErrorHandler($error_message, $error_type, $debugInfo=NULL, $HTMLToPrint = NULL, $shouldExit = true){
	global $ignore_functions;
	if($error_type == 0){
		if(debug_mode == true){
			$errorString = 'Error type can not be zero(0)!';
			die(str_replace('%error_message%',$errorString,error_html));
		}else{
			die(str_replace('%error_message%',default_error_message,error_html));
		}
	}
	if($HTMLToPrint != NULL)
		if($debugInfo != NULL && debug_mode == true)
			$HTMLToPrint = str_replace('%debug_info%', $debugInfo, $HTMLToPrint);
		else
			$HTMLToPrint = str_replace('%debug_info%', "", $HTMLToPrint);
		echo str_replace('%message%', $error_message, $HTMLToPrint);

	$backtrace = debug_backtrace();
	$errfile = $backtrace[count($backtrace)-1]['file'];
	$errline = $backtrace[count($backtrace)-1]['line'];
	$function = NULL;

	if(count($backtrace) > 2){
		for ($trace = 2; $trace < count($backtrace); $trace++) {
			if(in_array($backtrace[$trace]['function'], $ignore_functions)){
				return false;
			}
		}
		$function = $backtrace[count($backtrace)-1]['function'];
	}
	EKLogToDatabase($error_type, $error_message, $errfile, $errline, $function);
	if($shouldExit == true)
		die();
}

function EKErrorHandler($errno, $errstr, $errfile, $errline, $errcontext, $debugInfo=NULL){
	global $ignore_functions;
	if(error_reporting() == 0)
		return false;
	$backtrace = debug_backtrace();
	$function = NULL;

	if(count($backtrace) > 2){
		for ($trace = 2; $trace < count($backtrace); $trace++) {
			if(in_array($backtrace[$trace]['function'], $ignore_functions)){
				return false;
			}
		}
		$function = $backtrace[count($backtrace)-1]['function'];
	}
	switch ($errno){
	case EKError:{
			EKDisplayError($errstr, EKError, $errline, $errfile, $debugInfo, $function);
			break;
		}
	case EKWarning:{
			EKDisplayError($errstr, EKWarning, $errline, $errfile, $debugInfo, $function);
			break;
		}
	case EKNotice:{
			EKDisplayError($errstr, EKNotice, $errline, $errfile, $debugInfo, $function);
			break;
		}
	default:{
			EKDisplayError(default_error_message, $errno, $errline, $errfile, $errstr, $function);
			break;
		}
	}
	if($debugInfo != NULL)
		$errstr = $debugInfo;

	EKLogToDatabase($errno, $errstr, $errfile, $errline, $function);

	if(debug_mode == true){
		if($errno == E_ERROR || $errno == E_CORE_ERROR || $errno == E_USER_ERROR || $errno == E_RECOVERABLE_ERROR)
			die();
	}else{
		if($errno == E_ERROR || $errno == E_PARSE || $errno == E_CORE_ERROR || $errno == E_COMPILE_ERROR || $errno == E_USER_ERROR || $errno == E_RECOVERABLE_ERROR)
			die();
	}

	return true;
}

function EKExceptionHandler($exception){
	global $ignore_functions;
	$errstr = $exception->getMessage();
	$backtrace = debug_backtrace();
	$function = NULL;
	if(count($backtrace) > 2){
		for ($trace = 2; $trace < count($backtrace); $trace++) {
			if(in_array($backtrace[$trace]['function'], $ignore_functions)){
				return false;
			}
		}
		$function = $backtrace[count($backtrace)-1]['function'];
	}
	$errfile = $backtrace[count($backtrace)-1]['file'];
	$errline = $backtrace[count($backtrace)-1]['line'];
	EKDisplayError(default_error_message, NULL, $errline, $errfile, $errstr, $function);

	EKLogToDatabase(0, $errstr, $errfile, $errline, $function);
	return true;
}

//Register handlers
register_shutdown_function('EKScriptShutdown');
set_exception_handler('EKExceptionHandler');
set_error_handler("EKErrorHandler");

?>