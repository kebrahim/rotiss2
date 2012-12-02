<?php
  require_once 'util/sessions.php';
  SessionUtil::checkUserIsLoggedIn();
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
          <div class='span12 center'>";
  
  $rankYear = TimeUtil::getYearBasedOnEndOfSeason();
  echo "<div class='bodycenter'><h1>Offseason Ranks $rankYear</h1>";

  // navigation links
  echo "<a href='rankPage.php'>My Ranks</a>&nbsp
        <a href='allRanksPage.php'>All Ranks</a><br/><br/>";
  
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
  	echo "<tr";
  	if ($totalCount == 150) {
  	  echo " class='finished_ranking'";
  	}
  	echo "><td><img height=36 width=36 src='" . $team->getSportslineImageUrl() . "'></td>
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
  	        <meter min='0' max='150' low='150' optimum='150' value='" . $rankCount . "'></meter>
  	      </td></tr>";
  }
  echo "</table>";
  echo "</div>"; // span12
  echo "</div>"; // row-fluid
  
  // Footer
  LayoutUtil::displayFooter();
?>
</body>
</html>