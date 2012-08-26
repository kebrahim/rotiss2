<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'auctionResult.php');

/**
 *
 */
class AuctionResultDao {
  /**
   * Returns all of the draft picks belonging to the specified team.
   */
  public static function getAuctionResultsByTeamId($team_id) {
    CommonDao::connectToDb();
    $query = "select A.auction_id, A.year, A.team_id, A.player_id, A.cost, A.is_match
                  from auction A
              where A.team_id = $team_id
              order by A.year, A.cost DESC";
    return AuctionResultDao::createAuctionResults($query);
  }

  public static function getAuctionResultsByYear($year) {
    CommonDao::connectToDb();
    $query = "select A.auction_id, A.year, A.team_id, A.player_id, A.cost, A.is_match
              from auction A
              where A.year = $year
              order by A.cost DESC";
    return AuctionResultDao::createAuctionResults($query);
  }

  private static function createAuctionResults($query) {
    $auction_results_db = mysql_query($query);

    $auction_results = array();
    while ($auction_result_db = mysql_fetch_row($auction_results_db)) {
      $auction_results[] = new AuctionResult($auction_result_db[0], $auction_result_db[1],
      $auction_result_db[2], $auction_result_db[3], $auction_result_db[4], $auction_result_db[5]);
    }
    return $auction_results;
  }
}
?>