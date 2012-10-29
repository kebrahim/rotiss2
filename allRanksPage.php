<?php session_start(); ?>
<html>
<head>
<title>All Ranks</title>
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
#placeholder_row {background-color:#E3F2F9;}
#vert_td {vertical-align:top;}
</style>

<script>
</script>

<body>
<?php 
  require_once 'dao/rankDao.php';
  require_once 'dao/teamDao.php';
  require_once 'util/time.php';
  
  // display ranked players
  $rankYear = TimeUtil::getYearBasedOnEndOfSeason();
  echo "<h1>Offseason Ranks $rankYear</h1>";
  $teams = TeamDao::getAllTeams();
  
  echo "<table border id='ranks' class='center'>
          <tr><th></th><th>Team</th><th>Owner(s)</th>";
  for ($i = 1; $i <= 10; $i++) {
  	echo "<th>" . $i . "'s</th>";
  }
  echo "<th>Total</th></tr>";
  foreach ($teams as $team) {
  	$rankCountArray = RankDao::getRankCount($team->getId(), $rankYear);
  	echo "<tr><td><img src='" . $team->getSportslineImageUrl() . "'></td>
  	          <td>" . $team->getName() . "</td>
  	          <td>" . $team->getOwnersString() . "</td>";
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
?>
</body>
</html>