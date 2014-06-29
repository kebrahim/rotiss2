<?php
  require_once 'util/sessions.php';

  $redirectUrl = "history.php";
  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>

<?php
  require_once 'util/layout.php';
  LayoutUtil::displayHeadTag("History", true);
?>
<body>

<?php
  require_once 'dao/seasonDao.php';

  // Nav bar
  LayoutUtil::displayNavBar(true, LayoutUtil::HISTORY_BUTTON);

  function displayHistoryTable($sport) {
    $seasons = SeasonDao::getSeasonsBySport($sport);
    $columns = ($sport == Season::COOKOFF_TYPE) ?
        array("Year", "Champion", "Dish", "Runner up", "Dish") :
        array("Year", "Champion", "Team", "Runner up", "Team", "Result");
    echo "<table class='table center vertmiddle table-striped table-condensed table-bordered smallfonttable'>
            <thead><tr>";
    foreach ($columns as $column) {
      echo "<th>" . $column . "</th>";
    }
    echo "</tr></thead>";
    foreach ($seasons as $season) {
      echo "<tr>
              <td>" . $season->getYear() . "</td>
              <td>" . $season->getWinningOwnerName() . "</td>
              <td>" . $season->getWinningTeamName() . "</td>
              <td>" . $season->getRunnerUpOwnerName() . "</td>
              <td>" . $season->getRunnerUpTeamName() . "</td>";
      if ($sport != Season::COOKOFF_TYPE) {
      	echo "<td>" . $season->getResult() . "</td>";
      }
      echo "</tr>";
    }
    echo "</table>";
  }
?>

<div class='row-fluid'>
  <div class='span12 center'>
    <h3>League History</h3>
  </div>
</div>
<div class='row-fluid'>
  <div class='span12 center'>
    <h4>Baseball Rotiss</h4>
    <?php displayHistoryTable(Season::BASEBALL_TYPE); ?>
  </div>
</div>
<div class='row-fluid'>
  <div class='span12 center'>
    <h4>Winter Meetings Cookoff</h4>
    <?php displayHistoryTable(Season::COOKOFF_TYPE); ?>
  </div>
</div>
<div class='row-fluid'>
  <div class='span12 center'>
    <h4>Football Rotiss</h4>
    <?php displayHistoryTable(Season::FOOTBALL_TYPE); ?>    
  </div>
</div>
<div class='row-fluid'>
  <div class='span12 center'>
    <h4>Basketball Rotiss</h4>
    <?php displayHistoryTable(Season::BASKETBALL_TYPE); ?>
  </div>
</div>
 
<?php

  // Display footer
  LayoutUtil::displayFooter();
?>

</body>
</html>