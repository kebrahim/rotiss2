<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'brogna.php');

/**
 *
 */
class BrognaDao {

  /**
   * Returns all of the brognas belonging to the specified team.
   */
  public static function getBrognasByTeamId($teamId) {
    CommonDao::connectToDb();
    $query = "select B.team_id, B.year, B.total_points, B.banked_points, B.traded_in_points,
                     B.traded_out_points, B.tradeable_points
              from team_points B
              where B.team_id = $teamId
              order by B.year";
    return BrognaDao::createBrognas($query);
  }

  /**
  * Returns the set of brognas belonging to the specified team for the specified year.
  */
  public static function getBrognasByTeamAndYear($teamId, $year) {
    CommonDao::connectToDb();
    $query = "select B.team_id, B.year, B.total_points, B.banked_points, B.traded_in_points,
                     B.traded_out_points, B.tradeable_points
                from team_points B
                where B.team_id = $teamId and B.year = $year";
    $brognas = BrognaDao::createBrognas($query);
    return $brognas[0];
  }

  /**
   * Returns all of the brognas for all teams, within the specified years.
   */
  public static function getBrognasByYear($startYear, $endYear) {
    CommonDao::connectToDb();
    $query = "select B.team_id, B.year, B.total_points, B.banked_points, B.traded_in_points,
                     B.traded_out_points, B.tradeable_points
              from team_points B
              order by B.year, B.team_id";
    return BrognaDao::createBrognas($query);
  }

  private static function createBrognas($query) {
    $brognas_db = mysql_query($query);

    $brognas = array();
    while ($brogna_db = mysql_fetch_row ($brognas_db)) {
      $brognas[] = new Brogna($brogna_db[0], $brogna_db[1], $brogna_db[2], $brogna_db[3],
          $brogna_db[4], $brogna_db[5], $brogna_db[6]);
    }
    return $brognas;
  }

  /**
   * Updates a set of brognas for a specific year and team.
   */
  public static function updateBrognas(Brogna $brognas) {
    CommonDao::connectToDb();
    $query = "update team_points set total_points = " . $brognas->getTotalPoints() . ",
                                     banked_points = " . $brognas->getBankedPoints() . ",
                                     traded_in_points = " . $brognas->getTradedInPoints() . ",
                                     traded_out_points = " . $brognas->getTradedOutPoints() . ",
                                     tradeable_points = " . $brognas->getTradeablePoints() .
                               " where team_id = " . $brognas->getTeam()->getId() .
                               " and year = " . $brognas->getYear();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }
}
?>