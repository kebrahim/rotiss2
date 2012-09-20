<html>
<head>
<title>Auction</title>
</head>

<style type="text/css">
html {height:100%;}
body {text-align:center;}
table {text-align:center;}
table.center {margin-left:auto; margin-right:auto;}
#column_container {padding:0; margin:0 0 0 50%; width:50%; float:right;}
#left_col {float:left; width:100%; margin-left:-100%; text-align:center;}
#left_col_inner {padding:10px;}
#right_col {float:right; width:100%; text-align:center;}
#right_col_inner {padding:10px;}
</style>

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
	xmlhttp.open("GET","displayPlayerForTransaction.php?type=auction&player_id="+playerId,true);
	xmlhttp.send();
}

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
	xmlhttp.open("GET","displayTeamForTrade.php?type=auction&team_id="+teamId,true);
	xmlhttp.send();
}
</script>

<body>

<?php
  require_once '../dao/auctionDao.php';
  require_once '../dao/playerDao.php';
  require_once '../dao/teamDao.php';
  require_once '../entity/auction.php';
  require_once '../util/time.php';
  
  echo "<h1>Going once, Going twice, Sold!</h1>";
  echo "<FORM ACTION='manageAuction.php' METHOD=POST>";

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
  	  echo "<br/><input class='button' type=submit name='confirmAuction' value='Confirm'>";
  	  echo "<input class='button' type=submit name='cancelAuction' value='Cancel'><br>";
  	  
  	  // repost everything in POST, except for 'auction'
  	  // TODO change this to use SESSION
  	  foreach($_POST as $key=>$value) {
  	  	if ($key != "auction") {
  	  	  if (is_array($value)) {
  	  		foreach ($value as $val) {
  	  		  echo "<input type='hidden' name='" . $key . "[]' value='" . $val . "'>";
  	        }
  	      } else {
  	        echo "<input type='hidden' name='" . $key . "' value='" . $value . "'>";
  	      }
        }
  	  }
  	} else {
  	  echo "<h3>Cannot execute auction! Please <a href='manageAuction.php'>try again</a>.</h3>";
  	}
  } elseif (isset($_POST['confirmAuction'])) {
    // Re-create auction scenario
  	$auction = new Auction();
    // TODO pull data from session
  	$auction->parseAuctionFromPost();
  	
  	// Validate auction.
  	if ($auction->validateAuction()) {
  	  // Initiate auction & report results.
      $auction->initiateAuction();
  	  echo "<br><a href='manageAuction.php'>Let's do it again!</a><br>";
  	} else {
  	  echo "<h3>Cannot execute auction! Please <a href='manageAuction.php'>try again</a>.</h3>";
  	}
  } else {
  	// show auction results for current year
  	$currentYear = TimeUtil::getCurrentYear();
  	echo "<h2>Auction results " . $currentYear . "</h2>";
  	$auctionResults = AuctionResultDao::getAuctionResultsByYear($currentYear);
  	if (count($auctionResults) > 0) {
  	  echo "<table class='center' border>
  	          <tr><th>Player</th><th>Team</th><th>Amount</th></tr>";
  	}
  	foreach($auctionResults as $auctionResult) {
  	  echo "<tr><td>" . $auctionResult->getPlayer()->getFullName() . "</td>
  	            <td>" . $auctionResult->getTeam()->getName() . "</td>
  	            <td>" . $auctionResult->getCost() . "</td></tr>";
  	}
  	if (count($auctionResults) > 0) {
  	  echo "</table>";
  	}
  	
    // allow user to select one player from list of players eligible to be auctioned.
    $players = PlayerDao::getPlayersForAuction($currentYear); 
    echo "<div id='column_container'>";
    echo "<div id='left_col'><div id='left_col_inner'>";
    echo "Select Player:<br><select name='player' onchange='showPlayer(this.value)'>
          <option value='0'></option>";
    foreach ($players as $player) {
      echo "<option value='" . $player->getId() . "'" . ">" . $player->getFullName()
          . ", " . $player->getPositionString() . " ("
          . $player->getMlbTeam()->getAbbreviation() . ")</option>";
    }
    echo "</select><br>";
    echo "<div id='playerDisplay'></div><br/></div></div>";

    // allow user to select which team bid on player & how much they bid
    echo "<div id='right_col'><div id='right_col_inner'>";
    $teams = TeamDao::getAllTeams();
    echo "Select Team:<br><select name='team' onchange='showTeam(this.value)'>
                           <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName()
          . " (" . $team->getAbbreviation() . ")</option>";
    }
    echo "</select><br>";
    echo "<div id='teamDisplay'></div><br/></div></div></div>";

    echo "<div id='auctionButton' style='display:none'>
            <strong>Auction amount: </strong><input type=text name='amount'>
            <input class='button' type=submit name='auction' value='Auction player'>
            <input class='button' type=submit name='cancel' value='Cancel'>
          </div>";
  }
  echo "</form>";
?>

</body>
</html>
