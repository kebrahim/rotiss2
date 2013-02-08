<?php
  require_once '../util/sessions.php';

  SessionUtil::checkUserIsLoggedInAdmin();

  // Get team from REQUEST; otherwise, use logged-in user's team.
  $redirectUrl = "admin/manageSeltzer.php";
  if (isset($_REQUEST["team_id"])) {
  	$teamId = $_REQUEST["team_id"];
  	$redirectUrl .= "?team_id=$teamId";
  }

  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>

<?php
  require_once '../util/layout.php';
  LayoutUtil::displayHeadTag("Seltzer Contracts", false);
?>

<script>
//sets the display of the div with the specified id
function setDisplay(id, display) {
  var divid = document.getElementById(id);
  divid.style.display = display;
}

// shows the player with the specified id
function showPlayer(playerId) {
    // If playerId is blank, then clear the player div.
	if (playerId == "" || playerId == "0") {
		document.getElementById("playerDisplay").innerHTML="";

	    // hide seltzer buttons
		setDisplay("seltzerConfig", "none");
		setDisplay("seltzerButton", "none");
		return;
	}
	setDisplay("seltzerConfig", "block");
	setDisplay("seltzerButton", "inline");

	// Display player information.
	getRedirectHTML(document.getElementById("playerDisplay"),
	    "displayPlayer.php?type=seltzer&player_id="+playerId);
}

//populates the innerHTML of the specified elementId with the HTML returned by the specified
//htmlString
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

function selectType(type) {
	if (type == 1) {
		// major league type
		setDisplay("major_config", "block");
		setDisplay("minor_config", "none");
    } else if (type == 2) {
		// major type
		setDisplay("major_config", "none");
		setDisplay("minor_config", "block")
    } else {
		setDisplay("major_config", "none");
		setDisplay("minor_config", "none");
    }
}

function selectCallup(isCallup) {
    if (isCallup == 1) {
        // is callup = false; remove stat row and show uncalled price
        var minorTable = document.getElementById("minorTable");
        minorTable.deleteRow(1);

        // update price to default uncalled-up minor league price
        var currentPrice = document.getElementById("seltzer_minor_price").value;
        minorTable.rows[1].cells[1].innerHTML = minorTable.rows[1].cells[1].innerHTML.replace(
                "value=\"" + currentPrice + "\"", "value=\"15\"");
    } else if (isCallup == 2) {
        // is callup = true; show row for innings/ABs, which updates price
        var minorTable = document.getElementById("minorTable");
        var nextRowNumber = minorTable.rows.length;
        var posRow = minorTable.insertRow(nextRowNumber - 1);

        var posLabelCell = posRow.insertCell(0);
        getRedirectHTML(document.getElementById("minorTable").rows[nextRowNumber - 1].cells[0],
                "../util/playerManager.php?type=attribute&player_id=" +
                document.getElementById("seltzer_player").value + "&attr=minorseltzerstatlabel");

        var posCell = posRow.insertCell(1);
        posCell.innerHTML = "<input id='seltzer_stat' type='text' class='input-mini center' " +
                                   "value='0' onchange='selectStat(this.value)'></input>";

        // update price to default called-up minor league price
        var currentPrice = document.getElementById("seltzer_minor_price").value;
        minorTable.rows[2].cells[1].innerHTML = minorTable.rows[2].cells[1].innerHTML.replace(
                "value=\"" + currentPrice + "\"", "value=\"20\"");
    }
}

function selectStat(stat) {
    var minorTable = document.getElementById("minorTable");
    var divider = 0;
    if (minorTable.rows[1].cells[0].innerHTML.indexOf("At Bats") == -1) {
      // pitcher - divide by 6 IP
      divider = 6;
    } else {
      // batter - divide by 25 AB
      divider = 25;
    }
    var calculatedPrice = 20 + (2 * Math.floor(stat / divider));

    // update price to calculated price
    var currentPrice = document.getElementById("seltzer_minor_price").value;
    minorTable.rows[2].cells[1].innerHTML = minorTable.rows[2].cells[1].innerHTML.replace(
            "value=\"" + currentPrice + "\"", "value=\"" + calculatedPrice + "\"");
}

</script>

<body>

