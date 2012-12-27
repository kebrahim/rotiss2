<?php
  require_once 'commonScript.php';
  CommonScript::requireFileIn('/../../dao/', 'draftPickDao.php');

  $teams = TeamDao::getAllTeams();
  $draftYear = 2013;
  echo "<h1>Generating $draftYear Draft...</h1>";
  for ($round = 1; $round <= 20; $round++) {
    foreach ($teams as $team) {
      $draftPick = new DraftPick(-1, $team->getId(), $draftYear, $round, null, null, null);
      //$draftPick = DraftPickDao::createDraftPick($draftPick);
      echo $draftPick->toString() . " - " . $draftPick->getTeam()->getAbbreviation() . "<br/>";
    }
  }
?>