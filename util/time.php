<?php

class TimeUtil {
  /**
   * Returns the year based on whether keeper night has occurred yet during
   * that season; after keeper night [around 3/15], the year changes.
   */
  static function getYearBasedOnKeeperNight() {
	// TODO
	return "2012";  	
  }

  /**
   * Returns the year based on what season is currently occurring. At the start
   * of the fantasy season [aka the draft, around 4/1], the year changes.
   */
  static function getYearBasedOnStartOfSeason() {
  	// TODO
  	return "2012";
  }
  
  /**
   * Returns the year based on what season is currently occurring. At the end
   * of the fantasy season [around 9/15], the year changes.
   */  
  static function getYearBasedOnEndOfSeason() {
  	// TODO
  	return "2012";
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