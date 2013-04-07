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
  	return AuctionResultDao::createAuctionResultFromQuery(
  	    "select * from auction
  	     where auction_id = $auctionId");
  }

  /**
   * Returns all of the auction results belonging to the specified team.
   */
  public static function getAuctionResultsByTeamId($team_id) {
    return AuctionResultDao::createAuctionResultsFromQuery(
        "select * from auction
         where team_id = $team_id
         order by year, cost DESC");
  }

  /**
   * Returns all of the auction results in the specified year.
   */
  public static function getAuctionResultsByYear($year) {
    return AuctionResultDao::createAuctionResultsFromQuery(
        "select * from auction
         where year = $year
         order by auction_id");
  }

  /**
   * Returns all of the auction results where the specified player was auctioned.
   */
  public static function getAuctionResultsByPlayerId($player_id) {
    return AuctionResultDao::createAuctionResultsFromQuery(
        "select * from auction
        where player_id = $player_id
        order by year DESC");
  }

  private static function createAuctionResultFromQuery($query) {
    $auctionArray = AuctionResultDao::createAuctionResultsFromQuery($query);
    if (count($auctionArray) == 1) {
      return $auctionArray[0];
    }
    return null;
  }

  private static function createAuctionResultsFromQuery($query) {
    CommonDao::connectToDb();
    $res = mysql_query($query);
    $auctionResults = array();
    while ($auctionDb = mysql_fetch_assoc($res)) {
      $auctionResults[] = AuctionResultDao::populateAuction($auctionDb);
    }
    return $auctionResults;
  }

  private static function populateAuction($auctionDb) {
    return new AuctionResult($auctionDb["auction_id"], $auctionDb["year"], $auctionDb["team_id"],
        $auctionDb["player_id"], $auctionDb["cost"]);
  }

  /**
   * Returns the earliest auction year.
   */
  public static function getMinimumAuctionYear() {
    return CommonDao::getIntegerValueFromQuery(
        "select min(year) from auction");
  }

  /**
   * Returns the latest auction year.
   */
  public static function getMaximumAuctionYear() {
    return CommonDao::getIntegerValueFromQuery(
        "select max(year) from auction");
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