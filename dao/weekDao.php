<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'week.php');

/**
 * Handles the scoring weeks during the season.
 */
class WeekDao {

  /**
   * Returns the week w/ the specified id.
   */
  public static function getWeekById($weekId) {
    return WeekDao::createWeekFromQuery(
        "select w.* from week w where w.week_id = $weekId");
  }

  /**
   * Returns all of the weeks in the specified year.
   */
  public static function getWeeksByYear($year) {
    return WeekDao::createWeeksFromQuery(
        "select w.*
         from week w
         where w.year = $year");
  }

  private static function createWeekFromQuery($query) {
    $weekArray = WeekDao::createWeeksFromQuery($query);
    if (count($weekArray) == 1) {
      return $weekArray[0];
    }
    return null;
  }

  private static function createWeeksFromQuery($query) {
    CommonDao::connectToDb();
    $res = mysql_query($query);
    $weeksDb = array();
    while($weekDb = mysql_fetch_assoc($res)) {
      $weeksDb[] = WeekDao::populateWeek($weekDb);
    }
    return $weeksDb;
  }

  private static function populateWeek($weekDb) {
    return new Week($weekDb["week_id"], $weekDb["year"], $weekDb["week_number"],
        $weekDb["start_time"]);
  }

  public static function getMinimumYear() {
    return CommonDao::getIntegerValueFromQuery("select min(year) from week");
  }

  public static function getMaximumYear() {
    return CommonDao::getIntegerValueFromQuery("select max(year) from week");
  }

  /**
   * Updates the specified week in the 'week' table.
   */
  public static function updateWeek(Week $week) {
    CommonDao::connectToDb();
    $query = "update week set year = " . $week->getYear() . ",
                              week_number = " . $week->getWeekNumber() . ",
                              start_time = '" . $week->getStartTime() . "'
                          where week_id = " . $week->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }
}
