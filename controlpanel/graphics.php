<?php
header("Content-type: image/png");
require_once("../ErrorKit.php");

if(log_errors_to_database == false){
$im = @imagecreatetruecolor(300, 140)
		or EKTriggerError(default_error_message, EKError, "Cannot Initialize new GD image stream");
	$background_color = imagecolorallocate($im,
		255, 255, 255 //white
	);
		imagefill($im,0,0,$background_color);
	$black = imagecolorallocate($im,
		0, 0, 0 //white
	);
	imagettftext($im, 12, 0, 11, 21, $black, "Roboto-Regular.ttf", "Database is not activated,\nchange log_errors_to_database to true");

	imagepng($im);
	imagedestroy($im);
	}
if(isset($_GET['imagetype']))
	$imageType = $_GET['imagetype'];
else
	die();
switch($imageType){
case 'standardErrorGraph':{
		renderGraph("-" . 4 . " hours", "now", 10, 300, 140, true);
		break;
	}
case 'todayErrorGraph':{
		renderGraph("-" . date("s") . ' seconds -' . date("i") . ' minutes -' . date("G") . ' hours', "tomorrow", 30, 300, 140, true);
		break;
	}
}

function renderGraph($firstBound, $secondBound, $interval, $width, $height, $compareToYesterday=false, $queryStatements=NULL){
	global $ErrorKitDatabase;

	$firstTime = strtotime($firstBound);
	$lastTime = strtotime($secondBound);
	$range = ($lastTime-$firstTime)/3600;
	$timeInterval = $interval*60;
	$sortedArrayOfErrors = array_fill(0, (60*$range)/$interval, 0);

	$ErrorKitDatabase = EKConnectToDatabase();

	/*if($queryStatements != NULL)
		$queryResult = mysql_query("SELECT `unixtime` FROM `EKErrors` WHERE `unixtime`>=$firstTime AND `unixtime`<=$lastTime AND " . $queryStatements, $dbcon);
	else{*/
	$ErrorKitDatabase->beginTransaction();
	$query = $ErrorKitDatabase->prepare("SELECT `unixtime` FROM `EKErrors` WHERE `unixtime`>=? AND `unixtime`<=?;");

	if(!$query){
		$errorInfo =  $ErrorKitDatabase->errorInfo();
		InternalDatabaseError($errorInfo[2]);
	}

	$query->bindValue(1, $firstTime, PDO::PARAM_INT);
	$query->bindValue(2, $lastTime, PDO::PARAM_INT);

	$query->execute();
	$fetch = $query->fetchAll();
	$ErrorKitDatabase->commit();
	//}

	foreach($fetch as &$ftsc){
		$error = $ftsc['unixtime'];
		$previousTime = $firstTime;
		for($time=$firstTime+$timeInterval, $i=0; $time<=$lastTime;$time+=$timeInterval, $i++){
			if($error >= $previousTime && $error <= $time){
				$sortedArrayOfErrors[$i] = $sortedArrayOfErrors[$i]+1;
				break;
			}
			$previousTime = $time;
		}
	}
	unset($ftsc);

	$maxValue = 0;

	foreach($sortedArrayOfErrors as &$value){
		if($value>$maxValue)
			$maxValue = $value;
	}
	unset($value);

	if($compareToYesterday == true){
		$firstTimeYesterday = strtotime($firstBound . " -1 day");
		$lastTimeYesterday = strtotime($secondBound . " -1 day");
		$sortedArrayOfErrorsYesterday = array_fill(0, (60*$range)/$interval, 0);

		/*if($queryStatements != NULL)
			$queryResult = mysql_query("SELECT `unixtime` FROM `EKErrors` WHERE `unixtime`>=$firstTimeYesterday AND `unixtime`<=$lastTimeYesterday AND " . $queryStatements, $dbcon);
		else{*/
		$ErrorKitDatabase->beginTransaction();
		$query = $ErrorKitDatabase->prepare("SELECT `unixtime` FROM `EKErrors` WHERE `unixtime`>=? AND `unixtime`<=?");

		if(!$query){
			$errorInfo =  $ErrorKitDatabase->errorInfo();
			InternalDatabaseError($errorInfo[2]);
		}

		$query->bindValue(1, $firstTimeYesterday, PDO::PARAM_INT);
		$query->bindValue(2, $lastTimeYesterday, PDO::PARAM_INT);

		$query->execute();
		$fetch = $query->fetchAll();
		$ErrorKitDatabase->commit();

		//}
		foreach($fetch as &$ftsc){
			$error = $ftsc['unixtime'];
			$previousTime = $firstTimeYesterday;
			for($time=$firstTimeYesterday+$timeInterval, $i=0; $time<=$lastTimeYesterday;$time+=$timeInterval, $i++){
				if($error >= $previousTime && $error <= $time){
					$sortedArrayOfErrorsYesterday[$i] = $sortedArrayOfErrorsYesterday[$i]+1;
					break;
				}
				$previousTime = $time;
			}
		}
	unset($ftsc);

		$maxValueYesterday = 0;

		foreach($sortedArrayOfErrorsYesterday as &$value){
			if($value>$maxValueYesterday)
				$maxValueYesterday = $value;
		}
		unset($value);
		if($maxValue<$maxValueYesterday)
			$maxValue = $maxValueYesterday;
	}



	$im = @imagecreatetruecolor($width, $height)
		or EKTriggerError(default_error_message, EKError, "Cannot Initialize new GD image stream");
	$background_color = imagecolorallocate($im,
		255, 255, 255 //white
	);
	imagefill($im,0,0,$background_color);
	$height -=1;

	imageantialias($im, true);
	$black = imagecolorallocate($im, 0, 0, 0);

	imagesetthickness($im, 1);

	for($line=1;$line<6;$line++){
		$gray = imagecolorallocate($im, 220, 220, 220);

		imageline($im,
			1, $height-($line*($height/6)), // first point coordinates
			$width,$height-($line*($height/6)), // last point coordinates
			$gray);
	}
	imagesetthickness($im, 2);

	if($maxValue == 0)
		$pxval = 1;
	else
		$pxval = $maxValue/$height;

	$extraInterval = $interval/(($range*60)/$interval);
	$interval += $extraInterval;

	if($compareToYesterday == true){
		$orange = imagecolorallocate($im, 255, 165, 0);
		for($i=1, $prev=0; $i<count($sortedArrayOfErrorsYesterday);$i++, $prev++){
			$prevx = ($width*$prev*$interval)/(60*$range);
			$prevy = $height-($sortedArrayOfErrorsYesterday[$prev]/$pxval);
			$ix = ($width*$i*$interval)/(60*$range);
			$iy = $height-($sortedArrayOfErrorsYesterday[$i]/$pxval);

			imageline($im,
				$prevx, $prevy, // first point coordinates
				$ix,$iy, // last point coordinates
				$orange);
		}

		$prev = count($sortedArrayOfErrorsYesterday)-2;
		$i = count($sortedArrayOfErrorsYesterday)-1;
		$prevx = ($width*$prev*$interval)/(60*$range);
		$prevy = $height-($sortedArrayOfErrorsYesterday[$prev]/$pxval);
		$ix = ($width*$i*$interval)/(60*$range);
		$iy = $height-($sortedArrayOfErrorsYesterday[$i]/$pxval);

		imageline($im,
			$prevx, $prevy, // first point coordinates
			$ix,$iy, // last point coordinates
			$orange);
	}


	$blue = imagecolorallocate($im, 0, 0, 128);
	for($i=1, $prev=0; $i<count($sortedArrayOfErrors);$i++, $prev++){
		$prevx = ($width*$prev*$interval)/(60*$range);
		$prevy = $height-($sortedArrayOfErrors[$prev]/$pxval);
		$ix = ($width*$i*$interval)/(60*$range);
		$iy = $height-($sortedArrayOfErrors[$i]/$pxval);

		imageline($im,
			$prevx, $prevy, // first point coordinates
			$ix,$iy, // last point coordinates
			$blue);
	}

	$prev = count($sortedArrayOfErrors)-2;
	$i = count($sortedArrayOfErrors)-1;
	$prevx = ($width*$prev*$interval)/(60*$range);
	$prevy = $height-($sortedArrayOfErrors[$prev]/$pxval);
	$ix = ($width*$i*$interval)/(60*$range);
	$iy = $height-($sortedArrayOfErrors[$i]/$pxval);

	imageline($im,
		$prevx, $prevy, // first point coordinates
		$ix,$iy, // last point coordinates
		$blue);
	imagesetthickness($im, 2);
	imageline($im,
		0, 0, // first point coordinates
		0,$height, // last point coordinates
		$black);

	imageline($im,
		0, $height, // first point coordinates
		$width,$height, // last point coordinates
		$black);
	imagefilter($im, IMG_FILTER_SMOOTH, 15);
	imagepng($im);
	imagedestroy($im);
}

?>