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
  
  /**
   * Creates a new event in the 'event' table.
   */
  public static function createEvent(Event $event) {
  	CommonDao::connectToDb();
  	$query = "insert into event(year, event_type_id, date)
  	          values (" .
  	    $event->getYear() . ", " .
  	    $event->getEventType() . ", '" .
  	    $event->getEventDate() . "')";
  	$result = mysql_query($query);
  	if (!$result) {
  	  echo "Event " . $event->toString() . " already exists in DB. Try again.";
      return null;
  	}
  	
  	$idQuery = "select event_id from event where year = " . $event->getYear() .
  	    " and event_type_id = " . $event->getEventType();
  	$result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
  	$row = mysql_fetch_assoc($result);
  	$event->setId($row["event_id"]);
  	return $event;
  }
  
  /**
   * Updates the specified event in the 'event' table.
   */
  public static function updateEvent(Event $event) {
  	CommonDao::connectToDb();
  	$query = "update event set year = " . $event->getYear() . ",
    	                       event_type_id = " . $event->getEventType() . ",
    	                       date = '" . $event->getEventDate() . "'
    	                   where event_id = " . $event->getId();
  	$result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }
  
  /**
   * Returns the earliest event year.
   */
  public static function getMinimumYear() {
  	CommonDao::connectToDb();
  	$query = "select min(year) from event";
  	$res = mysql_query($query);
  	$row = mysql_fetch_row($res);
  	return $row[0];
  }
  
  /**
   * Returns the latest event year.
   */
  public static function getMaximumYear() {
  	CommonDao::connectToDb();
  	$query = "select max(year) from event";
  	$res = mysql_query($query);
  	$row = mysql_fetch_row($res);
  	return $row[0];
  }
}

?>