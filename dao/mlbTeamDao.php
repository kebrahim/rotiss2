<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'mlbTeam.php');

class MlbTeamDao {
  /**
   * Returns all MLB teams
   */
  static function getMlbTeams() {
    CommonDao::connectToDb();
    $query = "select M.*
              from mlb_team M
              order by M.city, M.team_name";
    return MlbTeamDao::createMlbTeamsFromQuery($query);
  }

  /**
   * Returns the MLB team with the specified ID, or null if none is found.
   */
  static function getMlbTeamById($mlbTeamId) {
    CommonDao::connectToDb();
    $query = "select M.*
              from mlb_team M
              where M.mlb_team_id = " . $mlbTeamId;
    return MlbTeamDao::createMlbTeamFromQuery($query);
  }
  
  /**
   * Returns the MLB team with the specified abbreviation or null if none is found.
   */
  static function getMlbTeamByAbbreviation($abbreviation) {
  	CommonDao::connectToDb();
  	$query = "select M.*
  	          from mlb_team M
  	          where M.abbreviation = '" . $abbreviation . "'";
  	return MlbTeamDao::createMlbTeamFromQuery($query);
  }
  
  private static function createMlbTeamFromQuery($query) {
    $teamArray = MlbTeamDao::createMlbTeamsFromQuery($query);
    if (count($teamArray) == 1) {
      return $teamArray[0];
    }
    return null;
  }

  private static function createMlbTeamsFromQuery($query) {
    $res = mysql_query($query);
    $teamsDb = array();
    while($teamDb = mysql_fetch_assoc($res)) {
      $teamsDb[] = new MlbTeam($teamDb["mlb_team_id"], $teamDb["city"], $teamDb["team_name"],
          $teamDb["abbreviation"], $teamDb["league"], $teamDb["division"]);
    }
    return $teamsDb;
  }
}
?>