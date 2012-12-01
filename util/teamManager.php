<?php

require_once 'commonUtil.php';
CommonUtil::requireFileIn('/../dao/', 'teamDao.php');

/**
 * Handles utility methods for team objects.
 */
class TeamManager {

  /**
   * Displays a team selection box inside a single span12 cell with the specified team selected.
   */
  public static function displayTeamChooser($selectedTeam) {
  	echo "<div class='row-fluid'>
  	        <div class='span12 center chooser'>";
  	$allTeams = TeamDao::getAllTeams();
  	echo "<label for='team_id'>Choose team: </label>";
  	echo "<select id='team_id' name='team_id' class='span6' onchange='showTeam(this.value)'>";
  	foreach ($allTeams as $team) {
  	  echo "<option value='" . $team->getId() . "'";
  	  if ($team->getId() == $selectedTeam->getId()) {
  	    echo " selected";
  	  }
  	  echo ">" . $team->getName() . " (" . $team->getAbbreviation() . ")</option>";
  	}
  	echo "</select>";
  	echo "</div>"; // span12
  	echo "</div>"; // row-fluid
  }
}

?>