<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'position.php');

class PositionDao {
  /**
   * Returns all positions
   */
  static function getPositions() {
    CommonDao::connectToDb();
    $query = "select P.*
              from position P
              order by P.position_id";
    return PositionDao::createPositionsFromQuery($query);
  }

  /**
   * Returns an array of positions matching the specified position Ids.
   */
  static function getPositionsByPositionIds($positionIds) {
    CommonDao::connectToDb();
    $query = "select P.*
              from position P where P.position_id in (";
    $firstPos = true;
    foreach ($positionIds as $positionId) {
      if (!$firstPos) {
        $query .= ",";
      } else {
        $firstPos = false;
      }
      $query .= $positionId;
    }
    $query .= ")";
    return PositionDao::createPositionsFromQuery($query);
  }

  /**
   * Returns all of the positions for the specified player ID
   */
  static function getPositionsByPlayerId($playerId) {
    CommonDao::connectToDb();
    $query = "select P.*
    	      from player_position PP, position P
              where PP.player_id = $playerId and PP.position_id = P.position_id";
    return PositionDao::createPositionsFromQuery($query);
  }
  
  /**
   * Returns the position with the specified abbreviation or null if none exists.
   */
  static function getPositionByAbbreviation($abbreviation) {
  	CommonDao::connectToDb();
  	$query = "select P.*
  	          from position P
  	          where P.abbreviation = '" . $abbreviation . "'";
  	return PositionDao::createPositionFromQuery($query);  	 
  }

  /**
   * Assigns the specified positions to the specified player.
   */
  static function assignPositionsToPlayer($positionsToAssign, $player) {
    CommonDao::connectToDb();
    $currentPositions = PositionDao::getPositionsByPlayerId($player->getId());

    // If position is not already assigned, add it.
    foreach ($positionsToAssign as $positionToAssign) {
      if (!PositionDao::containsPosition($currentPositions, $positionToAssign)) {
        PositionDao::assignPosition($positionToAssign->getId(), $player->getId());
      }
    }
    foreach ($currentPositions as $currentPosition) {
      // If current position is not being assigned, remove it.
      if (!PositionDao::containsPosition($positionsToAssign, $currentPosition)) {
        PositionDao::removePosition($currentPosition->getId(), $player->getId());
      }
    }
  }

  private static function assignPosition($positionId, $playerId) {
    $query = "insert into player_position(player_id, position_id) values (" .
              $playerId . ", " . $positionId . ")";
    $result = mysql_query($query) or die(mysql_error());
  }

  private static function removePosition($positionId, $playerId) {
    $query = "delete from player_position where player_id = " . $playerId .
             " and position_id = " . $positionId;
    $result = mysql_query($query) or die(mysql_error());
  }

  private static function containsPosition($positions, $pos) {
    foreach ($positions as $position) {
      if ($position->getId() == $pos->getId()) {
        return true;
      }
    }
    return false;
  }

  private static function createPositionFromQuery($query) {
  	$positionArray = PositionDao::createPositionsFromQuery($query);
  	if (count($positionArray) == 1) {
  	  return $positionArray[0];
  	}
  	return null;
  }
  
  private static function createPositionsFromQuery($query) {
    $res = mysql_query($query);
    $positions = array();
    while ($position_db =  mysql_fetch_assoc($res)) {
      $positions[] = new Position($position_db["position_id"], $position_db["position_name"], 
          $position_db["abbreviation"]);
    }
    return $positions;
  }
  
  /**
   * Deletes all of the player-position associations.
   */
  public static function deleteAllPlayerAssociations() {
  	CommonDao::connectToDb();
  	$query = "delete from player_position where player_id > 0";
  	mysql_query($query);
  }
}
?>