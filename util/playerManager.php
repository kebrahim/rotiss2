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
}

?>