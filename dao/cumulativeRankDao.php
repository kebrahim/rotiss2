<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'cumulativeRank.php');
CommonDao::requireFileIn('/../entity/', 'rank.php');

/**
 * DAO class for storing/retrieving offseason player cumulative ranks.
 */
class CumulativeRankDao {

  /**
   * Return the cumulative rank for the specified player during the specified year, or null if none
   * exist.
   */
  public static function getCumulativeRankByPlayerYear($playerId, $year) {
  	CommonDao::connectToDb();
  	$query = "select cr.*
  	          from cumulative_rank cr
  	          where cr.player_id = " . $playerId .
  	        " and cr.year = " . $year;
  	return CumulativeRankDao::createCumulativeRankByQuery($query);
  }

  /**
   * Returns all of the cumulative ranks for the specified year.
   */
  public static function getCumulativeRanksByYear($year) {
    CommonDao::connectToDb();
    $query = "select cr.*
    	      from cumulative_rank cr
    	      where cr.year = " . $year .
            " order by rank desc";
    return CumulativeRankDao::createCumulativeRanksByQuery($query);
  }

  private static function createCumulativeRankByQuery($query) {
  	$ranksDb = CumulativeRankDao::createCumulativeRanksByQuery($query);
  	if (count($ranksDb) == 1) {
  	  return $ranksDb[0];
  	}
  	return null;
  }

  private static function createCumulativeRanksByQuery($query) {
    $res = mysql_query($query);
    $ranksDb = array();
    if (mysql_num_rows($res) > 0) {
      while($rankDb = mysql_fetch_assoc($res)) {
        $ranksDb[] = new CumulativeRank($rankDb["cumulative_rank_id"], $rankDb["year"],
        	$rankDb["player_id"], $rankDb["rank"], $rankDb["is_placeholder"]);
      }
    }
    return $ranksDb;
  }

  /**
   * Returns true if the player with the specified id has a cumulative rank stored for the
   * specified year.
   */
  public static function hasCumulativeRank($playerId, $year) {
  	CommonDao::connectToDb();
  	$query = "select *
  	          from cumulative_rank cr
  	          where cr.year = " . $year . "
  	          and cr.player_id = " . $playerId;
  	$res = mysql_query($query);
  	return (mysql_num_rows($res) == 1);
  }

  /**
   * Returns true if any cumulative ranks exist for the specified year.
   */
  public static function hasCumulativeRanks($year) {
    CommonDao::connectToDb();
    $query = "select *
      	      from cumulative_rank cr
      	      where cr.year = " . $year;
    $res = mysql_query($query);
    return (mysql_num_rows($res) > 0);
  }

  /**
   * Inserts the specified CumulativeRank in the 'cumulative_rank' table and returns the same
   * CumulativeRank with its id set.
   */
  public static function createCumulativeRank(CumulativeRank $cumulativeRank) {
    CommonDao::connectToDb();
    // cumulative rank cannot be less than CumulativeRank::MINIMUM_RANK.
    $rank = ($cumulativeRank->getRank() >= CumulativeRank::MINIMUM_RANK) ?
        $cumulativeRank->getRank() : CumulativeRank::MINIMUM_RANK;
  	$query = "insert into cumulative_rank(year, player_id, rank, is_placeholder)
  	    values (" . $cumulativeRank->getYear() .
  	    ", " . $cumulativeRank->getPlayerId() . ", " . $rank .
  	    ", " . ($cumulativeRank->isPlaceholder() ? "1" : "0") . ")";
  	$result = mysql_query($query);
  	if (!$result) {
  	  echo "Cumulative rank " . $cumulativeRank->toString() . " already exists in DB. Try again.";
  	  return null;
  	}

  	$idQuery = "select cumulative_rank_id from cumulative_rank where year = " .
  	    $cumulativeRank->getYear() . " and player_id = " . $cumulativeRank->getPlayerId();
  	$result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
  	$row = mysql_fetch_assoc($result);
  	$cumulativeRank->setId($row["cumulative_rank_id"]);
  	return $cumulativeRank;
  }

  /**
   * Deletes all cumulative ranks.
   */
  public static function deleteAllCumulativeRanks() {
  	CommonDao::connectToDb();
  	$query = "delete from cumulative_rank where cumulative_rank_id > 0";
  	mysql_query($query);
  }
}
?>