<html>
<head>
<title>Rotiss.com - Budget</title>
<link href='css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>
  <?php
  require_once 'dao/brognaDao.php';
  require_once 'dao/contractDao.php';
  require_once 'dao/teamDao.php';
  require_once 'util/navigation.php';
  require_once 'util/time.php';

  // Show breakdown of brognas per year w/ contract info
  function displayBreakdown($teamId) {
    // get all points
    $brognas = BrognaDao::getBrognasByTeamId($teamId);

    // get all contracts
    $contracts = ContractDao::getContractsByTeamId($teamId);

    // for ever brogna record,
    $currentYear = TimeUtil::getYearBasedOnKeeperNight();
    // TODO budget: allow user to adjust start year
    foreach ($brognas as $brogna) {
      if ($brogna->getYear() < $currentYear) {
        continue;
      }
      // show brogna info
      echo "<h3>" . $brogna->getYear() . "</h3>";
      echo "<strong>Brognas</strong><br>";
      echo "<table border><tr><th>Alotted</th>
                       <th>" . ($brogna->getYear() - 1) . " Bank</th>
                       <th>Received in Trade</th>
                       <th>Given in Trade</th><th>Total</th></tr>";
      echo "<tr><td>450</td>
                <td>" . $brogna->getBankedPoints() . "</td>
                <td>" . $brogna->getTradedInPoints() . "</td>
                <td>" . $brogna->getTradedOutPoints() . "</td>
                <td><strong>" . $brogna->getTotalPoints() . "</td></tr></table><br>";

      // show contracts for that year
      $contractTotal = 0;
      $hasContracts = false;
      foreach ($contracts as $contract) {
        if (($contract->getStartYear() > $brogna->getYear()) ||
            ($contract->getEndYear() < $brogna->getYear())) {
          continue;
        }
        if ($hasContracts == false) {
          echo "<strong>Contracts</strong><br>";
          echo "<table border><tr><th>Name</th><th>Position</th><th>Team</th><th>Age</th>
                           <th>Years Remaining</th><th>Price</th></tr>";
          $hasContracts = true;
        }
        $player = $contract->getPlayer();
        echo "<tr><td>" . $player->getFullName() . "</td>
                  <td>" . $player->getPositionString() . "</td>
                  <td>" . $player->getMlbTeam()->getAbbreviation() . "</td>
                  <td>" . $player->getAge() . "</td>
                  <td>" . ($contract->getEndYear() - $brogna->getYear() + 1) . "</td>
                  <td>" . $contract->getPrice() . "</td></tr>";
        $contractTotal += $contract->getPrice();
      }
      if ($hasContracts == true) {
        echo "<tr><td></td><td></td><td></td><td></td><td></td>
                  <td><strong>" . $contractTotal . "</strong></td></tr>";
        echo "</table><br>";
      }

      // show leftover brognas
      echo "<strong>Bank for " . ($brogna->getYear() + 1) . ": </strong>" .
           ($brogna->getTotalPoints() - $contractTotal);
      // TODO budget: should bank from previous year be calculated?
    }
  }

  // Display header.
  NavigationUtil::printHeader(true, true, NavigationUtil::BUDGET_BUTTON);
  
  // TODO choose from list of teams to see corresponding budget page.
  
  // Display general team information.
  if (isset($_REQUEST["team_id"])) {
  	$teamId = $_REQUEST["team_id"];
  } else {
  	// if no team is selected, then show the logged-in user's team.
  	$teamId = SessionUtil::getLoggedInTeam()->getId();
  }
  $team = TeamDao::getTeamById($teamId);
  
  if ($team == null) {
    die("<h1>Team ID " . $teamId . " not found!</h1>");
  }
  echo "<div class='bodyleft'>";
  echo "<h1>Budget: " . $team->getName() . "</h1>";
  echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";
  
  echo "<table>";
  echo "  <tr><td><strong>Owner(s):</strong></td>
  <td>" . $team->getOwnersString() . "</td></tr>";
  echo "</table>";
  
  // Display contracts.
  displayBreakdown($teamId);
  
  echo "</div>";
  
  // Display footer
  NavigationUtil::printFooter();
  ?>
</body>
</html>