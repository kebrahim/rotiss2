<?php
  require_once 'util/sessions.php';
  SessionUtil::checkUserIsLoggedIn();
?>

<html>
<head>
<title>Rotiss.com - Draft</title>
<link href='css/style.css' rel='stylesheet' type='text/css'>
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
	    "admin/displayYear.php?type=auction&year=" + year);
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
  require_once 'dao/auctionDao.php';
  require_once 'util/navigation.php';
  require_once 'util/time.php';

  // Display header.
  NavigationUtil::printHeader(true, true, NavigationUtil::AUCTION_BUTTON);
  echo "<div class='bodycenter'>";
  
  // if year is not specified, use the current year.
  if (isset($_REQUEST["year"])) {
    $year = $_REQUEST["year"];
  } else {
    $year = TimeUtil::getCurrentYear();
  }

  // allow user to choose year
  $minYear = AuctionResultDao::getMinimumAuctionYear();
  $maxYear = AuctionResultDao::getMaximumAuctionYear();
  if ($year < $minYear) {
  	$year = $minYear;
  } else if ($year > $maxYear) {
  	$year = $maxYear;
  }
  echo "<br/><label for='year'>Choose year: </label>";
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

  // Display footer
  NavigationUtil::printFooter();
?>
</body>
</html>