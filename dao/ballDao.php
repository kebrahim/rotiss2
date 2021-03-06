<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'ball.php');

/**
 * DAO class for storing/retrieving ping pong balls.
 */
class BallDao {

  /**
   * Returns all of the ping pong balls belonging to the specified team.
   */
  public static function getPingPongBallsByTeamId($teamId) {
    return BallDao::createPingPongBallsByQuery(
        "select P.*
         from ping_pong P
         where P.team_id = $teamId
         order by P.year ASC, P.cost DESC, P.ordinal ASC");
  }

  /**
   * Returns all of the ping pong balls in the specified year.
   */
  public static function getPingPongBallsByYear($year) {
    return BallDao::createPingPongBallsByQuery(
        "select P.*
         from ping_pong P
         where P.year = $year
         order by P.cost DESC, P.ordinal ASC");
  }

  /**
   * Returns the ping pong ball associated w/ the specified id.
   */
  public static function getPingPongBallById($ballId) {
    return BallDao::createPingPongBallByQuery(
        "select p.*
         from ping_pong p
         where p.ping_pong_id = $ballId");
  }

  /**
   * Returns all of the ping pong balls where the specified player was selected.
   */
  public static function getPingPongBallsByPlayerId($playerId) {
    return BallDao::createPingPongBallsByQuery(
        "select P.*
         from ping_pong P
         where P.player_id = $playerId
         order by P.year DESC");
  }

  /**
   * Returns all of the ping pong balls where the specified player was selected during the
   * specified year.
   */
  public static function getPingPongBallsByPlayerYear($playerId, $year) {
    return BallDao::createPingPongBallsByQuery(
        "select P.*
         from ping_pong P
         where P.player_id = $playerId
         and P.year = $year");
  }

  /**
   * Returns the number of ping pong balls belonging to the specified team during the specified
   * year.
   */
  public static function getNumPingPongBallsByTeamYear($year, $teamId) {
    return CommonDao::getIntegerValueFromQuery(
        "select count(*)
         from ping_pong P
         where P.team_id = $teamId
         and P.year = $year");
  }

  /**
   * Returns the number of ping pong balls during the specified year.
   */
  public static function getNumPingPongBallsByYear($year) {
    return CommonDao::getIntegerValueFromQuery(
        "select count(*)
        from ping_pong
        where year = $year");
  }

  private static function createPingPongBallByQuery($query) {
    $balls = BallDao::createPingPongBallsByQuery($query);
    if (count($balls) == 1) {
      return $balls[0];
    }
    return null;
  }

  private static function createPingPongBallsByQuery($query) {
    CommonDao::connectToDb();
    $res = mysql_query($query);
    $balls = array();
    while($ballDb = mysql_fetch_assoc($res)) {
      $balls[] = BallDao::populatePingPongBall($ballDb);
    }
    return $balls;
  }

  private static function populatePingPongBall($ballDb) {
    return new PingPongBall($ballDb["ping_pong_id"], $ballDb["year"], $ballDb["cost"],
        $ballDb["team_id"], $ballDb["player_id"], $ballDb["ordinal"]);
  }

  /**
   * Inserts the specified PingPongBall in the 'ping_pong' table and returns the same PingPongBall
   * with its id set.
   */
  public static function createPingPongBall(PingPongBall $pingPongBall) {
    CommonDao::connectToDb();
  	$query = "insert into ping_pong(year, cost, team_id)
  	    values (" . $pingPongBall->getYear() . ", " . $pingPongBall->getCost() .
  	    ", " . $pingPongBall->getTeam()->getId() . ")";
  	$result = mysql_query($query);
  	if (!$result) {
  	  echo "Ping pong ball " . $pingPongBall->toString() . " already exists in DB. Try again.";
  	  return null;
  	}

  	$idQuery = "select max(ping_pong_id) as new_ping_pong_id from ping_pong
  	            where year = " . $pingPongBall->getYear() .
  	          " and team_id = " . $pingPongBall->getTeam()->getId() . " and cost = " .
  	    $pingPongBall->getCost();
  	$result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
  	$row = mysql_fetch_assoc($result);
  	$pingPongBall->setId($row["new_ping_pong_id"]);
  	return $pingPongBall;
  }

  public static function updatePingPongBall(PingPongBall $pingPongBall) {
    CommonDao::connectToDb();
    $query = "update ping_pong set year = " . $pingPongBall->getYear() . ",
                                   cost = " . $pingPongBall->getCost() . ",
                                   team_id = " . $pingPongBall->getTeam()->getId() . ",
                                   player_id = " . $pingPongBall->getPlayerId() . ",
                                   ordinal = " . $pingPongBall->getOrdinal() .
                             " where ping_pong_id = " . $pingPongBall->getId();
    return mysql_query($query);
  }

  /**
   * Deletes all of the ping pong balls.
   */
  public static function deleteAllPingPongBalls() {
  	CommonDao::connectToDb();
  	$query = "delete from ping_pong where ping_pong_id > 0";
  	mysql_query($query);
  }

  public static function getMinimumYear() {
    return CommonDao::getIntegerValueFromQuery("select min(year) from ping_pong");
  }

  public static function getMaximumYear() {
    return CommonDao::getIntegerValueFromQuery("select max(year) from ping_pong");
  }
}
?>