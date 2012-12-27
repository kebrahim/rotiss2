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
  	echo "<label for='team_id'>Choose team:</label>";
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

  /**
   * Returns a row in a table with the specified team name and logo.
   */
  public static function getNameAndLogoRow($team) {
  	if ($team != null) {
  	  return "<td>" . $team->getSportslineImg(32, 32) . "</td>
  		      <td>" . $team->getNameLink(true) . "</td>";
  	} else {
  	  return "<td colspan=2>--</td>";
  	}
  }

  /**
   * Returns a row in a table with the specified team abbreviation and logo.
   */
  public static function getAbbreviationAndLogoRow($team) {
    return TeamManager::getAbbreviationAndLogoRowAtLevel($team, true);
  }
  
  /**
   * Returns a row in a table with the specified team abbreviation and logo.
   */
  public static function getAbbreviationAndLogoRowAtLevel($team, $isTopLevel) {
  	if ($team != null) {
  		return "<td>" . $team->getSportslineImg(32, 32) . "</td>
  		<td>" . $team->getIdLink($isTopLevel, $team->getAbbreviation()) . "</td>";
  	} else {
  		return "<td colspan=2>--</td>";
  	}
  }
}

?>