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

function showTeam(pos, teamid) {
  if (teamid=="" || teamid=="0") {
    document.getElementById("teamDisplay"+pos).innerHTML="";
	return;
  }
  if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  } else {// code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
	  document.getElementById("teamDisplay"+pos).innerHTML=xmlhttp.responseText;
	}
  }
  xmlhttp.open("GET","displayTeamForTrade.php?team_id="+teamid,true);
  xmlhttp.send();
}
</script>

<body>

<?php
  require_once '../dao/teamDao.php';
  require_once '../util/time.php';
  require_once '../entity/trade.php';

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
    echo "<center><h1>Let's Make a Deal!</h1>";

    // allow user to select two teams.
    // TODO when team is selected, remove it from the other dropdown.
    $teams = TeamDao::getAllTeams();
    echo "<table><tr>";
    echo "<td width='50%' valign='top'>";
    echo "<center>Select Team:<br><select name='team1' onchange='showTeam(1, this.value)'>
                         <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName() . "</option>";
    }
    echo "</select></center><br>";
    echo "<div id='teamDisplay1'></div></td>";
    echo "<td width='100%' valign='top'>";
    echo "<center>Select Team:<br><select name='team2' onchange='showTeam(2, this.value)'>
                         <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName() . "</option>";
    }
    echo "</select></center><br>";
    echo "<div id='teamDisplay2'></div></td>";
    echo "</tr></table>";

    // TODO only show trade button if two teams are selected
    echo "<input class='button' type=submit name='trade' value='Initiate Trade'>";
    echo "<input class='button' type=submit name='cancel' value='Cancel'>";
  }
  echo "</form>";
?>

</body>
</html>
