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
  require_once 'dao/ballDao.php';
  require_once 'dao/draftPickDao.php';
  require_once 'util/navigation.php';
  require_once 'util/time.php';

  function displayTeamLink(Team $team) {
    return "<a href='summaryPage.php?team_id=" . $team->getId() . "'>" . $team->getName() . "</a>";
  }

  function displayPlayerLink($player) {
    if ($player != null) {
      return "<a href='displayPlayer.php?player_id=" . $player->getId() . "'>" .
          $player->getFullName() . "</a>";
    } else {
      return "--";
    }
  }

  // display entire draft for specified year
  function displayDraft($teamId, $year) {
    echo "<div class='bodycenter'><h1>Draft " . $year . "</h1>";

    // allow user to choose which year.
    $minYear = DraftPickDao::getMinimumDraftYear();
    $maxYear = DraftPickDao::getMaximumDraftYear();
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

    // display table of draft picks for selected year, highlighting row for specified team
    echo "<table border class='center'>
          <th>Round</th><th>Pick</th><th>Team</th><th>Player</th></tr>";

    $pingPongBalls = BallDao::getPingPongBallsByYear($year);
    foreach ($pingPongBalls as $pingPongBall) {
      echo "<tr";
      if ($pingPongBall->getTeam()->getId() == $teamId) {
        echo " class='selected_team_row'";
      }
      echo "><td>Ping Pong</td>
             <td>" . $pingPongBall->getCost() . "</td>
             <td>" . displayTeamLink($pingPongBall->getTeam()) . "</td>
             <td>" . displayPlayerLink($pingPongBall->getPlayer()) . "</td></tr>";
    }

    $draftPicks = DraftPickDao::getDraftPicksByYear($year);
    foreach ($draftPicks as $draftPick) {
      echo "<tr";
      if ($draftPick->getTeam()->getId() == $teamId) {
        echo " class='selected_team_row'";
      }
      echo "><td>" . $draftPick->getRound() . "</td>
             <td>" . $draftPick->getPick() . "</td>
             <td>" . displayTeamLink($draftPick->getTeam()) . "</td>
             <td>" . displayPlayerLink($draftPick->getPlayer()) . "</td></tr>";
    }
    echo "</table><br>";
  }

  // Display header.
  NavigationUtil::printHeader(true, true, NavigationUtil::DRAFT_BUTTON);

  // Use the logged-in user's team to highlight rows.
  $teamId = SessionUtil::getLoggedInTeam()->getId();

  // if year is not specified, use the year based on the end of the season.
  if (isset($_REQUEST["year"])) {
    $year = $_REQUEST["year"];
  } else {
    $year = TimeUtil::getYearBasedOnEndOfSeason();
  }

  // Display draft results.
  echo "<form action='draftPage.php' method=post>";
  displayDraft($teamId, $year);

  echo "</form></div>";

  // Display footer
  NavigationUtil::printFooter();
?>

</body>
</html>