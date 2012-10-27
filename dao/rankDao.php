<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'rank.php');

/**
 * DAO class for storing/retrieving offseason player ranks.
 */
class RankDao {

  /**
   * Returns all of the ranks belonging to the specified team for the specified year.
   */
  public static function getRanksByTeamYear($teamId, $year) {
	CommonDao::connectToDb();
	$query = "select * from rank
		      where team_id = " . $teamId . " and year = " . $year .
       		" order by rank DESC";
	return RankDao::createRanksByQuery($query);
  }
		
  /**
   * Returns all of the ranks belonging to the specified team for the specified year with the
   * specified rank.
   */
  public static function getRanksByTeamYearRank($teamId, $year, $rank) {
    CommonDao::connectToDb();
    $query = "select *
              from rank
              where team_id = " . $teamId . " and year = " . $year .
            " and rank = " . $rank .
            " order by is_placeholder DESC";
    return RankDao::createRanksByQuery($query);
  }

  private static function createRanksByQuery($query) {
    $res = mysql_query($query);
    $ranksDb = array();
    if (mysql_num_rows($res) > 0) {
      while($rankDb = mysql_fetch_assoc($res)) {
        $ranksDb[] = new Rank($rankDb["rank_id"], $rankDb["year"], $rankDb["team_id"],
        	$rankDb["player_id"], $rankDb["rank"], $rankDb["is_placeholder"]);
      }
    }
    return $ranksDb;
  }

  /**
   * Inserts the specified Rank in the 'rank' table and returns the same Rank
   * with its id set.
   */
  public static function createRank(Rank $rank) {
    CommonDao::connectToDb();
  	$query = "insert into rank(year, team_id, player_id, rank, is_placeholder)
  	    values (" . $rank->getYear() . ", " . $rank->getTeam()->getId() .
  	    ", " . $rank->getPlayer()->getId() . ", " . $rank->getRank() . 
  	    ", " . ($rank->isPlaceholder() ? "1" : "0") . ")";
  	$result = mysql_query($query);
  	if (!$result) {
  	  echo "Rank " . $rank->toString() . " already exists in DB. Try again.";
  	  return null;
  	}

  	$idQuery = "select rank_id from rank where year = " . $rank->getYear() .
  	    " and team_id = " . $rank->getTeam()->getId() . " and player_id = " .
  	    $rank->getPlayer()->getId();
  	$result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
  	$row = mysql_fetch_assoc($result);
  	$rank->setId($row["rank_id"]);
  	return $rank;
  }

  /**
   * Updates the specified rank in the DB.
   */
  public static function updateRank(Rank $rank) {
    CommonDao::connectToDb();
    $query = "update rank set year = " . $rank->getYear() . ",
                              rank = " . $rank->getRank() . ",
                              team_id = " . $rank->getTeam()->getId() . ",
                              player_id = " . $rank->getPlayerId() . ",
                              is_placeholder = " . ($rank->isPlaceholder() ? "1" : "0") .
                        " where rank_id = " . $rank->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }

  /**
   * Removes the specified rank from the DB.
   */
  public static function deleteRank(Rank $rank) {
  	CommonDao::connectToDb();
  	$query = "delete from rank where rank_id = " . $rank->getId();
  	$result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }
}
?>