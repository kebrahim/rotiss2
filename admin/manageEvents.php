<?php
  require_once '../util/sessions.php';
  require_once '../util/time.php';

  SessionUtil::checkUserIsLoggedInSuperAdmin();
  // if year is not specified, use the current year.
  $redirectUrl = "admin/manageEvents.php";
  if (isset($_REQUEST["year"])) {
  	$year = $_REQUEST["year"];
  	$redirectUrl .="?year=$year";
  } else {
  	$year = TimeUtil::getCurrentYear();
  }
  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>

<?php 
  require_once '../util/layout.php';
  LayoutUtil::displayHeadTag("Manage Events", false);
?>

<script>
// shows the draft page for the specified year
function showYear(year) {
    // If year is blank, then clear the yearDisplay div.
	if (year=="" || year=="0") {
		document.getElementById("yearDisplay").innerHTML="";
		return;
	}

	// Display team information.
	getRedirectHTML(document.getElementById("yearDisplay"),
	    "displayYear.php?type=events&year=" + year);
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
  require_once '../dao/eventDao.php';
  require_once '../util/yearManager.php';

  // Nav bar
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_EVENTS_BUTTON);

  
  // allow user to choose year.
  YearManager::displayYearChooser($year, EventDao::getMinimumYear(), EventDao::getMaximumYear());
  
  echo "<div class='row-fluid'>
          <div class='span12 center'>";
  
  if (isset($_POST['save'])) {
    $events = EventDao::getEventsByYear($year);
    foreach ($events as $event) {
      $datePicker = "eventDate" . $event->getEventType();
      $updatedEventDate = $_POST[$datePicker];
      $event->setEventDate($updatedEventDate);
      EventDao::updateEvent($event);
    }
    echo "<br/><div class='alert alert-success'>Events Updated!</div>";
  }
  
  echo "<FORM ACTION='manageEvents.php' METHOD=POST>";
  echo "<div id='yearDisplay'></div>";
?>

<script>
  // initialize yearDisplay with selected year
  showYear(document.getElementById("year").value);
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
