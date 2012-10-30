<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'cumulativeRank.php');
CommonDao::requireFileIn('/../entity/', 'rank.php');

/**
 * DAO class for storing/retrieving offseason player cumulative ranks.
 */
class CumulativeRankDao {
  
	// TODO getCumulativeRankByPlayerYear()
/*  private static function createCumulativeRanksByQuery($query) {
    $res = mysql_query($query);
    $ranksDb = array();
    if (mysql_num_rows($res) > 0) {
      while($rankDb = mysql_fetch_assoc($res)) {
        $ranksDb[] = new Rank($rankDb["rank_id"], $rankDb["year"], $rankDb["team_id"],
        	$rankDb["player_id"], $rankDb["rank"], $rankDb["is_placeholder"]);
      }
    }
    return $ranksDb;
  }*/
  
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
   * Inserts the specified CumulativeRank in the 'cumulative_rank' table and returns the same
   * CumulativeRank with its id set.
   */
  public static function createCumulativeRank(CumulativeRank $cumulativeRank) {
    CommonDao::connectToDb();
    // cumulative rank cannot be less than 30.
    $rank = ($cumulativeRank->getRank() >= 30) ? $cumulativeRank->getRank() : 30;
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
}
?>