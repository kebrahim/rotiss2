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
    CommonDao::connectToDb();
    $query = "select P.ping_pong_id, P.year, P.cost, P.team_id, P.player_id
              from ping_pong P
              where P.team_id = $teamId
              order by P.year ASC, P.cost DESC";
    return BallDao::createPingPongBalls($query);
  }

  /**
   * Returns all of the ping pong balls in the specified year.
   */
  public static function getPingPongBallsByYear($year) {
    CommonDao::connectToDb();
    $query = "select P.ping_pong_id, P.year, P.cost, P.team_id, P.player_id
              from ping_pong P
              where P.year = $year
              order by P.cost DESC";
    return BallDao::createPingPongBalls($query);
  }

  /**
   * Returns the ping pong ball associated w/ the specified id.
   */
  public static function getPingPongBallById($ballId) {
    CommonDao::connectToDb();
    $query = "select P.ping_pong_id, P.year, P.cost, P.team_id, P.player_id
              from ping_pong P
              where P.ping_pong_id = $ballId";
    $balls = BallDao::createPingPongBalls($query);
    return $balls[0];
  }

  private static function createPingPongBalls($query) {
    $balls_db = mysql_query($query);

    $balls = array();
    while ($ball_db = mysql_fetch_row ($balls_db)) {
      $balls[] = new PingPongBall($ball_db[0], $ball_db[1], $ball_db[2], $ball_db[3], $ball_db[4]);
    }
    return $balls;
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

  	$idQuery = "select ping_pong_id from ping_pong where year = " . $pingPongBall->getYear() .
  	    " and team_id = " . $pingPongBall->getTeam()->getId() . " and cost = " .
  	    $pingPongBall->getCost();
  	$result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
  	$row = mysql_fetch_assoc($result);
  	$pingPongBall->setId($row["ping_pong_id"]);
  	return $pingPongBall;
  }

  public static function updatePingPongBall(PingPongBall $pingPongBall) {
    CommonDao::connectToDb();
    $query = "update ping_pong set year = " . $pingPongBall->getYear() . ",
                                   cost = " . $pingPongBall->getCost() . ",
                                   team_id = " . $pingPongBall->getTeam()->getId() . ",
                                   player_id = " . $pingPongBall->getPlayerId() .
                             " where ping_pong_id = " . $pingPongBall->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }
}
?>