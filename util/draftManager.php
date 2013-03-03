<?php

  require_once 'commonUtil.php';
  CommonUtil::requireFileIn('/../dao/', 'draftPickDao.php');

  /**
   * Handles utility methods for draft pick objects.
   */
  class DraftManager {

    /**
     * Displays a draft year selection box inside a single span12 cell with the specified year
     * selected.
     */
    public static function displayYearChooser($selectedYear) {
      echo "<div class='row-fluid'>
  	          <div class='span12 center chooser'>";
  	  $minYear = DraftPickDao::getMinimumDraftYear();
  	  $maxYear = DraftPickDao::getMaximumDraftYear();
  	  echo "<label for='year'>Choose year: </label>";
  	  echo "<select id='year' class='input-small' name='year' onchange='showYear(this.value)'>";
  	  for ($year = $minYear; $year <= $maxYear; $year++) {
  	    echo "<option value='" . $year . "'";
  		if ($year == $selectedYear) {
  		  echo " selected";
  		}
  		echo ">$year</option>";
  	  }
  	  echo "</select>";
  	  echo "</div>"; // span12
  	  echo "</div>"; // row-fluid
    }

    /**
     * Displays a year filter for an individual team, with the specified year selected.
     */
    public static function displayYearFilter($selectedYear) {
      $minYear = DraftPickDao::getMinimumDraftYear();
      $maxYear = DraftPickDao::getMaximumDraftYear();
      echo "<label for='year'>Filter by year: </label>";
      echo "<select id='year' class='input-small' name='year'
               onchange='showDraftYear(document.getElementById(\"team_id\").value, this.value)'>";
      for ($year = $minYear; $year <= $maxYear; $year++) {
        echo "<option value='" . $year . "'";
        if ($year == $selectedYear) {
          echo " selected";
        }
        echo ">$year</option>";
      }
      echo "</select>";
    }

    /**
     * Displays the draft picks for the selected team for the selected year.
     */
    public static function displayDraftPicksForTeam($team, $year) {
      $team->displayDraftPicks($year, $year, false);
    }
  }

  $displayType = null;
  if (isset($_REQUEST["type"])) {
    $displayType = $_REQUEST["type"];
  }

  if ($displayType == "teamdisplay") {
    $team = null;
    if (isset($_REQUEST["team_id"])) {
      $team = TeamDao::getTeamById($_REQUEST["team_id"]);
    }
    $year = null;
    if (isset($_REQUEST["year"])) {
      $year = $_REQUEST["year"];
    }
    DraftManager::displayDraftPicksForTeam($team, $year);
  }

?>