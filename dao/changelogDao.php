<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'changelog.php');

/**
 * Handles the storing/retrieving of any type of changes.
 */
class ChangelogDao {
  
  /**
   * Returns all of the changes for the specified team.
   */
  public static function getChangesByTeam($teamId) {
  	CommonDao::connectToDb();
  	$query = "select c.*
  	          from changelog c
  	          where c.team_id = $teamId";
  	return ChangelogDao::createChangesFromQuery($query);
  }
	
  private static function createChangeFromQuery($query) {
    $changeArray = ChangelogDao::createChangesFromQuery($query);
	if (count($changeArray) == 1) {
	  return $changeArray[0];
	}
	return null;
  }
	
  private static function createChangesFromQuery($query) {
	$res = mysql_query($query);
    $changesDb = array();
	while($changeDb = mysql_fetch_assoc($res)) {
	  $changesDb[] = new Changelog($changeDb["changelog_id"], $changeDb["change_type"],
	      $changeDb["user_id"], $changeDb["timestamp"], $changeDb["change_id"],
	  	  $changeDb["team_id"]);
	}
	return $changesDb;
  }
  
  /**
   * Creates a new change in the 'changelog' table.
   */
  public static function createChange(Changelog $change) {
  	CommonDao::connectToDb();
  	$query = "insert into changelog(change_type, user_id, timestamp, change_id, team_id)
  	          values ('" .
  	          $change->getType() . "', " .
  	          $change->getUserId() . ", '" .
  	          $change->getTimestamp() . "', " .
  	          $change->getChangeId() . ", " .
  	          $change->getTeamId() . ")";
  	
  	$result = mysql_query($query);
  	if (!$result) {
  	  echo "Error creating change in DB: " . $change->toString();
      return null;
  	}
  	 
  	$idQuery = "select changelog_id from changelog where change_type = '" . 
  	     $change->getType() . "' and change_id = " . $change->getChangeId();
  	$result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
  	$row = mysql_fetch_assoc($result);
  	$change->setId($row["changelog_id"]);
  	return $change;
  }
}

?>