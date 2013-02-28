<?php
  require_once 'commonManager.php';
  CommonManager::requireFileIn('/../dao/', 'changelogDao.php');

  /**
   * Handles changelog-related methods
   */
  class ChangelogManager {

    /**
     * Displays a table describing all of the keeper results for the specified year.
     */
    public static function displayKeeperSummary($year) {
  	  echo "<h4>Keeper Results</h4>
  	        <table class='table vertmiddle table-striped table-condensed table-bordered center
                          smallfonttable'>
              <thead><tr>
                <th colspan=2>Team</th>
                <th>New Contracts</th>
                <th>Bought Out Contracts</th>
                <th>Ping Pong Balls</th>
                <th>Banked</th>
                <th>Contracts Paid</th>
              </tr></thead>";

      $teams = TeamDao::getAllTeams();
      foreach ($teams as $team) {
        $keeperChanges = ChangelogDao::getKeeperChangesByTeam($team->getId(), $year);
        if (ChangelogManager::hasKeepers($keeperChanges)) {
          echo "<tr>" .
                  TeamManager::getAbbreviationAndLogoRowAtLevel($team, false) .
                 "<td>" . ChangelogManager::displayChanges(
                      $keeperChanges, Changelog::CONTRACT_SIGNED_TYPE) . "</td>
                  <td>" . ChangelogManager::displayChanges(
                      $keeperChanges, Changelog::BUYOUT_CONTRACT_TYPE) . "</td>
                  <td>" . ChangelogManager::displayChanges(
                      $keeperChanges, Changelog::PING_PONG_BALL_TYPE) . "</td>
                  <td><strong>" . ChangelogManager::displayChanges(
                      $keeperChanges, Changelog::BANK_TYPE) . "</strong></td>
                  <td>" . ChangelogManager::displayChanges(
                      $keeperChanges, Changelog::CONTRACT_PAID_TYPE) . "</td>
                </tr>";
        }
      }
      echo "</table>";
    }

    /**
     * Returns true if the array of changes contain keeper changes.
     */
    private static function hasKeepers($changes) {
      foreach ($changes as $change) {
        if (($change->getType() == Changelog::CONTRACT_SIGNED_TYPE) &&
            (!$change->getChange()->isKeeper())) {
          continue;
        }
        return true;
      }
      return false;
    }

    /**
     * Returns a string with the changes in the specified array, filtered by the specified type.
     */
    private static function displayChanges($changes, $changeType) {
      $changeMsg = "";
      $firstChange = true;
      foreach ($changes as $change) {
        if ($change->getType() == $changeType) {
          if (($change->getType() == Changelog::CONTRACT_SIGNED_TYPE) &&
             (!$change->getChange()->isKeeper())) {
            continue;
          }
          if ($firstChange) {
            $firstChange = false;
          } else {
            $changeMsg .= "<br/>";
          }
          $changeMsg .= $change->getKeeperDetails();
        }
      }
      return $changeMsg;
    }
  }
?>
