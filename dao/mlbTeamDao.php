<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'mlbTeam.php');

class MlbTeamDao {
  /**
   * Returns all MLB teams
   */
  static function getMlbTeams() {
    CommonDao::connectToDb();
    $query = "select M.mlb_team_id, M.city, M.team_name, M.abbreviation, M.league, M.division
              from mlb_team M
              order by M.city, M.team_name";
    $mlb_teams_db = mysql_query($query);

    $mlb_teams = array();
    while ($mlb_team_db = mysql_fetch_row ($mlb_teams_db)) {
      $mlb_teams[] = new MlbTeam($mlb_team_db[0], $mlb_team_db[1], $mlb_team_db[2],
          $mlb_team_db[3], $mlb_team_db[4], $mlb_team_db[5]);
    }
    return $mlb_teams;
  }

  /**
   * Returns the MLB team with the specified ID, or null if none is found.
   */
  static function getMlbTeamById($mlbTeamId) {
    CommonDao::connectToDb();
    $query = "select M.mlb_team_id, M.city, M.team_name, M.abbreviation, M.league, M.division
              from mlb_team M
              where M.mlb_team_id = " . $mlbTeamId;
    $mlbTeamDb = mysql_fetch_row(mysql_query($query));
    if ($mlbTeamDb == null) {
      return null;
    }
    return new MlbTeam($mlbTeamDb[0], $mlbTeamDb[1], $mlbTeamDb[2], $mlbTeamDb[3], $mlbTeamDb[4],
        $mlbTeamDb[5]);
  }
}
?>