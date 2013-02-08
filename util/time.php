<?php

require_once 'commonUtil.php';
CommonUtil::requireFileIn('/../dao/', 'eventDao.php');
CommonUtil::requireFileIn('/../dao/', 'weekDao.php');

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

  /**
   * Returns today's date/time in a YYYY-MM-DD HH:MM:SS format.
   */
  static function getTimestampString() {
  	date_default_timezone_set('America/New_York');
  	return date("Y-m-d H:i:s");
  }

  /**
   * Returns true if we're currently in the middle of the regular season.
   */
  static function isInSeason() {
    return TimeUtil::getYearByEvent(Event::SEASON_START) ==
        TimeUtil::getYearByEvent(Event::OFFSEASON_START);
  }

  /**
   * Returns true if we're currently during the offseason.
   */
  static function isOffSeason() {
    return !TimeUtil::isInSeason();
  }

  /**
   * Returns the current week during the season.
   */
  static function getCurrentWeekInSeason() {
    $week = WeekDao::getCurrentWeekInYear(TimeUtil::getCurrentYear());
    if ($week == null) {
      return 1;
    }
    return $week;
  }
}
?>