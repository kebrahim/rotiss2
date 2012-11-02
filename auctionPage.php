<?php
  require_once 'util/sessions.php';
  SessionUtil::checkUserIsLoggedIn();
?>

<html>
<head>
<title>Rotiss.com - Draft</title>
<link href='css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>
<?php
  require_once 'dao/auctionDao.php';
  require_once 'util/navigation.php';
  require_once 'util/time.php';

  // display list of auctioned players for specified year
  function displayAuction($year, $teamId) {
    $minYear = AuctionResultDao::getMinimumAuctionYear();
    $maxYear = AuctionResultDao::getMaximumAuctionYear();
    if ($year < $minYear) {
      $year = $minYear;
    } else if ($year > $maxYear) {
      $year = $maxYear;
    }
    echo "<div class='bodycenter'><h1>Auction " . $year . "</h1>";

    // allow user to choose which year
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

    // display table of auction results for specified year, highlighting row for specified team
    echo "<table border class='center'><th>Team</th><th>Player</th><th>Cost</th></tr>";
    $auctionResults = AuctionResultDao::getAuctionResultsByYear($year);
    foreach ($auctionResults as $auctionResult) {
      $team = $auctionResult->getTeam();
      $player = $auctionResult->getPlayer();
      echo "<tr";
      if ($team->getId() == $teamId) {
        echo " class='selected_team_row'";
      }
      echo "><td><a href='summaryPage.php?team_id=" . $team->getId() . "'>" .
                  $team->getName() . "</a></td>
             <td><a href='displayPlayer.php?player_id=" . $player->getId() . "'>" .
                 $player->getFullName() . "</a></td>
             <td>" . $auctionResult->getCost() . "</td></tr>";
    }
    echo "</table><br>";
  }

  // Display header.
  NavigationUtil::printHeader(true, true, NavigationUtil::AUCTION_BUTTON);

  // Use the logged-in user's team to highlight rows.
  $teamId = SessionUtil::getLoggedInTeam()->getId();

  // if year is not specified, use the current year.
  if (isset($_REQUEST["year"])) {
    $year = $_REQUEST["year"];
  } else {
    $year = TimeUtil::getCurrentYear();
  }

  // Display auction results.
  echo "<form action='auctionPage.php' method=post>";
  displayAuction($year, $teamId);

  echo "</form></div>";

  // Display footer
  NavigationUtil::printFooter();
?>
</body>
</html>