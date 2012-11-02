<?php
  require_once 'util/sessions.php';
  SessionUtil::checkUserIsLoggedIn();
?>

<html>
<head>
<title>Rotiss.com - All Ranks</title>
<link href='css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>
<?php
  require_once 'dao/rankDao.php';
  require_once 'dao/teamDao.php';
  require_once 'util/navigation.php';
  require_once 'util/time.php';

  // Display header.
  NavigationUtil::printNoWidthHeader(true, true, NavigationUtil::RANKING_BUTTON);

  // display ranked players
  $rankYear = TimeUtil::getYearBasedOnEndOfSeason();
  echo "<div class='bodycenter'><h1>Offseason Ranks $rankYear</h1>";

  // navigation links
  echo "<a href='rankPage.php'>My Ranks</a>&nbsp
          <a href='allRanksPage.php'>All Ranks</a><br/><br/>";

  $teams = TeamDao::getAllTeams();

  echo "<table border id='ranks' class='center ranktable'>
          <tr><th></th><th>Team</th><th>Owner(s)</th>";
  for ($i = 1; $i <= 10; $i++) {
  	echo "<th>" . $i . "'s</th>";
  }
  echo "<th>Total</th></tr>";
  foreach ($teams as $team) {
    $totalCount = RankDao::getTotalRankCount($team->getId(), $rankYear);
  	echo "<tr";
  	if ($totalCount == 150) {
  	  echo " class='finished_ranking'";
  	}
  	echo "><td><img height=36 width=36 src='" . $team->getSportslineImageUrl() . "'></td>
  	          <td><a href='summaryPage.php?team_id=" . $team->getId() . "'>" .
  	              $team->getName() . "</a></td>
  	          <td>" . $team->getOwnersString() . "</td>";

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
  echo "</table></div>";

  // Footer
  NavigationUtil::printFooter();
?>
</body>
</html>