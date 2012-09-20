<html>
<head>
<title>Rotiss 2012</title>
</head>

<body>
  <?php
  require_once 'dao/brognaDao.php';
  require_once 'dao/contractDao.php';
  require_once 'dao/teamDao.php';
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

  // Display general team information.
  if (!isset($_GET["team_id"])) {
    die("<h1>Invalid team ID for budget page!</h1>");
  }
  $teamId = $_GET["team_id"];
  $team = TeamDao::getTeamById($teamId);
  if ($team == null) {
    die("<h1>Team ID " . $teamId . " not found!</h1>");
  }
  echo "<h2>" . $team->getName() . "</h2>";
  echo "<b>Division:</b> " . $team->getLeague() . " " . $team->getDivision() . "<br>";

  // Display contracts.
  displayBreakdown($teamId);
  ?>
</body>
</html>