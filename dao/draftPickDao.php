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
    return DraftPickDao::createDraftPicksByQuery(
        "select D.*
        from draft_pick D
        where D.team_id = $team_id
        order by D.year, D.round, D.pick");
  }

  /**
   * Returns all of the draft picks in the specified year.
   */
  public static function getDraftPicksByYear($year) {
    return DraftPickDao::createDraftPicksByQuery(
        "select D.*
         from draft_pick D
         where D.year = $year
         order by D.round, D.pick");
  }

  /**
   * Returns all of the draft picks in the specified year and specified round.
   */
  public static function getDraftPicksByYearRound($year, $round) {
  	return DraftPickDao::createDraftPicksByQuery(
  	    "select D.*
  	     from draft_pick D
  	     where D.year = $year and D.round = $round
  	     order by D.pick");
  }

  /**
   * Returns the draft pick identified by the specified id.
   */
  public static function getDraftPickById($draftPickId) {
    return DraftPickDao::createDraftPickByQuery(
        "select D.*
         from draft_pick D
         where D.draft_pick_id = $draftPickId");
  }

  /**
   * Returns all of the draft picks of the specified player.
   */
  public static function getDraftPicksByPlayerId($playerId) {
    return DraftPickDao::createDraftPicksByQuery(
        "select d.*
        from draft_pick d
        where d.player_id = $playerId
        order by year DESC");
  }

  /**
   * Returns the draft pick of the specified player during the specified year.
   */
  public static function getDraftPickByPlayer($playerId, $year) {
    return DraftPickDao::createDraftPickByQuery(
        "select d.*
         from draft_pick d
         where d.player_id = $playerId and d.year = $year");
  }

  /**
   * Returns the seltzer cutoff draft pick for the specified year: this pick and everything after
   * are eligible for seltzer contracts.
   */
  public static function getSeltzerCutoffPick($year) {
    return DraftPickDao::createDraftPickByQuery(
        "select d.*
         from draft_pick d
         where d.year = $year and d.is_seltzer_cutoff = 1");
  }

  /**
   * Returns the draft pick during the specified year in the specified round at the specified pick.
   */
  public static function getDraftPickByYearRoundPick($year, $round, $pick) {
    return DraftPickDao::createDraftPickByQuery(
        "select d.*
         from draft_pick d
         where d.year = $year and d.round = $round and d.pick = $pick");
  }

  private static function createDraftPickByQuery($query) {
    $draftPicks = DraftPickDao::createDraftPicksByQuery($query);
    if (count($draftPicks) == 1) {
      return $draftPicks[0];
    }
    return null;
  }

  private static function createDraftPicksByQuery($query) {
    CommonDao::connectToDb();
    $res = mysql_query($query);
    $draft_picks = array();
    while($draftPickDb = mysql_fetch_assoc($res)) {
      $draft_picks[] = DraftPickDao::populateDraftPick($draftPickDb);
    }
    return $draft_picks;
  }

  private static function populateDraftPick($draftPickDb) {
    return new DraftPick($draftPickDb["draft_pick_id"], $draftPickDb["team_id"],
        $draftPickDb["year"], $draftPickDb["round"], $draftPickDb["pick"],
        $draftPickDb["original_team_id"], $draftPickDb["player_id"],
        $draftPickDb["is_seltzer_cutoff"]);
  }

  /**
   * Returns the earliest draft year.
   */
  public static function getMinimumDraftYear() {
    return CommonDao::getIntegerValueFromQuery(
        "select min(year) from draft_pick");
  }

  /**
   * Returns the latest draft year.
   */
  public static function getMaximumDraftYear() {
    return CommonDao::getIntegerValueFromQuery(
        "select max(year) from draft_pick");
  }

  /**
   * Returns the earliest round in the specified draft year.
   */
  public static function getMinimumRound($year) {
    return CommonDao::getIntegerValueFromQuery(
        "select min(round) from draft_pick where year = $year");
  }

  /**
   * Returns the latest round in the specified draft year.
   */
  public static function getMaximumRound($year) {
    return CommonDao::getIntegerValueFromQuery(
        "select max(round) from draft_pick where year = $year");
  }

  /**
   * Returns the number of picks in the specified year, for the specified team, occurring before or
   * during the specified round.
   */
  public static function getNumberPicksByTeamByRound($year, $teamId, $round) {
    return CommonDao::getIntegerValueFromQuery(
        "select count(*)
      	 from draft_pick D
      	 where D.year = $year
      	 and D.team_id = $teamId
      	 and D.round <= $round");
  }

  /**
   * Creates and returns the specified draft pick with its ID populated.
   */
  public static function createDraftPick(DraftPick $draftPick) {
    CommonDao::connectToDb();
  	$query = "insert into draft_pick(team_id, year, round, pick, original_team_id, player_id,
  	                                 is_seltzer_cutoff) values (" .
  	        $draftPick->getTeamId() . ", " .
  	        $draftPick->getYear() . ", " .
  	        $draftPick->getRound() . ", " .
  	        ($draftPick->getPick() == null ? "null" : $draftPick->getPick()) . ", " .
  	        $draftPick->getOriginalTeamId() . ", " .
  	        $draftPick->getPlayerId(). ", " .
  	        ($draftPick->isSeltzerCutoff() ? "1" : "0") . ")";
  	$result = mysql_query($query);
  	if (!$result) {
  	  echo "Draft pick " . $draftPick->toString() . " already exists in DB. Try again.";
  	  return null;
  	}

  	$idQuery = "select draft_pick_id from draft_pick where year = " . $draftPick->getYear() .
  	    " and team_id = " . $draftPick->getTeamId() . " and round = " . $draftPick->getRound();
  	$result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
  	$row = mysql_fetch_assoc($result);
  	$draftPick->setId($row["draft_pick_id"]);
  	return $draftPick;
  }

  /**
   * Updates the specified draft pick in the db.
   */
  public static function updateDraftPick(DraftPick $draftPick) {
    CommonDao::connectToDb();
    $query = "update draft_pick set team_id = " . $draftPick->getTeam()->getId() . ",
                                    year = " . $draftPick->getYear() . ",
                                    round = " . $draftPick->getRound() . ",
                                    pick = " . (($draftPick->getPick() == null) ?
                                    		   "null" : $draftPick->getPick()) . ",
                                    original_team_id = " . $draftPick->getOriginalTeamId() . ",
                                    player_id = " . $draftPick->getPlayerId() . ",
                                    is_seltzer_cutoff = " .
                                        ($draftPick->isSeltzerCutoff() ? "1" : "0") .
             " where draft_pick_id = " . $draftPick->getId();
    return mysql_query($query);
  }

  /**
   * Deletes all of the draft picks.
   */
  public static function deleteAllDraftPicks() {
  	CommonDao::connectToDb();
  	$query = "delete from draft_pick where draft_pick_id > 0";
  	mysql_query($query);
  }
}
?>