<?php
  require_once '../util/sessions.php';

  SessionUtil::checkUserIsLoggedInAdmin();

  // if year isn't specified, use the current year, based on end of season.
  $redirectUrl = "admin/manageDraft.php";
  if (isset($_REQUEST["year"])) {
  	$year = $_REQUEST["year"];
  	$redirectUrl .= "?year=$year";
  } else {
  	$year = TimeUtil::getYearBasedOnEndOfSeason();
  }
  if (isset($_REQUEST["round"])) {
  	$round = $_REQUEST["round"];
  	$redirectUrl .= "&round=$round";
  } else {
  	$round = 0;
  }

  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - Manage Draft</title>
<link href='../css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='../css/stpetes.css' rel='stylesheet' type='text/css'>
</head>

<script>
//shows the draft page for the specified year
function showYear(year, round) {
    // update round dropdown for selected year & set to selected round
	getRedirectHTML(document.getElementById("round"),
	    "displayYear.php?type=draftround&year=" + year + "&round=" + round);

	// Display draft information for specified year and round.
	getRedirectHTML(document.getElementById("yearDisplay"),
	    "displayYear.php?type=managedraft&year=" + year + "&round=" + round);
}

function showRound(year, round) {
	// Display team information.
	getRedirectHTML(document.getElementById("yearDisplay"),
	    "displayYear.php?type=managedraft&year=" + year + "&round=" + round);
}

// populates the innerHTML of the specified elementId with the HTML returned by the specified
// htmlString
function getRedirectHTML(element, htmlString) {
	var xmlhttp;
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
	    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			element.innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET", htmlString, true);
	xmlhttp.send();
}
</script>

<body>