<?php
  require_once '../dao/playerDao.php';
  require_once '../dao/teamDao.php';
  require_once '../entity/contractScenario.php';
  require_once '../entity/team.php';
  require_once '../manager/contractManager.php';
  require_once '../util/teamManager.php';
  require_once '../util/time.php';

  // Nav bar
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_CONTRACTS_BUTTON);
  echo "<FORM ACTION='manageSeltzer.php' METHOD=POST>";

  if (isset($_POST['contract'])) {
    echo "<div class='row-fluid'>
            <div class='span12 center'>";

    // create contract scenario
    $contractScenario = new ContractScenario();
    $contractScenario->parseContractsFromPost();

    // if valid, show summary and request final confirmation.
    if ($contractScenario->validateContracts()) {
      $contractScenario->showContractSummary();
      echo "<p><button class='btn btn-primary' name='confirmContract'
                       type='submit'>Confirm</button>&nbsp&nbsp
               <a href='manageSeltzer.php?team_id=" . $contractScenario->getTeamId() . "'
                  class='btn'>Cancel</a>
            </p>";
    } else {
      echo "<h4>
              Cannot execute contract transaction! Please <a href='manageSeltzer.php?team_id=" .
              $contractScenario->getTeamId() . "' class='btn btn-primary'>try again</a>
            </h4>";
    }
    echo "</div>"; // span12
    echo "</div>"; // row-fluid
  } else if (isset($_POST['confirmContract'])) {
    echo "<div class='row-fluid'>
            <div class='span12 center'>";

    // re-create contract scenario from session
    $contractScenario = new ContractScenario();
    $contractScenario->parseContractsFromSession();

    // if valid, initiate transaction.
    if ($contractScenario->validateContracts()) {
      $contractScenario->initiateTransaction();
      echo "<a href='manageContracts.php?team_id=" . $contractScenario->getTeamId() . "'
               class='btn btn-primary'>Back to Contracts</a><br/><br/>";
    } else {
      echo "<h3>Cannot execute contract transaction! Please <a href='manageSeltzer.php?team_id=" .
          $contractScenario->getTeamId() . "' class='btn btn-primary'>try again</a></h3>";
    }
    echo "</div>"; // span12
    echo "</div>"; // row-fluid
  } else {
    // clear out seltzer variables from previous contract scenarios.
    SessionUtil::clearSessionVarsWithPrefix("seltzer_");

    $team = TeamDao::getTeamById($teamId);
    if ($team == null) {
      die("<h1>Team ID " . $teamId . " not found!</h1>");
    }
    echo "<div class='row-fluid'>
            <div class='span12 center'>";
    echo "<h3>" . $team->getAbbreviation() . ": Offer Seltzer Contract </h3>";

    // show list of players eligible to be offered a seltzer contract
    // i.e. players on team w/out contracts who weren't drafted before the seltzer cutoff
    $players = PlayerDao::getPlayersForSeltzerContracts($team->getId(), TimeUtil::getCurrentYear());
    echo "<div class='row-fluid'>
            <div class='span6 center'><div class='chooser'>";
    echo "<label for='seltzer_player'>Select Player:</label>&nbsp
          <select id='seltzer_player' class='span8 smallfonttable' name='seltzer_player'
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

    // once player is selected show seltzer contract information based on type of contract
    echo "<div class='span6' id='seltzerConfig' style='display:none'>";
    echo "<h4>Seltzer contract configuration</h4><hr class='bothr'/>";

    echo "<table id='seltzer_table'
                 class='table vertmiddle table-striped table-condensed table-bordered center'>";
    echo "<tr><td><label for='seltzer_length'>Contract Length:</label></td>
          <td><select class='input-medium' id='seltzer_length' name='seltzer_length'>
            <option value='0' class='center'>-- Select Length --</option>
            <option value='1'>1-year</option>
            <option value='2'>2-year</option>
          </select></td></tr>";
    echo "<tr><td><label for='seltzer_type'>Contract Type:</label></td>
              <td><select class='input-medium' id='seltzer_type' name='seltzer_type'
                          onchange='selectType(this.value)'>
                    <option value='0'>-- Select Type --</option>
                    <option value='1'>Major League</option>
                    <option value='2'>Minor League</option>
                  </select></td></tr></table>";

    $week = TimeUtil::getCurrentWeekInSeason();
    $contractValue = ContractManager::getMajorSeltzerContractValue($week);
    echo "<div id='major_config' style='display:none'>
            <h5>Major League Seltzer</h5>
            <table class='table vertmiddle table-striped table-condensed table-bordered center'>
              <tr>
                <td><label>Week in Season:</label></td>
                <td>" . $week . "</td>
              </tr>
              <tr>
                <td><label for='seltzer_price'>Contract Cost:</label></td>
                <td><input type='number' class='input-mini center' id='seltzer_price'
                       name='seltzer_price' value='$contractValue'></td>
              </tr>
            </table>
          </div>";

    // minor seltzering options [based on ABs or innings pitched]
    echo "<div id='minor_config' style='display:none'>
            <h5>Minor League Seltzer</h5>
            <table id='minorTable'
                   class='table vertmiddle table-striped table-condensed table-bordered center'>
              <tr>
                <td><label for='seltzer_callup'>Has Been Called Up?</label></td>
                <td><select class='input-small' id='seltzer_callup' name='seltzer_callup'
                            onchange='selectCallup(this.value)'>
                      <option value='1'>No</option>
                      <option value='2'>Yes</option>
                    </select></td>
              </tr>
              <tr>
                <td><label for='seltzer_minor_price'>Contract Cost:</label></td>
                <td><input type='text' class='input-mini center' id='seltzer_minor_price'
                           name='seltzer_minor_price'
                           value='" . Contract::UNCALLED_MINOR_CONTRACT . "' readonly='true'></td>
              </tr>
            </table>
          </div>";

    echo "</div>"; // span6
    echo "</div>"; // row-fluid

    echo "<p>
            <button id='seltzerButton' style='display:none' class='btn btn-primary' name='contract'
                    type='submit'>Sign Contract</button>
            &nbsp&nbsp
            <a href='manageContracts.php?team_id=" . $team->getId() . "' class='btn'>Cancel</a>
          </p>";
    echo "<input type='hidden' name='seltzer_teamid' value='" . $team->getId() . "'>";

    echo "  </div>
          </div>"; // span12, row-fluid
  }

  echo "</form>";

  // Footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
