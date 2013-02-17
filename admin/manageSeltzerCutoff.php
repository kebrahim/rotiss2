<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
  SessionUtil::logoutUserIfNotLoggedIn("admin/manageSeltzerCutoff.php");
?>

<!DOCTYPE html>
<html>

<?php
  require_once '../util/layout.php';
  LayoutUtil::displayHeadTag("Manage Seltzer Cutoff", false);
?>

<body>

<?php
  require_once '../dao/ballDao.php';
  require_once '../dao/draftPickDao.php';
  require_once '../dao/teamDao.php';
  require_once '../util/teamManager.php';

  // Display nav bar.
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_KEEPERS_BUTTON);
  $year = TimeUtil::getCurrentYear();

  echo "<div class='row-fluid'>
          <div class='span8 center offset2'>
            <h3>Seltzer Cutoff $year</h3>
          </div>
        </div>";

  echo "<form action='manageSeltzerCutoff.php' method='post'>";
  echo "  <div class='row-fluid'>
            <div class='span12 center'>";

  if (isset($_POST['save'])) {
    $draftPick = DraftPickDao::getDraftPickById($_POST['calculatedPickId']);
    $draftPick->setSeltzerCutoff(true);
    DraftPickDao::updateDraftPick($draftPick);

    echo "<br/><div class='alert alert-success'>
            <strong>Saved Calculated Cutoff Pick!</strong>
          </div>";
  }

  function showPickTable($draftPick, $numBalls) {
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
            <tr>
              <td><label>Overall</label></td>
              <td>" . (($draftPick != null) ? $draftPick->getOverallPick($numBalls) : "") . "</td>
            </tr>
            <tr>
              <td><label>Round</label></td>
              <td>" . (($draftPick != null) ? $draftPick->getRound() : "") . "</td>
            </tr>
            <tr>
              <td><label>Pick</label></td>
              <td>" . (($draftPick != null) ? $draftPick->getPick() : "") . "</td>
            </tr>
          </table>";
  }

  // Show persisted cutoff pick
  echo "<br/>
        <div class='row-fluid'>
          <div class='span6 center'>";
  echo "<h4>Current Cutoff Pick</h4>";
  $numBalls = BallDao::getNumPingPongBallsByYear($year);
  showPickTable(DraftPickDao::getSeltzerCutoffPick($year), $numBalls);
  echo "  </div>";

  // Show calculated cutoff pick
  $numContracts = ContractDao::getNumberNonZeroContracts($year);
  $calculatedOverallPick = DraftPick::SELTZER_CUTOFF - ($numBalls + $numContracts);
  $round = (floor($calculatedOverallPick / 16)) + 1;
  $pick = $calculatedOverallPick % 16;
  echo "  <div class='span6 center'>
            <h4>Calculated Cutoff Pick</h4>";
  $calculatedPick = DraftPickDao::getDraftPickByYearRoundPick($year, $round, $pick);
  showPickTable($calculatedPick, $numBalls);
  echo "<input type='hidden' name='calculatedPickId' value='" . $calculatedPick->getId() . "'>";
  echo "  </div>
        </div>";

  // button to save
  echo "<p>
          <button class='btn btn-primary' name='save' type='submit'>Save Calculated Cutoff</button>
          &nbsp&nbsp
          <a href='manageKeepers.php' class='btn'>Return to Keepers</a>
        </p>";

  // show calculation
  echo "<div class='row-fluid'><div class='span12 center'>";
  echo "<br/>
        <div class='alert alert-info'>
          Seltzer Cutoff (" . DraftPick::SELTZER_CUTOFF. ") - Total Contracts (" . $numContracts .
          ") - Total Ping Pong Balls (" . $numBalls . ") = <strong>Draft Pick #" .
          $calculatedOverallPick . "</strong><br/>
          Draft Pick #" . $calculatedOverallPick . " + Total Ping Pong Balls (" . $numBalls .
          ") = <strong>Overall Pick #" . ($calculatedOverallPick + $numBalls) . "</strong>
        </div>";
  echo "<h4>Team Breakdown</h4>
        <table class='table vertmiddle table-striped table-condensed table-bordered center'>
          <thead><tr>
            <th colspan=2>Team</th>
            <th>Contracts</th>
            <th>Ping pong balls</th>
          </tr></thead>";

  // team breakdown
  $totalContracts = 0;
  $totalBalls = 0;
  $teams = TeamDao::getAllTeams();
  foreach ($teams as $team) {
    $teamContracts = ContractDao::getNumberNonZeroContractsByTeam($year, $team->getId());
    $teamBalls = BallDao::getNumPingPongBallsByTeamYear($year, $team->getId());
    echo "<tr>" .
            TeamManager::getAbbreviationAndLogoRowAtLevel($team, false) . "
            <td>" . $teamContracts . "</td>
            <td>" . $teamBalls . "</td>
          </tr>";
    $totalContracts += $teamContracts;
    $totalBalls += $teamBalls;
  }
  echo "<tr>
          <td colspan=2><strong>Total</strong></td>
          <td><strong>$totalContracts</strong></td>
          <td><strong>$totalBalls</strong></td>
        </tr>";
  echo "</table></div></div>";

  echo "</div></div>
        </form>";

  // Footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
