<?php
  //$access_token = $_GET['access_token'];
  $crl = curl_init();
  $timeout = 30;
  //curl_setopt($crl, CURLOPT_URL,
 // 	"http://api.cbssports.com/fantasy/league/teams?access_token=$access_token&response_format=json");
  curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $timeout );
  $ret = curl_exec($crl);
  curl_close($crl);
  $data = json_decode($ret);
  echo "<html><table>";
  $teams = $data->body->teams;
  foreach ( $teams as $team ) {
    $name = $team->name;
    $logo = $team->logo;
    echo "<tr><td><img src='$logo'></td><td>$name</td>";
    $owners = $team->owners;
    foreach ( $owners as $owner ) {
      $ownname = $owner->name;
      echo "<td>$ownname</td>";
    }
    echo "</tr>";
  }
  echo "</table></html>";
?>