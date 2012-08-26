<html>
<head>
<title>Rotiss 2012</title>
</head>

<body>
  <?php
  require_once 'dao/ballDao.php';
  require_once 'dao/draftPickDao.php';
  require_once 'util/time.php';

  // display entire draft for specified year
  function displayDraft($teamId, $year) {
    echo "<h3>Draft " . $year . "</h3>";
    echo "<table border><th>Round</th><th>Pick</th><th>Team</th><th>Player</th></tr>";

    // TODO draft: highlight row if team matches teamId
    $pingPongBalls = BallDao::getPingPongBallsByYear($year);
    foreach ($pingPongBalls as $pingPongBall) {
      echo "<tr><td>Ping Pong</td>
                <td>" . $pingPongBall->getCost() . "</td>
                <td>" . $pingPongBall->getTeam()->getName() . "</td>
                <td>" . $pingPongBall->getPlayerName() . "</td></tr>";
    }

    $draftPicks = DraftPickDao::getDraftPicksByYear($year);
    foreach ($draftPicks as $draftPick) {
      echo "<tr><td>" . $draftPick->getRound() . "</td>
                <td>" . $draftPick->getPick() . "</td>
                <td>" . $draftPick->getTeam()->getName() . "</td>
                <td>" . $draftPick->getPlayerName() . "</td></tr>";
    }
    echo "</table><br>";
  }

  // TODO draft: allow year to be edited
  if (!isset($_GET["team_id"])) {
    die("<h1>Invalid team ID for draft results page!</h1>");
  }
  $teamId = $_GET["team_id"];
  if (!isset($_GET["year"])) {
    $year = TimeUtil::getCurrentSeasonYear();
  } else {
    $year = $_GET["year"];
  }
  // Display draft results.
  displayDraft($teamId, $year);
  ?>
</body>
</html>