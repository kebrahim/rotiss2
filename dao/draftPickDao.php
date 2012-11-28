<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'draftPick.php');

/**
 * Manages the 'draft_pick' table.
 */
class DraftPickDao {
  /**
   * Returns all of the draft picks belonging to the specified team.
   */
  public static function getDraftPicksByTeamId($team_id) {
    CommonDao::connectToDb();
    $query = "select D.draft_pick_id, D.team_id, D.year, D.round, D.pick, D.original_team_id,
                D.player_id
              from draft_pick D
              where D.team_id = $team_id
              order by D.year, D.round, D.pick";
    return DraftPickDao::createDraftPicks($query);
  }

  /**
   * Returns all of the draft picks in the specified year.
   */
  public static function getDraftPicksByYear($year) {
    CommonDao::connectToDb();
    $query = "select D.draft_pick_id, D.team_id, D.year, D.round, D.pick, D.original_team_id,
                D.player_id
              from draft_pick D
              where D.year = $year
              order by D.round, D.pick";
    return DraftPickDao::createDraftPicks($query);
  }

  /**
   * Returns all of the draft picks in the specified year and specified round.
   */
  public static function getDraftPicksByYearRound($year, $round) {
  	CommonDao::connectToDb();
  	$query = "select D.draft_pick_id, D.team_id, D.year, D.round, D.pick, D.original_team_id,
  	                 D.player_id
  	          from draft_pick D
  	          where D.year = $year and D.round = $round
  	          order by D.pick";
  	return DraftPickDao::createDraftPicks($query);
  }
  
  /**
   * Returns the draft pick identified by the specified id.
   */
  public static function getDraftPickById($draftPickId) {
    CommonDao::connectToDb();
    $query = "select D.draft_pick_id, D.team_id, D.year, D.round, D.pick, D.original_team_id,
                     D.player_id
              from draft_pick D
              where D.draft_pick_id = $draftPickId";
    $draft_picks = DraftPickDao::createDraftPicks($query);
    return $draft_picks[0];
  }

  private static function createDraftPicks($query) {
    $draft_picks_db = mysql_query($query);

    $draft_picks = array();
    while ($draft_pick_db = mysql_fetch_row ($draft_picks_db)) {
      $draft_picks[] = new DraftPick($draft_pick_db[0], $draft_pick_db[1], $draft_pick_db[2],
      $draft_pick_db[3], $draft_pick_db[4], $draft_pick_db[5], $draft_pick_db[6]);
    }
    return $draft_picks;
  }

  /**
   * Returns the earliest draft year.
   */
  public static function getMinimumDraftYear() {
    CommonDao::connectToDb();
    $query = "select min(year) from draft_pick";
    $res = mysql_query($query);
    $row = mysql_fetch_row($res);
    return $row[0];
  }

  /**
   * Returns the latest draft year.
   */
  public static function getMaximumDraftYear() {
    CommonDao::connectToDb();
    $query = "select max(year) from draft_pick";
    $res = mysql_query($query);
    $row = mysql_fetch_row($res);
    return $row[0];
  }
  
  /**
   * Returns the earliest round in the specified draft year.
   */
  public static function getMinimumRound($year) {
    CommonDao::connectToDb();
    $query = "select min(round) from draft_pick where year = $year";
    $res = mysql_query($query);
    $row = mysql_fetch_row($res);
    return $row[0];
  }

  /**
   * Returns the latest round in the specified draft year.
   */
  public static function getMaximumRound($year) {
    CommonDao::connectToDb();
    $query = "select max(round) from draft_pick where year = $year";
    $res = mysql_query($query);
    $row = mysql_fetch_row($res);
    return $row[0];
  }
  
  public static function createDraftPick(DraftPick $draftPick) {
    // TODO
  }

  public static function updateDraftPick(DraftPick $draftPick) {
    CommonDao::connectToDb();
    $query = "update draft_pick set team_id = " . $draftPick->getTeam()->getId() . ",
                                    year = " . $draftPick->getYear() . ",
                                    round = " . $draftPick->getRound() . ",
                                    pick = " . ($draftPick->getPick() == null ? 
                                    		   "null" : $draftPick->getPick()) . ",
                                    original_team_id = " . $draftPick->getOriginalTeamId() . ",
                                    player_id = " . $draftPick->getPlayerId() .
             " where draft_pick_id = " . $draftPick->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }
}
?>