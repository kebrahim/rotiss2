<html>
<head>
<title>Rotiss 2012</title>
</head>

<body>

<?php
  require_once '../util/time.php';
  require_once '../dao/brognaDao.php';

// TODO (manageBrognas): allot 450 points to each team for the upcoming year.

// TODO default - show all teams' brognas for current year
// allow user to select years & teams
// allow user to edit individual row[?] or just team
  function displayBrognas($startYear, $endYear) {
    echo "<h1>Manage Brognas</h1>";
    echo "<table border><tr><th>Year</th><th>Total</th><th>Banked</th><th>Traded In</th>
  		        <th>Traded Out</th><th>Tradeable</th></tr>";

    $brognas = BrognaDao::getBrognasByYear($startYear, $endYear);
    foreach ($brognas as $brogna) {
      // Only show brogna info for this year & the future.
      if ($brogna->getYear() < $currentYear) {
        continue;
      }
      echo "<tr><td>" . $brogna->getYear() . "</td>
                <td><strong>" . $brogna->getTotalPoints() . "</strong></td>
       	        <td>" . $brogna->getBankedPoints() . "</td>
           	    <td>" . $brogna->getTradedInPoints() . "</td>
               	<td>" . $brogna->getTradedOutPoints() . "</td>
               	<td>" . $brogna->getTradeablePoints() . "</td>
           	</tr>";
    }
    echo "</table>";
  }

  // TODO switch to POST
  if (!isset($_GET["startYear"]) || !isset($_GET["endYear"])) {
    $startYear = TimeUtil::getCurrentSeasonYear();
    $endYear = $startYear;
  } else {
    $startYear = $_GET["startYear"];
    $endYear = $_GET["endYear"];
  }

  // Display points information
  displayBrognas($startYear, $endYear);
?>

</body>
</html>
