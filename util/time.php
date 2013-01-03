<?php

require_once 'commonUtil.php';
CommonUtil::requireFileIn('/../dao/', 'eventDao.php');

class TimeUtil {
	
  /**
   * Returns the year based on the specified event. If the specified event has not occurred yet,
   * then it is year n; if it has occurred, then it's year n+1.
   */
  static function getYearByEvent($eventType) {
  	return EventDao::getYearByEventType($eventType);
  }
  
  /**
   * Returns the current year.
   */
  static function getCurrentYear() {
  	date_default_timezone_set('America/New_York');
  	return date("Y");
  }
  
  /**
   * Returns today's date in YYYY-MM-DD format.
   */
  static function getTodayString() {
  	date_default_timezone_set('America/New_York');
  	return date("Y-m-d");
  }
}
?>