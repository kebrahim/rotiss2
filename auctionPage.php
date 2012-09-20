<html>
<head>
<title>Rotiss 2012</title>
</head>

<body>
<?php
  require_once 'dao/auctionDao.php';
  require_once 'util/time.php';

  // display entire draft for specified year
  function displayAuction($year) {
    echo "<h3>Auction " . $year . "</h3>";
    echo "<table border><th>Team</th><th>Player</th><th>Cost</th></tr>";

    // TODO auction: highlight row if team matches teamId
    $auctionResults = AuctionResultDao::getAuctionResultsByYear($year);
    foreach ($auctionResults as $auctionResult) {
      echo "<tr><td>" . $auctionResult->getTeam()->getName() . "</td>
                <td>" . $auctionResult->getPlayer()->getFullName() . "</td>
                <td>" . $auctionResult->getCost() . "</td></tr>";
    }
    echo "</table><br>";
  }

  // TODO auction: allow year to be edited
  if (!isset($_REQUEST["year"])) {
    $year = TimeUtil::getCurrentYear();
  } else {
    $year = $_REQUEST["year"];
  }
  // Display auction results.
  displayAuction($year);
?>
</body>
</html>