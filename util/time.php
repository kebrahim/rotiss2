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
    return date("Y");
  }
}

?>