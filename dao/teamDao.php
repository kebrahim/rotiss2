<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'team.php');

class TeamDao {
  /**
   * Returns team information for specified team ID and null if none is found.
   */
  public static function getTeamById($team_id) {
    CommonDao::connectToDb();
    $query = "select t.*
              from team t
              where t.team_id = $team_id";
  	return TeamDao::createTeamFromQuery($query);
  }

  /**
   * Returns all teams.
   */
  public static function getAllTeams() {
    CommonDao::connectToDb();
    $query = "select t.*
              from team t
              order by lower(t.abbreviation)";
    return TeamDao::createTeamsFromQuery($query);
  }

  /**
   * Returns the team to which the specified player belongs, and null if the player deos not belong
   * to any team.
   */
  public static function getTeamByPlayer(Player $player) {
  	CommonDao::connectToDb();
  	$query = "select t.*
  	          from team t, team_player tp
  	          where t.team_id = tp.team_id and tp.player_id = " . $player->getId();
  	return TeamDao::createTeamFromQuery($query);
  }

  /**
   * Returns the team with the specified team name, or null if none exists.
   */
  public static function getTeamByName($teamName) {
  	CommonDao::connectToDb();
  	$query = "select t.*
  	          from team t
  	          where t.team_name = \"" . $teamName . "\"";
  	return TeamDao::createTeamFromQuery($query);
  }

  private static function createTeamFromQuery($query) {
  	$teamArray = TeamDao::createTeamsFromQuery($query);
  	if (count($teamArray) == 1) {
  	  return $teamArray[0];
  	}
  	return null;
  }

  public static function createTeamsFromQuery($query) {
    $res = mysql_query($query);
    $teamsDb = array();
    if (mysql_num_rows($res) > 0) {
      while($teamDb = mysql_fetch_assoc($res)) {
        $teamsDb[] = TeamDao::populateTeam($teamDb);
      }
    }
    return $teamsDb;
  }

  public static function populateTeam($teamDb) {
    return new Team($teamDb["team_id"], $teamDb["team_name"], $teamDb["league"],
        $teamDb["division"], $teamDb["abbreviation"], $teamDb["sportsline_image"]);
  }

 /**
  * Updates the specified team in the 'team' table.
  */
  public static function updateTeam($team) {
    CommonDao::connectToDb();
    $query = "update team set team_name = \"" . $team->getName() . "\",
                              league = '" . $team->getLeague() . "',
                              division = '" . $team->getDivision() . "',
                              abbreviation = '" . $team->getAbbreviation() . "',
                              sportsline_image = '" . $team->getSportslineImageName() .
                              "' where team_id = " . $team->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }

  /**
   * Drops player from whatever team he's on.
   */
  public static function dropPlayer(Player $player) {
    TeamDao::assignPlayerToTeam($player, 0);
  }

  /**
   * Assigns the specified player to the specified team; if teamId = 0, then unassigns player from
   * current team.
   */
  public static function assignPlayerToTeam(Player $player, $teamId) {
  	CommonDao::connectToDb();
  	$currentTeam = TeamDao::getTeamByPlayer($player);

  	if ($currentTeam == null) {
  	  if ($teamId > 0) {
  	  	// create new record in team_player
  	  	$query = "insert into team_player(team_id, player_id) values (" . $teamId . ", " .
    	    $player->getId() . ")";
  	  } else {
  	    // player had no team and still has no team, so there's no query to execute.
  	    return;
  	  }
  	} else {
  	  if ($teamId == 0) {
  	  	// delete record from team_player
  	  	$query = "delete from team_player where player_id = " . $player->getId();
  	  } else {
  	  	// update record in team_player
  	  	$query = "update team_player set team_id = " . $teamId .
  	  	    " where player_id = " . $player->getId();
  	  }
  	}
  	$result = mysql_query($query) or die(mysql_error());
  }

  /**
   * Deletes all of the team-player associations.
   */
  public static function deleteAllPlayerAssociations() {
  	CommonDao::connectToDb();
  	$query = "delete from team_player where team_player_id > 0";
  	mysql_query($query);
  }
}
?>