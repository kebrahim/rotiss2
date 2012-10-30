<?php session_start(); ?>
<html>
<head>
<title>Manage Ranks</title>
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
#placeholder {background-color:#E3F2F9;}
#row_indb {background-color:#BCECBE;}
#row_missing {background-color:#F09E9E;}
</style>

<body>

<?php
  require_once '../dao/cumulativeRankDao.php';
  require_once '../dao/playerDao.php';
  require_once '../dao/rankDao.php';
  require_once '../dao/statDao.php';
  require_once '../entity/rank.php';
  require_once '../entity/stat.php';
  require_once '../util/time.php';

  echo "<FORM ACTION='manageRanks.php' METHOD=POST>";
  $rankYear = TimeUtil::getYearBasedOnEndOfSeason();
  $lastYear = $rankYear - 1;
  
  // Save ranks
  if(isset($_POST['save'])) {
  	$ranks = RankDao::calculateCumulativeRanksByYear($rankYear);
  	foreach ($ranks as $rank) {
  	  if (!CumulativeRankDao::hasCumulativeRank($rank->getPlayerId(), $rankYear)) {
  	  	CumulativeRankDao::createCumulativeRank($rank);
  	  	echo "saved cumulative rank " . $rank->toString() . "</br>";
  	  }
  	} 
  }

  // show cumulative ranks for all players, including placeholders & whether the ranks have
  // been saved
  echo "<h1>Ranks " . $rankYear . "</h1>";
  $ranks = RankDao::calculateCumulativeRanksByYear($rankYear);

  echo "<input class='button' type=submit name='save' value='Save ranks'>&nbsp&nbsp";
  if (isset($_REQUEST["filter"])) {
  	echo "<a href='manageRanks.php'>Show placeholders</a>";
  	echo "<input type=hidden name='filter'>";
  	$filter = true;
  } else {
  	echo "<a href='manageRanks.php?filter'>Hide placeholders</a>";
  	$filter = false;
  }
  echo "<br/><br/>";
  
  echo "<table class='center' border>
  	          <th>Player</th><th>Team</th><th>Rank</th><th>Is Placeholder</th>
              <th>Saved In DB</th></tr>";
  foreach($ranks as $rank) {
  	if ($filter && $rank->isPlaceholder()) {
  	  continue;
  	}

  	// determine if cumulative rank is already in db
  	$hasCumulativeRank = CumulativeRankDao::hasCumulativeRank($rank->getPlayerId(), $rankYear);
  	 
  	// highlight rows based on placeholders/rank-in-db
  	echo "<tr id='";
  	if ($rank->isPlaceholder()) {
      echo "placeholder";
  	} else if ($hasCumulativeRank) {
  	  echo "row_indb";
  	} else {
  	  echo "row_missing";
  	}
  	echo "'>";
  	echo "    <td>" . $rank->getPlayer()->getFullName() . "</td>
  	          <td>" . $rank->getPlayer()->getFantasyTeam()->getAbbreviation() . "</td>
  	          <td>" . $rank->getRank() . "</td>
  	          <td>" . ($rank->isPlaceholder() ? "Y" : "") . "</td>
  	          <td>" . ($hasCumulativeRank ? "Y" : "") . "</td>
  	      </tr>";
  }
  echo "</table><br/>";
  echo "</form>";
?>

</body>
</html>
