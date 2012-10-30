<?php session_start(); ?>
<html>
<head>
<title>Placeholders</title>
</head>

<style type="text/css">
html {height:100%;}
body {text-align:center;}
table {text-align:center;}
table.center {margin-left:auto; margin-right:auto;}
#column_container {padding:0; margin:0 0 0 50%; width:50%; float:right;}
#left_col {float:left; width:100%; margin-left:-100%; text-align:center;}
#left_col_inner {padding:10px;}
#right_col {float:right; width:100%; text-align:center;}
#right_col_inner {padding:10px;}
#placeholder {background-color:#BCECBE;}
#placeholder_missing {background-color:#F09E9E;}
</style>

<body>

<?php
  require_once '../dao/playerDao.php';
  require_once '../dao/rankDao.php';
  require_once '../dao/statDao.php';
  require_once '../entity/rank.php';
  require_once '../entity/stat.php';
  require_once '../util/time.php';

  echo "<FORM ACTION='managePlaceholders.php' METHOD=POST>";
  $rankYear = TimeUtil::getYearBasedOnEndOfSeason();
  $lastYear = $rankYear - 1;
  
  // Save placeholders
  if(isset($_POST['save'])) {
  	$stats = StatDao::getStatsForRankingByYear($lastYear);
  	$ct = 1;
  	$teams = TeamDao::getAllTeams();
  	foreach($stats as $stat) {
      $ranking = floor(11.0 - ($ct++ / 15.0));
  	  $isPlaceholder = PlayerDao::hasContractForPlaceholders($stat->getPlayerId(), $lastYear);
  	  if ($isPlaceholder && 
  	      !RankDao::hasAllPlaceholderRanks($stat->getPlayerId(), $rankYear)) {
  	  	// insert placeholder ranks into db if they haven't already been inserted
  	  	foreach ($teams as $team) {
  	  	  if ($stat->getPlayer()->getFantasyTeam()->getId() != $team->getId()) {
  	  	  	$rank = new Rank(-1, $rankYear, $team->getId(), $stat->getPlayerId(), $ranking, true);
  	  	  	RankDao::createRank($rank);
  	  	  }
  	  	}
  	  }
  	}
  }

  // show stats for players belonging to teams for last season
  echo "<h1>Placeholders " . $rankYear . "</h1>";
  $stats = StatDao::getStatsForRankingByYear($lastYear);

  echo "<input class='button' type=submit name='save' value='Save placeholders'>&nbsp&nbsp";
  if (isset($_REQUEST["filter"])) {
  	echo "<a href='managePlaceholders.php'>Show all</a>";
  	echo "<input type=hidden name='filter'>";
  	$filter = true;
  } else {
  	echo "<a href='managePlaceholders.php?filter'>Show placeholders</a>";
  	$filter = false;
  }
  echo "<br/><br/>";
  
  echo "<table class='center' border>
  	          <tr><th></th><th>Player</th><th>Team</th><th>$lastYear Fantasy Pts</th><th>Rank</th>
                  <th>Is Placeholder</th><th>Saved In DB</th></tr>";
  $ct = 0;
  foreach($stats as $stat) {
  	$ct++;
  	$rank = floor(11.0 - ($ct / 15.0));
  	$isPlaceholder = PlayerDao::hasContractForPlaceholders($stat->getPlayerId(), $lastYear);
  	$placeholderInDb = ($isPlaceholder && 
  	    RankDao::hasAllPlaceholderRanks($stat->getPlayerId(), $rankYear));
  	if ($filter && !$isPlaceholder) {
  	  continue;
  	}
  	echo "<tr";
  	if ($isPlaceholder) {
      echo " id='placeholder";
      if (!$placeholderInDb) {
      	echo "_missing";
      }
      echo "'";
  	}
  	echo ">";
  	echo "    <td>" . $ct . "</td>
  	          <td>" . $stat->getPlayer()->getFullName() . "</td>
  	          <td>" . $stat->getPlayer()->getFantasyTeam()->getAbbreviation() . "</td>
  	          <td>" . $stat->getStatLine()->getFantasyPoints() . "</td>
  	          <td>" . $rank . "</td>
  	          <td>" . ($isPlaceholder ? 'Y' : "") . "</td>
  	          <td>" . ($placeholderInDb ? "Y" : "") . "</td>
  	      </tr>";
  }
  echo "</table><br/>";
  echo "</form>";
?>

</body>
</html>
