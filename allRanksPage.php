<?php
  require_once 'util/sessions.php';
  SessionUtil::logoutUserIfNotLoggedIn("allRanksPage.php");
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - All Ranks</title>
<link href='css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='css/stpetes.css' rel='stylesheet' type='text/css'>
</head>

<body>
<?php
  require_once 'dao/cumulativeRankDao.php';
  require_once 'dao/rankDao.php';
  require_once 'dao/teamDao.php';
  require_once 'util/layout.php';
  require_once 'util/playerManager.php';
  require_once 'util/teamManager.php';
  require_once 'util/time.php';

  // Display nav bar.
  LayoutUtil::displayNavBar(true, LayoutUtil::ALL_RANKS_BUTTON);

  // if any cumulative ranks exist for the rank year, then display players with their cumulative
  // ranks; otherwise, display the list of teams with how many ranks they've made so far.
  $rankYear = TimeUtil::getYearBasedOnEndOfSeason();
  $rankingOver = CumulativeRankDao::hasCumulativeRanks($rankYear);

  echo "<div class='row-fluid'>
          <div class='span6 offset3 center'>";

  echo "<h3>Offseason Ranks $rankYear</h3>";

  // navigation links
  echo "<a href='rankPage.php'>My Ranks</a>&nbsp
        <a href='allRanksPage.php'>All Ranks</a><br/><br/>";
  echo "  </div>
        </div>
        <div class='row-fluid'>
          <div class='span12 center'><br/>";

  if ($rankingOver) {
    // TODO support sorting by column
    $cumulativeRanks = CumulativeRankDao::getCumulativeRanksByYear($rankYear);
    echo "<table border id='ranks'
                 class='table vertmiddle table-striped table-condensed table-bordered center'>
          <thead><tr><th colspan=2>Player</th><th>MLB Team</th><th>Age</th><th>Position</th>
                     <th colspan=2>Fantasy Team</th><th>Rank</th></tr></thead>";
    foreach ($cumulativeRanks as $cumulativeRank) {
      echo "<tr";
      if ($cumulativeRank->isPlaceholder()) {
        echo " class='placeholder_row'";
      }
      echo ">" .
              PlayerManager::getNameAndHeadshotRow($cumulativeRank->getPlayer()) . "
              <td>" . $cumulativeRank->getPlayer()->getMlbTeam()->getImageTag(30, 30) . "</td>
              <td>" . $cumulativeRank->getPlayer()->getAge() . "</td>
              <td>" . $cumulativeRank->getPlayer()->getPositionString() . "</td>" .
              TeamManager::getNameAndLogoRow($cumulativeRank->getPlayer()->getFantasyTeam()) .
             "<td>" . $cumulativeRank->getRank() . "</td>
            </tr>";
    }
    echo "</table>";
  } else {
    // display number of ranked players by team
    $teams = TeamDao::getAllTeams();
    echo "<table border id='ranks'
             class='table vertmiddle table-striped table-condensed table-bordered center'>
            <thead><tr><th colspan=2>Team</th>";
    for ($i = 1; $i <= 10; $i++) {
  	  echo "<th>" . $i . "'s</th>";
    }
    echo "<th>Total</th></tr></thead>";
    foreach ($teams as $team) {
  	  echo "<tr>";
  	  echo "<td><img height=36 width=36 src='" . $team->getSportslineImageUrl() . "'></td>
  	            <td>" . $team->getNameLink(true) . "</td>";

      // individual ranks
      $rankCountArray = RankDao::getRankCount($team->getId(), $rankYear);
  	  $rankCount = 0;
  	  for ($i = 1; $i <= 10; $i++) {
  	    $numRanks = isset($rankCountArray[$i]) ? $rankCountArray[$i] : 0;
  	    echo "<td>" . $numRanks . "</td>";
  	    $rankCount += $numRanks;
      }
  	  echo "<td>($rankCount / 150)
  	          <div class='progress progress-striped smallprogress'>
  	          <div class=\"bar ";
  	  if ($rankCount < 150) {
  	    echo "bar";
  	  } else if ($rankCount > 150) {
  	    echo "bar-danger";
  	  } else {
  	    echo "bar-success";
  	  }
      echo "\" style=\"width: " . (($rankCount / 150) * 100) . "%;\"></div>
   	       </div>";
  	  echo "</td></tr>";
    }
    echo "</table>";
  }
  echo "</div>"; // span12
  echo "</div>"; // row-fluid

  // Footer
  LayoutUtil::displayFooter();
?>
</body>
</html>