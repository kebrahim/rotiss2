<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'player.php');

class PlayerDao {

  /**
   * Returns player information for the specified player ID and null if the player ID is not found.
   */
  static function getPlayerById($playerId) {
    CommonDao::connectToDb();
    $query = "select player_id, first_name, last_name, birth_date, mlb_team_id, sportsline_id
              from player
              where player_id = $playerId";
    $res = mysql_query($query);
    $playerDb = mysql_fetch_assoc($res);
    if ($playerDb == null) {
      return null;
    }
    return new Player($playerDb["player_id"], $playerDb["first_name"], $playerDb["last_name"],
        $playerDb["birth_date"], $playerDb["mlb_team_id"], $playerDb["sportsline_id"]);
  }

  /**
   * Inserts the specified player in the 'player' table and returns the same Player with its id set.
   */
  static function createPlayer($player) {
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