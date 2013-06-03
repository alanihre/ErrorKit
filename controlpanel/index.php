<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
    <title>ErrorKit Control Panel</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap-combined.min.css" rel="stylesheet"><script src="http://code.jquery.com/jquery-latest.js"></script><script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js"></script>

    
</script>
<?php
include('../ErrorKit.php');
?>
    <style type="text/css">
body { padding-top: 60px; padding-bottom: 40px; }
    </style>
    <script type="text/javascript">
    function loadGraph(elementid, type){
     var img = new Image();
        $(img).load(function () {
            $(this).hide();
            $('#'+elementid).removeClass('loading').html(this).addClass('img-polaroid').width('270px');
            $(this).fadeIn();
            img.style.height = '140px';
        }).error(function () {
            // notify the user that the image could not be loaded
        }).attr('src', 'graphics.php?imagetype='+type);
    }
    loadGraph("errorgraph4", "standardErrorGraph");
    loadGraph("errorgraphtoday", "todayErrorGraph");
    
    </script>
</head>

<body>
    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"></a> <a class="brand" href="#">ErrorKit</a>

                <div class="nav-collapse collapse">
                    <ul class="nav">
                        <li class="active"><a href="./">Home</a></li>

                        <li><a href="errors.php">Errors</a></li>

                        <li><a href="#contact">Settings</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
    <div class="hero-unit">
  <h1>ErrorKit Control Panel</h1>
  <p>ErrorKit is currently under development and the functions in the control panel are therefore limited.</p>
  <p>
    <a class="btn btn-primary btn-large">
      Learn more
    </a>
  </p>
</div>

        <div class="row">
            <div class="span4">
                <h2>Last four hours</h2>
                
                <div id="errorgraph4" align="center" style="height:140px;margin-bottom: 10px"><img style="margin-top:54px;" src="loading.gif" />
</div><span class="label label-info">Today</span>
<span class="label label-warning">Yesterday</span><br><br>
                <p><a class="btn" href="#">View details »</a></p>
            </div>
<div class="span4" style="text-align:center;padding-top:55px;">

                <p><?php 
	                if(log_errors_to_database == true){
                $firstBound = "-" . date("s") . ' seconds -' . date("i") . ' minutes -' . date("G") . ' hours';
	                $secondBound = "tomorrow";
	                
	$firstTime = strtotime($firstBound);
	$lastTime = strtotime($secondBound);
	$ErrorKitDatabase = EKConnectToDatabase();
	$ErrorKitDatabase->beginTransaction();
	$query = $ErrorKitDatabase->prepare("SELECT `unixtime` FROM `EKErrors` WHERE `unixtime`>=? AND `unixtime`<=?");
	
	if(!$query){
		$errorInfo =  $ErrorKitDatabase->errorInfo();
		InternalDatabaseError($errorInfo[2]);
	}
	
	$query->bindValue(1, $firstTime, PDO::PARAM_INT);
	$query->bindValue(2, $lastTime, PDO::PARAM_INT);
	
	$query->execute();
	$fetch = $query->fetchAll();
	$ErrorKitDatabase->commit();
	$today = count($fetch);
	
	$firstBound = "-" . date("s") . ' seconds -' . date("i") . ' minutes -' . date("G") . ' hours';
	$secondBound = "tomorrow";
	                
	$firstTime = strtotime($firstBound. " -1 day");
	$lastTime = strtotime($secondBound. " -1 day");
	
	$ErrorKitDatabase->beginTransaction();
	$query = $ErrorKitDatabase->prepare("SELECT `unixtime` FROM `EKErrors` WHERE `unixtime`>=? AND `unixtime`<=?");
	
	if(!$query){
		$errorInfo =  $ErrorKitDatabase->errorInfo();
		InternalDatabaseError($errorInfo[2]);
	}
	
	$query->bindValue(1, $firstTime, PDO::PARAM_INT);
	$query->bindValue(2, $lastTime, PDO::PARAM_INT);
	
	$query->execute();
	$fetch = $query->fetchAll();
	$ErrorKitDatabase->commit();
	$yesterday = count($fetch);
	
	
					echo '<h3>Errors today: ' . $today . '</h3>';
				echo '<h3>Errors yesterday: ' . $yesterday . '</h3>';
				if($yesterday != 0){
					
				$percent = $today/$yesterday;
		if($percent>1){
			echo '<h3>Alteration: <span style="color:#EC633F">+' . round(($percent-1)*100,1) . '%</span></h3>';
		}else if($percent<1){
			echo '<h3>Alteration: <span style="color:#71B238">' . round(($percent-1)*100,1) . '%</span></h3>';
		}
		}
}else{
	echo "Database is not activated, change log_errors_to_database to true";
}
	?>
</p>

            </div>

            <div class="span4">
                <h2>Today</h2>

                
                <div id="errorgraphtoday" align="center" style="height:140px;margin-bottom: 10px"><img style="margin-top:54px;" src="loading.gif" /></div><span class="label label-info">Today</span>
<span class="label label-warning">Yesterday</span><br><br>

                <p><a class="btn" href="#">View details »</a></p>
            </div>

                    </div>
        <hr>
    </div>
</body>
</html>
