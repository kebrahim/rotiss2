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
	if ($player != null) {
	  return "<td>" . $player->getHeadshotImg(24,32) . "</td>
	      <td>" . $player->getNameLink(true) . "</td>";
    } else {
	  return "<td colspan=2>--</td>";
	}
  }
}

?>