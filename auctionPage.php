<html>
<head>
<title>Rotiss 2012</title>
</head>

<body>
  <?php
  require_once 'dao/auctionDao.php';
  require_once 'util/time.php';

  // display entire draft for specified year
  function displayAuction($teamId, $year) {
    echo "<h3>Auction " . $year . "</h3>";
    echo "<table border><th>Team</th><th>Player</th><th>Cost</th><th>Match?</th></tr>";

    // TODO auction: highlight row if team matches teamId
    $auctionResults = AuctionResultDao::getAuctionResultsByYear($year);
    foreach ($auctionResults as $auctionResult) {
      echo "<tr><td>" . $auctionResult->getTeam()->getName() . "</td>
                <td>" . $auctionResult->getPlayer()->getFullName() . "</td>
                <td>" . $auctionResult->getCost() . "</td>
                <td>" . (($auctionResult->isMatch()) ? "Y" : "N") . "</td></tr>";
    }
    echo "</table><br>";
  }

  // TODO auction: allow year to be edited
  if (!isset($_GET["team_id"])) {
    die("<h1>Invalid team ID for auction page!</h1>");
  }
  $teamId = $_GET["team_id"];
  if (!isset($_GET["year"])) {
    $year = TimeUtil::getCurrentSeasonYear();
  } else {
    $year = $_GET["year"];
  }
  // Display auction results.
  displayAuction($teamId, $year);
  ?>
</body>
</html>