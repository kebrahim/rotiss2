<?php
  require_once 'commonScript.php';
  CommonScript::requireFileIn('/../../dao/', 'weekDao.php');
  CommonScript::requireFileIn('/../../entity/', 'week.php');
  CommonScript::requireFileIn('/../../util/', 'time.php');
  
  $year = 2014;
  echo "<h1>Generating Scoring weeks for $year...</h1>";
  for ($wknum = 1; $wknum <= 20; $wknum++) {
  	$week = new Week(-1, $year, $wknum, TimeUtil::getTimestampString());
    $week = WeekDao::createWeek($week);
    echo $week . "<br/>";
  }
?>
