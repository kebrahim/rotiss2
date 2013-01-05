<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'auctionResult.php');

/**
 * DAO for handling auction data, specifically in the 'auction' table.
 */
class AuctionResultDao {

  /**
   * Returns the auction result associated with the specified id.
   */
  public static function getAuctionResultById($auctionId) {
  	CommonDao::connectToDb();
  	$query = "select * from auction
  	          where auction_id = $auctionId";
  	return AuctionResultDao::createAuctionResultFromQuery($query);
  }

  /**
   * Returns all of the auction results belonging to the specified team.
   */
  public static function getAuctionResultsByTeamId($team_id) {
    CommonDao::connectToDb();
    $query = "select * from auction
              where team_id = $team_id
              order by year, cost DESC";
    return AuctionResultDao::createAuctionResultsFromQuery($query);
  }

  /**
   * Returns all of the auction results in the specified year.
   */
  public static function getAuctionResultsByYear($year) {
    CommonDao::connectToDb();
    $query = "select * from auction
              where year = $year
              order by auction_id";
    return AuctionResultDao::createAuctionResultsFromQuery($query);
  }

  private static function createAuctionResultFromQuery($query) {
    $auctionArray = AuctionResultDao::createAuctionResultsFromQuery($query);
    if (count($auctionArray) == 1) {
      return $auctionArray[0];
    }
    return null;
  }

  private static function createAuctionResultsFromQuery($query) {
    $res = mysql_query($query);

    $auctionResults = array();
    while ($auctionDb = mysql_fetch_assoc($res)) {
      $auctionResults[] = new AuctionResult($auctionDb["auction_id"], $auctionDb["year"],
          $auctionDb["team_id"], $auctionDb["player_id"], $auctionDb["cost"]);
    }
    return $auctionResults;
  }

  /**
   * Returns the earliest auction year.
   */
  public static function getMinimumAuctionYear() {
    CommonDao::connectToDb();
    $query = "select min(year) from auction";
    $res = mysql_query($query);
    $row = mysql_fetch_row($res);
    return $row[0];
  }

  /**
   * Returns the latest auction year.
   */
  public static function getMaximumAuctionYear() {
    CommonDao::connectToDb();
    $query = "select max(year) from auction";
    $res = mysql_query($query);
    $row = mysql_fetch_row($res);
    return $row[0];
  }

  /**
   * Inserts the specified AuctionResult in the 'auction' table and returns the same AuctionResult
   * with its id set.
   */
  public static function createAuctionResult(AuctionResult $auctionResult) {
  	CommonDao::connectToDb();
  	$query = "insert into auction(year, team_id, player_id, cost)
  	    values (" . $auctionResult->getYear() . ", " . $auctionResult->getTeam()->getId() .
  	    ", " . $auctionResult->getPlayer()->getId() . ", " . $auctionResult->getCost() . ")";
  	$result = mysql_query($query);
  	if (!$result) {
  	  echo "Auction result " . $auctionResult->toString() . " already exists in DB. Try again.";
  	  return null;
  	}

  	$idQuery = "select auction_id from auction where year = " . $auctionResult->getYear() .
  	    " and team_id = " . $auctionResult->getTeam()->getId() . " and player_id = " .
  	    $auctionResult->getPlayer()->getId();
  	$result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
  	$row = mysql_fetch_assoc($result);
  	$auctionResult->setId($row["auction_id"]);
  	return $auctionResult;
  }

  /**
   * Deletes all of the auction results.
   */
  public static function deleteAllAuctionResults() {
  	CommonDao::connectToDb();
  	$query = "delete from auction where auction_id > 0";
  	mysql_query($query);
  }
}
?>