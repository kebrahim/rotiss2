<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
  SessionUtil::logoutUserIfNotLoggedIn("admin/manageAuction.php");
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - Manage Auction</title>
<link href='../css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='../css/stpetes.css' rel='stylesheet' type='text/css'>
</head>

<script>
// sets the display of the div with the specified id
function setDisplay(id, display) {
  var divid = document.getElementById(id);
  divid.style.display = display;
}

// shows the player with the specified id
function showPlayer(playerId) {
	var selectedTeam = document.getElementsByName("team").item(0);
	var selectedPlayer = document.getElementsByName("player").item(0);

    // If playerId is blank, then clear the player div.
	if (playerId == "" || playerId == "0") {
		document.getElementById("playerDisplay").innerHTML="";

	    // hide auction button
		setDisplay("auctionButton", "none");
	    return;
	}

	// only show auction button if player and team are selected
	if (selectedTeam.selectedIndex > 0) {
		setDisplay("auctionButton", "block");
	}

	// Display team information.
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
	    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("playerDisplay").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","displayPlayer.php?type=auction&player_id="+playerId,true);
	xmlhttp.send();
}

// shows the team with the specified id
function showTeam(teamId) {
	var selectedTeam = document.getElementsByName("team").item(0);
	var selectedPlayer = document.getElementsByName("player").item(0);

    // If teamid is blank, then clear the team div.
	if (teamId=="" || teamId=="0") {
		document.getElementById("teamDisplay").innerHTML="";

	    // hide auction button
		setDisplay("auctionButton", "none");
	    return;
	}

	// only show auction button if player and team are selected
	if (selectedPlayer.selectedIndex > 0) {
		setDisplay("auctionButton", "block");
	}

	// Display team information.
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
	    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("teamDisplay").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","displayTeam.php?type=auction&team_id="+teamId,true);
	xmlhttp.send();
}
</script>

<body>

<?php
  require_once '../dao/auctionDao.php';
  require_once '../dao/playerDao.php';
  require_once '../dao/teamDao.php';
  require_once '../entity/auction.php';
  require_once '../util/layout.php';
  require_once '../util/time.php';

  // Display nav bar.
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_AUCTION_BUTTON);

  echo "<FORM ACTION='manageAuction.php' METHOD=POST>";
  echo "<div class='row-fluid'>
          <div class='span6 offset3 center'>
            <h3>Going once... Going twice... Sold!</h3>
          </div>
        </div>
        <div class='row-fluid'>
          <div class='span12 center'>";

  // If auction button was pressed...
  if(isset($_POST['auction'])) {
  	// Create auction scenario.
  	$auction = new Auction();
  	$auction->parseAuctionFromPost();

  	// Validate auction.
  	if ($auction->validateAuction()) {
  	  // display who will be auctioning & amount
  	  $auction->showAuctionSummary();

  	  // request final confirmation of auction before execution
  	  echo "<p><button class=\"btn btn-primary\" name='confirmAuction' 
                       type=\"submit\">Confirm</button>&nbsp&nbsp
               <button class=\"btn\" name='cancelAuction' type=\"submit\">Cancel</button>
  	        </p>";
  	} else {
  	  echo "<h3>
  	          Cannot execute auction! Please <a href='manageAuction.php' 
  	          class=\"btn btn-primary\">try again</a>
  	        </h3>";
  	}
  } elseif (isset($_POST['confirmAuction'])) {
    // Re-create auction scenario from session.
  	$auction = new Auction();
  	$auction->parseAuctionFromSession();

  	// Validate auction.
  	if ($auction->validateAuction()) {
  	  // Initiate auction & report results.
      $auction->initiateAuction();
  	  echo "<a href='manageAuction.php' class='btn btn-primary'>Let's do it again!</a><br/><br/>";
  	} else {
  	  echo "<h3>Cannot execute auction! Please <a href='manageAuction.php' 
  	        class='btn btn-primary'>try again</a></h3>";
  	}
  } else {
  	// clear out trade session variables from previous auction scenarios.
  	SessionUtil::clearSessionVarsWithPrefix("auction_");

  	// show auction results for current year
  	$currentYear = TimeUtil::getCurrentYear();
  	echo "<h4>Auction results " . $currentYear . "</h4>";
  	$auctionResults = AuctionResultDao::getAuctionResultsByYear($currentYear);
  	if (count($auctionResults) > 0) {
  	  echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
  	          <thead><tr><th>Player</th><th>Team</th><th>Amount</th></tr></thead>";
  	}
  	foreach($auctionResults as $auctionResult) {
  	  echo "<tr><td>" . $auctionResult->getPlayer()->getNameLink(false) . "</td>
  	            <td>" . $auctionResult->getTeam()->getNameLink(false) . "</td>
  	            <td>" . $auctionResult->getCost() . "</td></tr>";
  	}
  	if (count($auctionResults) > 0) {
  	  echo "</table>";
  	}
    echo "</div>"; // span12
    echo "</div>"; // row-fluid

    echo "<div class='row-fluid'>
            <div class='span12 center'>
              <h4>New Auction</h4>";
    // allow user to select one player from list of players eligible to be auctioned.
    $players = PlayerDao::getPlayersForAuction($currentYear);
    echo "<div class='row-fluid'>
            <div class='span6 center'><div class='chooser'>";
    echo "<label for='player'>Select Player:</label>&nbsp
          <select id='player' class='span8 smallfonttable' name='player' 
                  onchange='showPlayer(this.value)'>
          <option value='0'></option>";
    foreach ($players as $player) {
      echo "<option value='" . $player->getId() . "'" . ">" . $player->getFullName()
          . ", " . $player->getPositionString() . " ("
          . $player->getMlbTeam()->getAbbreviation() . ")</option>";
    }
    echo "</select></div>"; // chooser
    echo "<div id='playerDisplay'></div>";
    echo "</div>"; // span6

    // allow user to select which team bid on player & how much they bid
    echo "<div class='span6 center'><div class='chooser'>";
    $teams = TeamDao::getAllTeams();
    echo "<label for='team'>Select Team:</label>&nbsp
          <select name='team' class='span8 smallfonttable' onchange='showTeam(this.value)'>
                           <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName()
          . " (" . $team->getAbbreviation() . ")</option>";
    }
    echo "</select></div>"; // chooser
    echo "<div id='teamDisplay'></div>";
    echo "</div>"; // span6
    echo "</div>"; // row-fluid
    
    echo "<div id='auctionButton' style='display:none'>
            <div class=\"input-prepend\">
              <label for='auction_amount'>Auction amount:</label>&nbsp
              <span class=\"add-on\">$</span>
              <input type=number id='auction_amount' name='auction_amount'
                   placeholder='Enter non-zero value'></div>
            <p><button class=\"btn btn-primary\" name='auction' 
                       type=\"submit\">Auction player</button>
            &nbsp&nbsp<button class=\"btn\" name='cancel' type=\"submit\">Cancel</button></p>
          </div>";
  }
  echo "</form>";
  echo "</div>"; // span12
  echo "</div>"; // row-fluid

  // Footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
