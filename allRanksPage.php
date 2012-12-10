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
  require_once 'dao/rankDao.php';
  require_once 'dao/teamDao.php';
  require_once 'util/layout.php';
  require_once 'util/time.php';

  // Display nav bar.
  LayoutUtil::displayNavBar(true, LayoutUtil::ALL_RANKS_BUTTON);

  echo "<div class='row-fluid'>
          <div class='span6 offset3 center'>";
  
  $rankYear = TimeUtil::getYearBasedOnEndOfSeason();
  echo "<h3>Offseason Ranks $rankYear</h3>";

  // navigation links
  echo "<a href='rankPage.php'>My Ranks</a>&nbsp
        <a href='allRanksPage.php'>All Ranks</a><br/><br/>";
  echo "  </div>
        </div>
        <div class='row-fluid'>
          <div class='span12 center'><br/>";
  
  // display ranked players
  $teams = TeamDao::getAllTeams();
  echo "<table border id='ranks' 
           class='table vertmiddle table-striped table-condensed table-bordered center'>
          <thead><tr><th colspan=2>Team</th>";
  for ($i = 1; $i <= 10; $i++) {
  	echo "<th>" . $i . "'s</th>";
  }
  echo "<th>Total</th></tr></thead>";
  foreach ($teams as $team) {
    $totalCount = RankDao::getTotalRankCount($team->getId(), $rankYear);
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
  echo "</div>"; // span12
  echo "</div>"; // row-fluid
  
  // Footer
  LayoutUtil::displayFooter();
?>
</body>
</html>