<?php
  require_once '../dao/draftPickDao.php';
  require_once '../util/layout.php';
  require_once '../util/time.php';

  // Display nav bar.
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_DRAFT_BUTTON);

  echo "<div class='row-fluid'>
          <div class='span12 center'>";

  // If save button was pressed, save results
  if (isset($_POST['save'])) {
  	$currentYear = TimeUtil::getYearBasedOnEndOfSeason();
  	if ($round == 0) {
  	  // update ping pong balls
  	  $balls = BallDao::getPingPongBallsByYear($year);
  	  foreach ($balls as $ball) {
  	  	$ballUpdated = false;

  	  	$playerSelection = "player" . $ball->getId();
  	  	$currentPlayer = $ball->getPlayer();
  	  	$currentPlayerId = ($currentPlayer == null ? 0 : $currentPlayer->getId());
  	  	if (isset($_POST[$playerSelection])
  	  	    && (intval($_POST[$playerSelection]) != $currentPlayerId)) {
  	  	  $ball->setPlayerId(intval($_POST[$playerSelection]));
  	  	  $ballUpdated = true;
  	  	}

  	  	if ($ballUpdated) {
  	  	  $result = BallDao::updatePingPongBall($ball);

  	  	  // assign player to team if ball was saved and it's the current year.
  	  	  if ($result) {
  	  	  	if ($year == $currentYear) {
  	  	  	  if (($ball->getPlayer() == null) && ($currentPlayer != null)) {
  	  	  	  	// player was removed; remove player from team
  	  	  	  	TeamDao::assignPlayerToTeam($currentPlayer, 0);
  	  	  	  } else {
    	        TeamDao::assignPlayerToTeam($ball->getPlayer(), $ball->getTeamId());
  	  	  	  }
  	  	  	}
  	  	  } else {
  	  	  	echo "<br/><div class='alert alert-error'>
     	  	  	  Ping pong ball not updated: " . $ball->toString() .
  	  	  	    "</div>";
  	  	  }
  	  	}
  	  }
  	} else {
  	  // update draft picks
      $draftPicks = DraftPickDao::getDraftPicksByYearRound($year, $round);
      foreach ($draftPicks as $draftPick) {
        $draftPickUpdated = false;

      	$playerSelection = "player" . $draftPick->getId();
        $currentPlayer = $draftPick->getPlayer();
        $currentPlayerId = ($currentPlayer == null ? 0 : $currentPlayer->getId());
        if (isset($_POST[$playerSelection])
            && (intval($_POST[$playerSelection]) != $currentPlayerId)) {
          $draftPick->setPlayerId(intval($_POST[$playerSelection]));
          $draftPickUpdated = true;
        }

        $pickSelection = "pick" . $draftPick->getId();
        $currentPick = $draftPick->getPick();
        if (isset($_POST[$pickSelection])
        		&& (intval($_POST[$pickSelection]) != $currentPick)) {
          $draftPick->setPick(intval($_POST[$pickSelection]));
          $draftPickUpdated = true;
        }

        if ($draftPickUpdated) {
          $result = DraftPickDao::updateDraftPick($draftPick);

  	  	  // assign player to team if draft pick was saved and it's the current year.
          if ($result) {
          	if ($year == $currentYear) {
          	  if (($draftPick->getPlayer() == null) && ($currentPlayer != null)) {
          		// player was removed; remove player from team
          		TeamDao::assignPlayerToTeam($currentPlayer, 0);
          	  } else if ($draftPick->getPlayer() != null) {
                TeamDao::assignPlayerToTeam($draftPick->getPlayer(), $draftPick->getTeamId());
          	  }
          	}
          } else {
          	echo "<br/><div class='alert alert-error'>
          	      Draft pick not updated: " . $draftPick->toString() .
          	    "</div>";
          }
        }
      }
  	}
  }

  echo "<h3>Manage Draft</h3>
        </div>
        </div>";

  echo "<div class='row-fluid'>
          <div class='span4 offset1 center chooser'>";

  // allow user to choose year.
  $minYear = DraftPickDao::getMinimumDraftYear();
  $maxYear = DraftPickDao::getMaximumDraftYear();
  echo "<label for='year'>Choose year: </label>";
  echo "<select id='year' name='year' class='input-small'
                onchange='showYear(this.value, document.getElementById(\"round\").value)'>";
  for ($yr = $minYear; $yr <= $maxYear; $yr++) {
  	echo "<option value='" . $yr . "'";
  	if ($yr == $year) {
  	  echo " selected";
  	}
  	echo ">$yr</option>";
  }
  echo "</select>";
  echo "</div>"; // span6

  echo "<div class='span4 offset2 center chooser'>";
  // allow user to choose round.
  $minRound = DraftPickDao::getMinimumRound($year);
  $maxRound = DraftPickDao::getMaximumRound($year);
  echo "&nbsp&nbsp<label for='round'>Choose round: </label>";
  echo "<select id='round' name='round' class='input-small'
                onchange='showRound(document.getElementById(\"year\").value, this.value)'>";
  echo "<option value='0'>PP</option>";
  for ($rd = $minRound; $rd <= $maxRound; $rd++) {
  	echo "<option value='" . $rd . "'";
  	if ($rd == $round) {
  	  echo " selected";
  	}
  	echo ">$rd</option>";
  }
  echo "</select>";
  echo "</div>"; // span6
  echo "</div>"; // row-fluid

  echo "<div class='row-fluid'>
          <div class='span12 center'>";
  echo "<FORM ACTION='manageDraft.php' METHOD=POST>";
  echo "<div id='yearDisplay'></div>";
?>

<script>
  // initialize yearDisplay with selected year
  showYear(document.getElementById("year").value, document.getElementById("round").value);
</script>

<?php
  echo "<p><button class=\"btn btn-primary\" name='save' type=\"submit\">Save changes</button>";
  echo "&nbsp&nbsp<button class=\"btn\" name='cancel' type=\"submit\">Reset</button></p>";
  echo "</form>";
  echo "</div>"; // span12
  echo "</div>"; // row-fluid

  // Footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
