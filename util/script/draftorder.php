<?php
  require_once 'commonScript.php';
  CommonScript::requireFileIn('/../../dao/', 'draftPickDao.php');

  // read in draft order from file
  $fh = fopen("Draft_Order_2013.csv", 'r');
  $header = fgetcsv($fh);

  $data = array();
  while ($line = fgetcsv($fh)) {
    $data[] = array_combine($header, $line);
  }
  fclose($fh);

  foreach ($data as $line) {
    print_r($line);
    echo "<br/>";
    $orig = ($line["originalteam"] == "") ? null : $line["originalteam"];
    $draftPick = new DraftPick(-1, $line["team"], 2013, $line["round"], $line["pick"], $orig, null);
    //$draftPick = DraftPickDao::createDraftPick($draftPick);
    echo $draftPick->toString() . " - " . $draftPick->getTeam()->getAbbreviation() .
       (($draftPick->getOriginalTeam() == null) ? "" :
          (" (" . $draftPick->getOriginalTeam()->getAbbreviation() . ")")) .
    "<br/><br/>";
  }
?>