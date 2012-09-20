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
    
    $players = PlayerDao::createPlayersFromQuery($query);
    return $players[0];
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
  	$query = "SELECT C.* FROM contract C
  	          WHERE C.player_id = " . $playerId . " AND C.is_auction = 1
  	          AND C.start_year = " . $startYear;
  	$res = mysql_query($query);
  	return (mysql_num_rows($res) > 0);
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