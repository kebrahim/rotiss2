<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
?>

<html>
<head>
<title>St Pete's Rotiss - Manage Draft</title>
<link href='../css/style.css' rel='stylesheet' type='text/css'>
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
  require_once '../util/navigation.php';
  require_once '../util/time.php';
  
  // Display header.
  NavigationUtil::printHeader(true, false, NavigationUtil::MANAGE_DRAFT_BUTTON);
  echo "<div class='bodycenter'>";

  // if year isn't specified, use the current year, based on end of season.
  if (isset($_REQUEST["year"])) {
  	$year = $_REQUEST["year"];
  } else {
  	$year = TimeUtil::getYearBasedOnEndOfSeason();
  }
  if (isset($_REQUEST["round"])) {
  	$round = $_REQUEST["round"];
  } else {
  	$round = 0;
  }
  
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
  	  	  	echo "<div class='error_msg'>Ping pong ball not updated: " . $ball->toString() .
  	  	  	    "<br/></div>";
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
          	  } else {
                TeamDao::assignPlayerToTeam($draftPick->getPlayer(), $draftPick->getTeamId());
          	  }
          	}
          } else {
          	echo "<div class='error_msg'>Draft pick not updated: " . $draftPick->toString() . 
          	    "<br/></div>";
          }
        }
      }
  	}
  }

  echo "<h1>Manage Draft</h1><hr/>";
  
  // allow user to choose year.
  $minYear = DraftPickDao::getMinimumDraftYear();
  $maxYear = DraftPickDao::getMaximumDraftYear();
  echo "<label for='year'>Choose year: </label>";
  echo "<select id='year' name='year'
                onchange='showYear(this.value, document.getElementById(\"round\").value)'>";
  for ($yr = $minYear; $yr <= $maxYear; $yr++) {
  	echo "<option value='" . $yr . "'";
  	if ($yr == $year) {
  	  echo " selected";
  	}
  	echo ">$yr</option>";
  }
  echo "</select>";
  
  // allow user to choose round.
  $minRound = DraftPickDao::getMinimumRound($year);
  $maxRound = DraftPickDao::getMaximumRound($year);
  echo "&nbsp&nbsp<label for='round'>Choose round: </label>";
  echo "<select id='round' name='round' 
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
  
  echo "<FORM ACTION='manageDraft.php' METHOD=POST>";
  
  echo "<div id='yearDisplay'></div><br/>";
?>
      
<script>
  // initialize yearDisplay with selected year
  showYear(document.getElementById("year").value, document.getElementById("round").value);
</script>
      
<?php
  echo "<input type='submit' name='save' value='Save changes'>";
  echo "</form></div>";
  
  // Footer
  NavigationUtil::printFooter();
?>

</body>
</html>
