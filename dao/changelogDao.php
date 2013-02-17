<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'changelog.php');

/**
 * Handles the storing/retrieving of any type of changes.
 */
class ChangelogDao {

  /**
   * Returns the change with the specified id.
   */
  public static function getChangeById($changeId) {
    return ChangelogDao::createChangeFromQuery(
        "select c.*
         from changelog c
         where c.changelog_id = $changeId");
  }

  /**
   * Returns all of the changes.
   */
  public static function getAllChanges() {
    return ChangelogDao::createChangesFromQuery(
        "select c.*
    	 from changelog c
         order by c.timestamp desc");
  }

  /**
   * Returns all of the changes for the specified team.
   */
  public static function getChangesByTeam($teamId) {
    return ChangelogDao::createChangesFromQuery(
        "select c.*
  	     from changelog c
  	     where c.team_id = $teamId or c.secondary_team_id = $teamId
  	     order by c.timestamp desc");
  }

  private static function createChangeFromQuery($query) {
    $changeArray = ChangelogDao::createChangesFromQuery($query);
	if (count($changeArray) == 1) {
	  return $changeArray[0];
	}
	return null;
  }

  private static function createChangesFromQuery($query) {
    CommonDao::connectToDb();
	$res = mysql_query($query);
    $changesDb = array();
	while($changeDb = mysql_fetch_assoc($res)) {
	  $changesDb[] = ChangelogDao::populateChangelog($changeDb);
	}
	return $changesDb;
  }

  private static function populateChangelog($changeDb) {
    return new Changelog($changeDb["changelog_id"], $changeDb["change_type"], $changeDb["user_id"],
        $changeDb["timestamp"], $changeDb["change_id"], $changeDb["team_id"],
        $changeDb["secondary_team_id"]);
  }

  /**
   * Creates a new change in the 'changelog' table.
   */
  public static function createChange(Changelog $change) {
  	CommonDao::connectToDb();
  	$query = "insert into changelog(change_type, user_id, timestamp, change_id, team_id,
  	              secondary_team_id) values ('" .
  	          $change->getType() . "', " .
  	          $change->getUserId() . ", '" .
  	          $change->getTimestamp() . "', " .
  	          $change->getChangeId() . ", " .
  	          $change->getTeamId() . ", " .
  	          ($change->getSecondaryTeamId() == null ? "null" : $change->getSecondaryTeamId()) .
  	     ")";
  	$result = mysql_query($query);
  	if (!$result) {
  	  echo "Error creating change in DB: " . $change->toString();
      return null;
  	}

  	$idQuery = "select changelog_id from changelog where change_type = '" .
  	     $change->getType() . "' and change_id = " . $change->getChangeId() . " and team_id = "
  	     . $change->getTeamId() . " and timestamp = '" . $change->getTimestamp() . "'";
  	$result = mysql_query($idQuery) or die('Invalid query: ' . mysql_error());
  	$row = mysql_fetch_assoc($result);
  	$change->setId($row["changelog_id"]);
  	return $change;
  }
}

?>
