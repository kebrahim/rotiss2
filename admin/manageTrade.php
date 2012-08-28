<html>
<head>
<title>Trading</title>
</head>

<script language="javascript">
function toggle(teamId) {
  var head = document.getElementById("headerbox" + teamId);
  var ele = document.getElementById("tradebox" + teamId);
  if (ele.style.display == "block") {
    ele.style.display = "none";
    head.style.display = "none";
  } else {
    ele.style.display = "block";
    head.style.display = "block";
  }
}

function selectTeam(position, teamid) {
  // TODO when team is selected, remove it from the other dropdown.
  var otherPosition = "1";
  if (position == "1") {
    otherPosition = "2";
  }
  var selectedTeam = document.getElementsByName("team"+position);
  var otherTeam = document.getElementsByName("team"+otherPosition);
  
  // If teamid is blank, then clear out that position.
  if (teamid=="" || teamid=="0") {
    document.getElementById("teamDisplay"+position).innerHTML="";
    // TODO hide trade button if it's visible
    // TODO put previously selected team back in other drop-down
	return;
  }

  // Only show trade button if two teams are selected
  var tradeButton = document.getElementById("tradeButton");
  // TODO if otherTeam.hasName then set display=block
  tradeButton.style.display = "block";
  
  if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  } else {// code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
	  document.getElementById("teamDisplay"+position).innerHTML=xmlhttp.responseText;
	}
  }
  xmlhttp.open("GET","displayTeamForTrade.php?team_id="+teamid+"&position="+position,true);
  xmlhttp.send();
}
</script>

<body>

<?php
  require_once '../dao/teamDao.php';
  require_once '../util/time.php';
  require_once '../entity/trade.php';
  
  echo "<center><h1>Let's Make a Deal!</h1>";
  echo "<FORM ACTION='manageTrade.php' METHOD=POST>";

  // If trade button was pressed, execute validated trade.
  if(isset($_POST['trade'])) {
    // Create trade scenario.
    $trade = new Trade();
    $trade->parseTradeFromPost();

    // display what will be traded
    $trade->showTradeSummary();

    // request final confirmation of trade before execution
    echo "Confirm trade: <br>";
    echo "<input class='button' type=submit name='confirmTrade' value='Confirm'>";
    echo "<input class='button' type=submit name='cancelTrade' value='Cancel'><br>";

    // repost everything in POST, except for 'trade'
    // TODO should probably change this to use SESSION
    foreach($_POST as $key=>$value) {
      if ($key != "trade") {
        if (is_array($value)) {
          foreach ($value as $val) {
            echo "<input type='hidden' name='" . $key . "[]' value='" . $val . "'>";
          }
        } else {
          echo "<input type='hidden' name='" . $key . "' value='" . $value . "'>";
        }
      }
    }
  } elseif (isset($_POST['confirmTrade'])) {
    // Re-create trade scenario from POST
    // TODO pull data from session
    $trade = new Trade();
    $trade->parseTradeFromPost();

    // Validate trade.
    if ($trade->validateTrade()) {
      // Initiate trade & report results.
      $trade->initiateTrade();
    } else {
      echo "<h3>Cannot execute trade! Please try again.</h3>";
    }
  } else {
    // allow user to select two teams.
    $teams = TeamDao::getAllTeams();
    echo "<table><tr>";
    echo "<td width='50%' valign='top'>";
    echo "<center>Select Team:<br><select name='team1' onchange='selectTeam(1, this.value)'>
                         <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName() . "</option>";
    }
    echo "</select></center><br>";
    echo "<div id='teamDisplay1'></div></td>";
    echo "<td width='100%' valign='top'>";
    echo "<center>Select Team:<br><select name='team2' onchange='selectTeam(2, this.value)'>
                         <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName() . "</option>";
    }
    echo "</select></center><br>";
    echo "<div id='teamDisplay2'></div></td>";
    echo "</tr></table>";

    echo "<div id='tradeButton' style='display:none'>
            <input class='button' type=submit name='trade' value='Initiate Trade'>
            <input class='button' type=submit name='cancel' value='Cancel'>
          </div>";
  }
  echo "</form>";
?>

</body>
</html>
