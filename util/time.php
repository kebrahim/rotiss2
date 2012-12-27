<?php

class TimeUtil {
  const RANKINGS_OPEN_EVENT = 1;
  const RANKINGS_CLOSE_EVENT = 2;
  const AUCTION_EVENT = 3;
  const KEEPER_NIGHT_EVENT = 4;
  const DRAFT_EVENT = 5;
  const TRADE_DEADLINE_EVENT = 6;
  const SEASON_END_EVENT = 7;
	
  /**
   * Returns the year based on the specified event. If the specified event has not occurred yet,
   * then it is year n; if it has occurred, then it's year n+1.
   */
  static function getYearByEvent($event) {
  	// TODO add support for rankings, trade deadline events
  	switch ($event) {
  	  case TimeUtil::AUCTION_EVENT:
  		return TimeUtil::getYearBasedOnAuctionNight();
  	  case TimeUtil::KEEPER_NIGHT_EVENT:
  	  	return TimeUtil::getYearBasedOnKeeperNight();
  	  case TimeUtil::DRAFT_EVENT:
  	  	return TimeUtil::getYearBasedOnStartOfSeason();
  	  case TimeUtil::SEASON_END_EVENT:
  	  	return TimeUtil::getYearBasedOnEndOfSeason();
  	  default:
  	  	return "error";
  	}
  }

  /**
   * Returns the year based on whether auction night has occurred yet during
   * that season; after auction night [around 1/15], the year changes.
   */
  static function getYearBasedOnAuctionNight() {
    // TODO
    return "2012";
  }
	
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
  	return "2013";
  }
  
  /**
   * Returns the current year.
   */
  static function getCurrentYear() {
  	date_default_timezone_set('America/New_York');
  	// TODO return date("Y");
  	return "2013";
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