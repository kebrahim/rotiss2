<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
?>

<html>
<head>
<title>Rotiss.com - Manage Brognas</title>
<link href='../css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>

<?php
  require_once '../dao/brognaDao.php';
  require_once '../util/time.php';
  require_once '../util/navigation.php';

  // Display header.
  NavigationUtil::printHeader(true, false, NavigationUtil::MANAGE_BROGNAS_BUTTON);
  echo "<div class='bodycenter'>";

  function displayBrognas($year) {
    echo "<h1>Manage $year Brognas</h1>";

    // allow user to change year
    $minYear = BrognaDao::getMinimumYear();
    $maxYear = BrognaDao::getMaximumYear();
    echo "<strong>Filter by year: </strong>";
    echo "<select name='year'>";
    for ($yr = $minYear; $yr <= $maxYear; $yr++) {
      echo "<option value='$yr'";
      if ($yr == $year) {
        echo " selected";
      }
      echo ">$yr</option>";
    }
    echo "</select>&nbsp&nbsp<input type='submit' name='submit' value='Filter'><br/><br/>";

    echo "<table border class='center'>
            <tr><th>Team</th><th>Total</th><th>Banked</th><th>Traded In</th>
  		        <th>Traded Out</th><th>Tradeable</th></tr>";

    $brognas = BrognaDao::getBrognasByYear($year, $year);
    foreach ($brognas as $brogna) {
      echo "<tr><td>" . $brogna->getTeam()->getNameLink(false) . "</td>
                <td><strong>" . $brogna->getTotalPoints() . "</strong></td>
       	        <td>" . $brogna->getBankedPoints() . "</td>
           	    <td>" . $brogna->getTradedInPoints() . "</td>
               	<td>" . $brogna->getTradedOutPoints() . "</td>
               	<td>" . $brogna->getTradeablePoints() . "</td>
           	</tr>";
    }
    echo "</table>";
  }

  // if year isn't specified, use the current year, based on keeper night.
  if (isset($_REQUEST["year"])) {
    $year = $_REQUEST["year"];
  } else {
    $year = TimeUtil::getYearBasedOnKeeperNight();
  }

  // Display brognas for given year
  echo "<form action='manageBrognas.php' method=post>";
  displayBrognas($year);
  echo "</form></div>";

  // TODO should this be editable?

  // Footer
  NavigationUtil::printFooter();
?>

</body>
</html>
