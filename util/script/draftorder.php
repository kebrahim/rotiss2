<?php
  require_once 'commonScript.php';
  CommonScript::requireFileIn('/../../dao/', 'draftPickDao.php');

  // TODO user inputs year & file
  $year = 2016;
  $filename = "Draft_Order_2016.csv";
  $saveData = false;

  // read in draft order from file
  $fh = fopen($filename, 'r');
  $header = fgetcsv($fh);
  $data = array();
  while ($line = fgetcsv($fh)) {
    $data[] = array_combine($header, $line);
  }
  fclose($fh);

  // Rounds 1-4
  foreach ($data as $line) {
    //print_r($line);
    $draftPick = new DraftPick(-1, $line["team"], $year, $line["round"], $line["pick"], null, null,
        false);
    if ($saveData) {
      $draftPick = DraftPickDao::createDraftPick($draftPick);
    }
    echo $draftPick->toString() . " - " . $draftPick->getTeam()->getAbbreviation() . "<br/>";

    $draftPickRev = new DraftPick(-1, $line["team"], $year, $line["round"] + 1,
        (17 - $line["pick"]), null, null, false);
    if ($saveData) {
      $draftPickRev = DraftPickDao::createDraftPick($draftPickRev);
    }
    echo $draftPickRev->toString() . " - " . $draftPickRev->getTeam()->getAbbreviation() . "<br/>";
  }

  // Rounds 5-20
  for ($round = 5; $round < 20; $round += 2) {
    // randomize team IDs
    $numbers = range(1,16);
    shuffle($numbers);

    $pick = 1;
    foreach ($numbers as $number) {
      $draftPick = new DraftPick(-1, $number, $year, $round, $pick, null, null,
          false);
      if ($saveData) {
        $draftPick = DraftPickDao::createDraftPick($draftPick);
      }
      echo $draftPick->toString() . " - " . $draftPick->getTeam()->getAbbreviation() . "<br/>";

      $draftPickRev = new DraftPick(-1, $number, $year, $round + 1, (17 - $pick++), null, null,
          false);
      if ($saveData) {
        $draftPickRev = DraftPickDao::createDraftPick($draftPickRev);
      }
      echo $draftPickRev->toString() . " - " .
          $draftPickRev->getTeam()->getAbbreviation() . "<br/>";
    }
  }
?>