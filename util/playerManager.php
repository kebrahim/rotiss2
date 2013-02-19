<?php

require_once 'commonUtil.php';
CommonUtil::requireFileIn('/../dao/', 'playerDao.php');

/**
 * Handles utility methods for player objects.
 */
class PlayerManager {

  /**
   * Returns a row in a table with the specified player name and headshot.
   */
  public static function getNameAndHeadshotRow($player) {
    return PlayerManager::getNameAndHeadshotRowAtLevel($player, true);
  }

  public static function getNameAndHeadshotRowAtLevel($player, $isTopLevel) {
  	if ($player != null) {
	  return "<td>" . $player->getMiniHeadshotImg() . "</td>
	      <td>" . $player->getNameLink($isTopLevel) . "</td>";
    } else {
	  return "<td colspan=2>--</td>";
	}
  }

  public static function getPlayerDetailsAndHeadshotRowAtLevel($player, $linkText, $isTopLevel) {
    if ($player != null) {
      return "<td>" . $player->getMiniHeadshotImg() . "</td>
              <td>" . $player->getIdLink($isTopLevel, $linkText) . "</td>";
    } else {
      return "<td colspan=2>--</td>";
    }
  }

  public static function displayAttribute($player, $attribute) {
    if ($player == null) {
      return;
    }
    switch ($attribute) {
      case "baseposition": {
        echo $player->getBasePosition();
        break;
      }
      case "minorseltzerstatlabel": {
        echo ($player->getBasePosition() == Position::PITCHER) ?
            "<label for='seltzer_stat'>Innings Pitched</label>" :
            "<label for='seltzer_stat'>At Bats</label>";
        break;
      }
    }
  }
}

$displayType = null;
if (isset($_REQUEST["type"])) {
  $displayType = $_REQUEST["type"];
}

if ($displayType == "attribute") {
  $player = null;
  if (isset($_REQUEST["player_id"])) {
    $player = PlayerDao::getPlayerById($_REQUEST["player_id"]);
  }
  PlayerManager::displayAttribute($player, $_REQUEST["attr"]);
}

?>