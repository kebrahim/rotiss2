<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'player.php');

class PlayerDao {

  /**
   * Returns player information for the specified player ID and null if the player ID is not found.
   */
  static function getPlayerById($playerId) {
    CommonDao::connectToDb();
    $query = "select * from player
              where player_id = $playerId";
    return PlayerDao::createPlayerFromQuery($query);
  }

  /**
   * Returns all of the players eligible for auction in the specified year.
   */
  public static function getPlayersForAuction($year) {
  	CommonDao::connectToDb();

  	// get all players whose contract ended last year.
  	$lastYear = $year - 1;
  	$query = "SELECT P.*
  	          FROM player P, contract C
  	          WHERE P.player_id = C.player_id and C.is_auction = 0 and C.end_year = " . $lastYear
  	          . " ORDER BY P.last_name, P.first_name";
  	$eligiblePlayers = PlayerDao::createPlayersFromQuery($query);

  	// filter out players who already have an auction contract for this year.
  	$playersForAuction = array();
  	foreach ($eligiblePlayers as $eligiblePlayer) {
  	  if (!PlayerDao::hasAuctionContract($eligiblePlayer->getId(), $year)) {
  	  	$playersForAuction[] = $eligiblePlayer;
  	  }
  	}
  	return $playersForAuction;
  }

  /**
   * Returns true if the player with the specified id has an auction contract starting in the
   * specified year.
   */
  private static function hasAuctionContract($playerId, $startYear) {
  	CommonDao::connectToDb();
  	$query = "SELECT C.*
  	          FROM contract C
  	          WHERE C.player_id = " . $playerId . " AND C.is_auction = 1
  	          AND C.start_year = " . $startYear;
  	$res = mysql_query($query);
  	return (mysql_num_rows($res) > 0);
  }

  /**
   * Returns true if the player w/ the specified id has a contract that either ended in the
   * specified year or is still active after the specified year.
   */
  public static function hasContractForPlaceholders($playerId, $year) {
  	CommonDao::connectToDb();
  	$query = "SELECT c.*
  	          FROM contract c
  	          WHERE c.player_id = " . $playerId . "
  	          AND c.is_bought_out = 0
  	          AND c.end_year >= " . $year;
  	$res = mysql_query($query);
  	return (mysql_num_rows($res) > 0);
  }

  /**
   * Return all players eligible to be ranked by the specified team.
   */
  public static function getPlayersForRanking($teamId) {
  	CommonDao::connectToDb();

  	// get all players in team_player who aren't assigned to specified team & haven't been ranked
  	// by specified team
  	$query = "select p.*
              from player p, team_player tp
              where p.player_id = tp.player_id and tp.team_id <> " . $teamId .
              " and p.player_id not in (
                  select player_id
                  from rank
                  where team_id = " . $teamId . ")";
  	return PlayerDao::createPlayersFromQuery($query);
  }

  /**
   * Returns a list of players eligible to be kept in the specified year.
   */
  // TODO filter by team
  public static function getEligibleKeepers(Team $team, $year) {
    // first, get all players
    $query = "select P.*
        	  from player P
              order by P.last_name, P.first_name";
    $allPlayers = PlayerDao::createPlayersFromQuery($query);

    // filter out players who currently have a contract or a contract ended for them last year.
    $eligibleKeepers = array();
    foreach ($allPlayers as $player) {
      if (!PlayerDao::hasContractForPlaceholders($player->getId(), $year - 1)) {
        $eligibleKeepers[] = $player;
      }
    }
    return $eligibleKeepers;
  }

  private static function createPlayerFromQuery($query) {
    $playerArray = PlayerDao::createPlayersFromQuery($query);
    if (count($playerArray) == 1) {
      return $playerArray[0];
    }
    return null;
  }

  private static function createPlayersFromQuery($query) {
    $res = mysql_query($query);
    $playersDb = array();
    while($playerDb = mysql_fetch_assoc($res)) {
      $playersDb[] = new Player($playerDb["player_id"], $playerDb["first_name"],
          $playerDb["last_name"], $playerDb["birth_date"], $playerDb["mlb_team_id"],
          $playerDb["sportsline_id"]);
    }
    return $playersDb;
  }

  /**
   * Inserts the specified player in the 'player' table and returns the same Player with its id set.
   */
  static function createPlayer(Player $player) {
    CommonDao::connectToDb();
    $query = "insert into player(first_name, last_name, mlb_team_id, birth_date, sportsline_id)
                 values ('" .
             $player->getFirstName() . "', '" . $player->getLastName() . "', " .
             $player->getMlbTeam()->getId() . ", '" . $player->getBirthDate() . "', " .
             $player->getSportslineId() . ")";
    $result = mysql_query($query);
    if (!$result) {
      echo "Player " . $player->getFullName() . " already exists in DB. Try again.";
      return null;
    }

    $idQuery = "select player_id from player where first_name = '" . $player->getFirstName() .
        "' and last_name = '" . $player->getLastName() . "' and birth_date = '" .
        $player->getBirthDate() . "'";
    $result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
    $row = mysql_fetch_assoc($result);
    $player->setId($row["player_id"]);
    return $player;
  }

  /**
   * Updates the specified player in the 'player' table.
   */
  static function updatePlayer($player) {
    CommonDao::connectToDb();
    $query = "update player set first_name = '" . $player->getFirstName() . "',
                                last_name = '" . $player->getLastName() . "',
                                mlb_team_id = " . $player->getMlbTeam()->getId() . ",
                                birth_date = '" . $player->getBirthDate() . "',
                                sportsline_id = " . $player->getSportslineId() .
             " where player_id = " . $player->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }
}
?>