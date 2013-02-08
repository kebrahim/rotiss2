<?php
  require_once 'commonManager.php';
  CommonManager::requireFileIn('/../dao/', 'weekDao.php');

  /**
   * Handles scoring week-related methods
   */
  class WeekManager {
    public static function displayWeeksForManagement($year) {
      echo "<h3>$year Scoring Weeks</h3>";

      echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
              <thead><tr><th>Week Number</th><th>Start Date/Time</th></tr></thead>";

      $weeks = WeekDao::getWeeksByYear($year);
      foreach ($weeks as $week) {
        echo "<tr class='tdselect'>
                <td>" . $week->getWeekNumber() . "</td>
                <td><input type=datetime name='weekStart" . $week->getWeekNumber() . "' required
                           class='center' value='" . $week->getStartTime() . "'></td>
        </tr>";
      }
      echo "</table>";
      echo "<input type=hidden name='year' value='$year'>";
    }
  }

  $displayType = null;
  if (isset($_REQUEST["type"])) {
    $displayType = $_REQUEST["type"];
  }

  if ($displayType == "manage") {
    if (isset($_REQUEST["year"])) {
      $year = $_REQUEST["year"];
    }
    WeekManager::displayWeeksForManagement($year);
  }
?>
