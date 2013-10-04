<?php
  require_once 'util/sessions.php';

  $redirectUrl = "playerPage.php";
  if (isset($_REQUEST['player_id'])) {
  	$playerId = $_REQUEST['player_id'];
  	$redirectUrl .= "?player_id=$playerId";
  } else {
  	die("<h1>Missing playerId for player page</h1>");
  }
  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - Player Summary</title>
<link href='css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='css/stpetes.css' rel='stylesheet' type='text/css'>
<link rel="shortcut icon" href="img/background-tiles-01.png" />
</head>

<body>

<?php
  require_once 'dao/cumulativeRankDao.php';
  require_once 'dao/auctionDao.php';
  require_once 'dao/ballDao.php';
  require_once 'dao/contractDao.php';
  require_once 'dao/draftPickDao.php';
  require_once 'dao/playerDao.php';
  require_once 'dao/rankDao.php';
  require_once 'util/layout.php';

  function getDraftPick($draftPicks, $year) {
    foreach ($draftPicks as $draftPick) {
      if ($draftPick->getYear() == $year) {
        return $draftPick;
      }
    }
    return null;
  }

  function getBall($balls, $year) {
    foreach ($balls as $ball) {
      if ($ball->getYear() == $year) {
        return $ball;
      }
    }
    return null;
  }

  // Display header.
  LayoutUtil::displayNavBar(TRUE, LayoutUtil::PLAYERS_BUTTON);

  echo "<div class='row-fluid'>";
  // Get player from db.
  $player = PlayerDao::getPlayerById($playerId);
  if ($player == null) {
  	die("<div class='span12 center'>
  			<h1>player id " . $playerId . " does not exist!</h1>
  	     </div></div>");
  }

  echo "<div class='span2 center headshotimg nexttoh1'>";

  // Headshot
  if ($player->hasSportslineId()) {
    echo $player->getHeadshotImg(42, 56);
  } else {
    // TODO show blank face
  }
  echo "</div>"; // span2

  // player name heading
  echo "<div class='span7 center'>
            <h3>" . $player->getFullName() . "</h3>
          </div>";
  // TODO add bookmarks to name heading

  // links to external sites
  echo "<div class='span3 center headshotimg nexttoh1'>
          <label>Other sites:</label>
          <a href='" . $player->getStPetesUrl() . "' target='_blank'>
            <img class='img_42' src='img/cbs.jpg' /></a>
          <a href='" . $player->getBaseballReferenceUrl() . "' target='_blank'>
            <img class='img_56_42' src='img/bbr.jpg' /></a>
        </div>";

  echo "</div>"; // row-fluid

  // Summary
  echo "<div class='row-fluid'>
          <div class='span12'>";
  echo "<h4>Player Summary</h4>
        <table class='table vertmiddle table-striped table-condensed table-bordered'>";

  // ID
  echo "<tr>
          <td><strong>Rotiss Id:</strong></td>
          <td>" . $player->getId() . "</td>
        </tr>";

  // MLB Team
  $mlbTeam = $player->getMlbTeam();
  echo "<tr><td><strong>Team:</strong></td>
            <td>" . $mlbTeam->getCity() . " " . $mlbTeam->getName() . "&nbsp&nbsp" .
                    $mlbTeam->getImageTag(32) . "</td>
        </tr>";

  // Birth date & age
  echo "<tr><td><strong>Birth Date:</strong></td>
        <td>" . $player->getBirthDate() . "</td></tr>";
  echo "<tr><td><strong>Age:</strong></td>
        <td>" . $player->getAge() . "</td></tr>";

  // Positions
  echo "<tr><td><strong>Position(s):</strong></td>
        <td>" . $player->getPositionString() . "</td></tr>";

  // Fantasy team
  echo "<tr><td><strong>Fantasy Team:</strong></td><td>";
  $fantasyTeam = $player->getFantasyTeam();
  if ($fantasyTeam == null) {
  	echo "--";
  } else {
  	echo $fantasyTeam->getIdLink(
  	    true, $fantasyTeam->getAbbreviation() . " (" . $fantasyTeam->getName() . ")") .
  	"&nbsp&nbsp" . $fantasyTeam->getSportslineImg(32);
  }
  echo "</td></tr>";

  echo "</table>";

  // show contract history
  $contracts = ContractDao::getContractsByPlayerId($player->getId());
  if (count($contracts) > 0) {
    echo "<h4>Contract History</h4>";
    echo "<table class='table center vertmiddle table-striped table-condensed table-bordered'>
            <thead><tr>
              <th colspan=2>Team</th><th>Sign Date</th><th>Years</th><th>Price</th>
              <th>Start Year</th><th>End Year</th><th>Type</th>
            </tr></thead>";
    foreach ($contracts as $contract) {
      $type = $contract->getType();
      if ($contract->isBoughtOut()) {
        $type .= " (Bought out)";
      }
      echo "<tr>" .
              TeamManager::getAbbreviationAndLogoRow($contract->getTeam()) .
             "<td>" . $contract->getFormattedSignDate() . "</td>
              <td>" . $contract->getTotalYears() . "</td>
              <td>" . $contract->getPrice() . "</td>
              <td>" . $contract->getStartYear() . "</td>
              <td>" . $contract->getEndYear() . "</td>
              <td>" . $type . "</td>
            </tr>";
    }
    echo "</table>";
  }

  // show draft/pingpong history
  $draftPicks = DraftPickDao::getDraftPicksByPlayerId($player->getId());
  $balls = BallDao::getPingPongBallsByPlayerId($player->getId());
  if ((count($draftPicks) + count($balls)) > 0) {
    echo "<h4>Draft History</h4>";
    echo "<table class='table center vertmiddle table-striped table-condensed table-bordered'>
            <thead><tr>
              <th>Year</th><th colspan=2>Team</th><th>Round</th><th>Pick</th><th>Original Team</th>
            </tr></thead>";
    $minYear = min(DraftPickDao::getMinimumDraftYear(), BallDao::getMinimumYear());
    $maxYear = max(DraftPickDao::getMaximumDraftYear(), BallDao::getMaximumYear());
    for($year = $maxYear; $year >= $minYear; $year--) {
      $draftPick = getDraftPick($draftPicks, $year);
      $ball = getBall($balls, $year);
      if ($draftPick != null) {
        echo "<tr>
                <td>$year</td>" .
                TeamManager::getAbbreviationAndLogoRow($draftPick->getTeam()) .
               "<td>" . $draftPick->getRound() . "</td>
                <td>" . $draftPick->getPick() . "</td>
                <td>" . $draftPick->getOriginalTeamName() . "</td>
              </tr>";
      } else if ($ball != null) {
        echo "<tr>
                <td>$year</td>" .
                TeamManager::getAbbreviationAndLogoRow($ball->getTeam()) .
               "<td>PP</td>
                <td>" . $ball->getOrdinal() . " ($" . $ball->getCost() . ")</td>
                <td>--</td>
              </tr>";
      }
    }
    echo "</table>";
  }

  // show auction history
  $auctions = AuctionResultDao::getAuctionResultsByPlayerId($player->getId());
  if (count($auctions) > 0) {
    echo "<h4>Auction History</h4>";
    echo "<table class='table center vertmiddle table-striped table-condensed table-bordered'>
            <thead><tr>
              <th>Year</th><th colspan=2>Team</th><th>Cost</th>
            </tr></thead>";
    foreach ($auctions as $auction) {
      echo "<tr>
              <td>" . $auction->getYear() . "</td>" .
              TeamManager::getAbbreviationAndLogoRow($auction->getTeam()) .
             "<td>" . $auction->getCost() . "</td>
            </tr>";
    }
    echo "</table>";
  }

  // TODO show trade history

  // show ranks of all years where player was ranked
  $stats = StatDao::getStatsByPlayer($player->getId());
  if (count($stats) > 0) {
    echo "<h4>Offseason Ranks</h4>
          <table class='table center vertmiddle table-striped table-condensed table-bordered'>
            <thead><tr>
              <th>Year</th><th>Fantasy Points</th><th colspan=15>Team Ranks</th>
              <th>Offseason Rank</th>
            </tr></thead>";
    foreach ($stats as $stat) {
      $rankYear = $stat->getYear() + 1;
      echo "<tr><td><strong>" . $stat->getYear() . "</strong></td>
                <td>". $stat->getStatLine()->getFantasyPoints() ."</td>";

      # show ranks if cumulative rank has been saved
      $cumulativeRank =
          CumulativeRankDao::getCumulativeRankByPlayerYear($player->getId(), $rankYear);
      if ($cumulativeRank) {
        $ranks = RankDao::getRanksByPlayerYear($player->getId(), $rankYear);
        foreach ($ranks as $rank) {
          echo "<td>" . $rank->getRank() . "</td>";
        }
        // show 0s
        if (count($ranks) < 15) {
          for ($i = count($ranks); $i < 15; $i++) {
            echo "<td>0</td>";
          }
        }
      } else {
        echo "<td colspan=15></td>";
      }

      // show cumulative rank
      echo "<td><strong>";
      if ($cumulativeRank) {
        echo $cumulativeRank->getRank();
        if ($cumulativeRank->isPlaceholder()) {
          echo " (PH)";
        }
      } else {
        echo "--";
      }
      echo "</strong></td></tr>";
    }
    echo "</table>";
  }

  // if admin user, show edit link
  if (SessionUtil::isLoggedInAdmin()) {
    echo "<div class='managelink'>
            <a class='btn btn-primary' href='admin/managePlayer.php?player_id=" . $player->getId() .
                "'>Manage player</a>
          </div>";
  }

  echo "</div></div>"; // span12, row-fluid

  // Display footer
  LayoutUtil::displayFooter();
?>

</body>
</html>
