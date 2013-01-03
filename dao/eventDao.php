<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'event.php');

/**
 * Handles the events that occur during the rotiss fantasy season.
 */
class EventDao {
  
  /**
   * Returns the year associated with the specified event type; if the event has not occurred yet,
   * it is year n, if the event has occurred, it is year n+1. 
   */
  public static function getYearByEventType($eventType) {
  	CommonDao::connectToDb();
  	$query = "select max(e.year)
              from event e
              where e.event_type_id = $eventType
              and e.date < NOW()";
  	$res = mysql_query($query);
  	$row = mysql_fetch_row($res);
  	return $row[0];
  }
  
  /**
   * Returns all of the events during the specified year.
   */
  public static function getEventsByYear($year) {
  	CommonDao::connectToDb();
  	$query = "select e.*
  	          from event e
  	          where e.year = $year
  	          order by e.event_type_id";
	return EventDao::createEventsFromQuery($query);  	 
  }
  
  private static function createEventFromQuery($query) {
  	$eventArray = EventDao::createEventsFromQuery($query);
  	if (count($eventArray) == 1) {
  	  return $eventArray[0];
  	}
  	return null;
  }
  
  private static function createEventsFromQuery($query) {
  	$res = mysql_query($query);
  	$eventsDb = array();
  	while($eventDb = mysql_fetch_assoc($res)) {
  	  $eventsDb[] = new Event($eventDb["event_id"], $eventDb["year"], $eventDb["event_type_id"],
  	      $eventDb["date"]);
  	}
  	return $eventsDb;
  }
}

?>