<?php
  require_once 'commonScript.php';
  CommonScript::requireFileIn('/../../dao/', 'eventDao.php');
  CommonScript::requireFileIn('/../../util/', 'time.php');
  
  $year = 2014;
  echo "<h1>Generating Events for $year...</h1>";
  for ($eventType = Event::OFFSEASON_START; $eventType <= Event::TRADE_DEADLINE; $eventType++) {
    $event = new Event(-1, $year, $eventType, TimeUtil::getTodayString());
    $event = EventDao::createEvent($event);
    echo $event->toString() . "<br/>";
  }
?>
