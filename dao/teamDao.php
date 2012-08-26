<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'team.php');

class TeamDao {
  /**
   * Returns team information for specified team ID and null if none is found.
   */
  public static function getTeamById($team_id) {
    CommonDao::connectToDb();
    $query = "select T.team_name, T.league, T.division, T.abbreviation, T.sportsline_image
              from team T
              where T.team_id = $team_id";
    $res = mysql_query($query);
    $team_info = mysql_fetch_row($res);
    if ($team_info == null) {
      return null;
    }
    return new Team($team_id, $team_info[0], $team_info[1], $team_info[2], $team_info[3],
        $team_info[4]);
  }

  /**
   * Returns all teams.
   */
  public static function getAllTeams() {
    CommonDao::connectToDb();
    $query = "select T.team_id, T.team_name, T.league, T.division, T.abbreviation,
                     T.sportsline_image
              from team T order by T.team_name";
    return TeamDao::createTeamsFromQuery($query);
  }

  private static function createTeamsFromQuery($query) {
    $res = mysql_query($query);
    $teamsDb = array();
    while($teamDb = mysql_fetch_assoc($res)) {
      $teamsDb[] = new Team($teamDb["team_id"], $teamDb["team_name"], $teamDb["league"],
          $teamDb["division"], $teamDb["abbreviation"], $teamDb["sportsline_image"]);
    }
    return $teamsDb;
  }

 /**
  * Updates the specified team in the 'team' table.
  */
  public static function updateTeam($team) {
    CommonDao::connectToDb();
    $query = "update team set team_name = '" . $team->getName() . "',
                              league = '" . $team->getLeague() . "',
                              division = '" . $team->getDivision() . "',
                              abbreviation = '" . $team->getAbbreviation() . "',
                              sportsline_image = '" . $team->getSportslineImageName() .
                              "' where team_id = " . $team->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }
}
?>