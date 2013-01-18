<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'cumulativeRank.php');
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
    $query = "select r.*
              from rank r
              where r.team_id = " . $teamId . " and r.year = " . $year .
            " order by r.rank DESC";
    return RankDao::createRanksByQuery($query);
  }

  /**
   * Returns all of the ranks belonging to the specified team for the specified year with the
   * specified rank, sorted by fantasy points from the previous year.
   */
  public static function getRanksByTeamYearRank($teamId, $year, $rank) {
    CommonDao::connectToDb();
    $query = "select r.*, p.*, s.*
              from rank r, player p, stat s
              where r.team_id = " . $teamId .
            " and r.year = " . $year .
            " and r.rank = " . $rank .
            " and r.player_id = p.player_id
              and p.player_id = s.player_id
              and s.year = " . ($year - 1) .
            " order by r.is_placeholder DESC, s.fantasy_pts DESC";

    $res = mysql_query($query);
    $ranksDb = array();
    if (mysql_num_rows($res) > 0) {
      while($rankDb = mysql_fetch_assoc($res)) {
    	$rank = new Rank($rankDb["rank_id"], $rankDb["year"], $rankDb["team_id"],
    	    $rankDb["player_id"], $rankDb["rank"], $rankDb["is_placeholder"]);
    	$player = PlayerDao::populatePlayer($rankDb);
    	$player->setStatLine($year - 1, StatDao::populateStatLine($rankDb));
    	$rank->setPlayer($player);
    	$ranksDb[] = $rank;
      }
    }
    return $ranksDb;
  }

  /**
   * Returns all ranks for the specified player during the specified year.
   */
  public static function getRanksByPlayerYear($playerId, $year) {
    CommonDao::connectToDb();
    $query = "select r.*, p.*
              from rank r, player p
              where r.player_id = " . $playerId .
            " and r.year = " . $year .
            " and r.player_id = p.player_id
              order by r.rank DESC";

    $res = mysql_query($query);
    $ranksDb = array();
    if (mysql_num_rows($res) > 0) {
      while($rankDb = mysql_fetch_assoc($res)) {
        $rank = new Rank($rankDb["rank_id"], $rankDb["year"], $rankDb["team_id"],
            $rankDb["player_id"], $rankDb["rank"], $rankDb["is_placeholder"]);
        $player = PlayerDao::populatePlayer($rankDb);
        $rank->setPlayer($player);
        $ranksDb[] = $rank;
      }
    }
    return $ranksDb;
  }

  /**
   * Returns a set of CumulativeRanks [sums of ranks per player] for the specified year, ordered by
   * cumulative rank total.
   */
  public static function calculateCumulativeRanksByYear($year) {
  	CommonDao::connectToDb();
  	$query = "select r.year, r.player_id, sum(r.rank) as totalRank,
  	          count(r.rank) as rankCount, r.is_placeholder, p.*
  	          from rank r, player p
  	          where r.player_id = p.player_id
  	          and r.year = " . $year .
  	        " group by r.player_id
  	          order by sum(r.rank) DESC";
  	$res = mysql_query($query);
  	$ranksDb = array();
  	if (mysql_num_rows($res) > 0) {
  	  while($rankDb = mysql_fetch_assoc($res)) {
  	    $rank = new CumulativeRank(-1, $rankDb["year"], $rankDb["player_id"], $rankDb["totalRank"],
  	        $rankDb["is_placeholder"]);
  		$rank->setPlayer(PlayerDao::populatePlayer($rankDb));
  		$rank->setRankCount($rankDb["rankCount"]);
  		$ranksDb[] = $rank;
  	  }
  	}
  	return $ranksDb;
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
   * Returns true if the player with the specified id has placeholder ranks stored for the
   * specified year.
   */
  public static function hasAllPlaceholderRanks($playerId, $year) {
  	CommonDao::connectToDb();
  	$query = "select *
  	          from rank r
  	          where r.year = " . $year . "
  	          and r.is_placeholder = 1
  	          and r.player_id = " . $playerId;
  	$res = mysql_query($query);
  	return (mysql_num_rows($res) == 15);
  }

  /**
   * Returns an array of rank values to corresponding number of ranks for the specified team during
   * the specified year.
   */
  public static function getRankCount($teamId, $year) {
  	CommonDao::connectToDb();
  	$query = "select r.rank, count(r.rank)
  	          from rank r
  	          where r.year = " . $year . "
  	          and r.team_id = " . $teamId . "
  	          group by r.rank";
  	$res = mysql_query($query);
  	$rankCountArray = array();
  	while ($row = mysql_fetch_row($res)) {
  	  $rankCountArray[$row[0]] = $row[1];
  	}
  	return $rankCountArray;
  }

  /**
   * Returns the total number of ranks for the specified team during the specified year.
   */
  public static function getTotalRankCount($teamId, $year) {
  	CommonDao::connectToDb();
  	$query = "select count(r.rank)
  	          from rank r
  	          where r.year = " . $year . "
  	          and r.team_id = " . $teamId . "
  	          and r.rank > 0";
  	$res = mysql_query($query);
  	$row = mysql_fetch_row($res);
  	return $row[0];
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

  /**
   * Deletes all of the ranks.
   */
  public static function deleteAllRanks() {
  	CommonDao::connectToDb();
  	$query = "delete from rank where rank_id > 0";
  	mysql_query($query);
  }
}
?>