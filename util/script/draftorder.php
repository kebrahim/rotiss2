<?php
  require_once 'commonScript.php';
  CommonScript::requireFileIn('/../../dao/', 'draftPickDao.php');

  $saveData = false;

  // read in draft order from file
  $fh = fopen("Draft_Order_2015.csv", 'r');
  $header = fgetcsv($fh);

  $data = array();
  while ($line = fgetcsv($fh)) {
    $data[] = array_combine($header, $line);
  }
  fclose($fh);

  // Rounds 1-2
  foreach ($data as $line) {
    //print_r($line);
    $draftPick = new DraftPick(-1, $line["team"], 2015, 1, $line["pick"], null, null,
        false);
    if ($saveData) {
      $draftPick = DraftPickDao::createDraftPick($draftPick);
    }
    echo $draftPick->toString() . " - " . $draftPick->getTeam()->getAbbreviation() . "<br/>";

    $draftPickRev = new DraftPick(-1, $line["team"], 2015, 2, (17 - $line["pick"]), null, null,
        false);
    if ($saveData) {
      $draftPickRev = DraftPickDao::createDraftPick($draftPickRev);
    }
    echo $draftPickRev->toString() . " - " . $draftPickRev->getTeam()->getAbbreviation() . "<br/>";
  }

  // Rounds 3-20
  for ($round = 3; $round < 20; $round += 2) {
    // randomize team IDs
    $numbers = range(1,16);
    shuffle($numbers);

    $pick = 1;
    foreach ($numbers as $number) {
      $draftPick = new DraftPick(-1, $number, 2015, $round, $pick, null, null,
          false);
      if ($saveData) {
        $draftPick = DraftPickDao::createDraftPick($draftPick);
      }
      echo $draftPick->toString() . " - " . $draftPick->getTeam()->getAbbreviation() . "<br/>";

      $draftPickRev = new DraftPick(-1, $number, 2015, $round+1, (17 - $pick++), null, null,
          false);
      if ($saveData) {
        $draftPickRev = DraftPickDao::createDraftPick($draftPickRev);
      }
      echo $draftPickRev->toString() . " - " .
          $draftPickRev->getTeam()->getAbbreviation() . "<br/>";
    }
  }
?>