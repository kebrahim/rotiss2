<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
?>

<html>
<head>
<title>Rotiss.com - Manage Brognas</title>
<link href='../css/style.css' rel='stylesheet' type='text/css'>
</head>

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
	    "displayYear.php?type=brognas&year=" + year);
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
  require_once '../dao/brognaDao.php';
  require_once '../util/time.php';
  require_once '../util/navigation.php';

  // Display header.
  NavigationUtil::printHeader(true, false, NavigationUtil::MANAGE_BROGNAS_BUTTON);
  echo "<div class='bodycenter'>";

  // if year isn't specified, use the current year, based on keeper night.
  if (isset($_REQUEST["year"])) {
    $year = $_REQUEST["year"];
  } else {
    $year = TimeUtil::getYearBasedOnKeeperNight();
  }

  // allow user to change year
  $minYear = BrognaDao::getMinimumYear();
  $maxYear = BrognaDao::getMaximumYear();
  echo "<br/><label for='year'>Choose year:</label>&nbsp";
  echo "<select id='year' name='year' onchange='showYear(this.value)'>";
  for ($yr = $minYear; $yr <= $maxYear; $yr++) {
  	echo "<option value='" . $yr . "'";
  	if ($yr == $year) {
  		echo " selected";
  	}
  	echo ">$yr</option>";
  }
  echo "</select>";
  echo "<div id='yearDisplay'></div><br/>";
?>
      
<script>
  // initialize yearDisplay with selected year
  showYear(document.getElementById("year").value);
</script>
      
<?php
  echo "</div>";
  // TODO should this be editable?

  // Footer
  NavigationUtil::printFooter();
?>

</body>
</html>
