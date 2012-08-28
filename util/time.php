<?php

class TimeUtil {
  /**
   * Retrieves the year for the current season.
   *
   * TODO 10 season-year changes after rotiss season completes
   * Consider storing this in DB.
   */
  static function getCurrentSeasonYear() {
    // Currently returns the current year
  	date_default_timezone_set('America/New_York');
    return date("Y");
  }
}

?>