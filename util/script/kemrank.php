<?php
  require_once 'commonScript.php';
  CommonScript::requireFileIn('/../../dao/', 'playerDao.php');
  CommonScript::requireFileIn('/../../dao/', 'rankDao.php');

  $teamId = 1;
  $lastYear = 2012;
  $year = 2013;
  //$unranked = PlayerDao::getPlayersForRanking($teamId, $lastYear);
  $auctioned = PlayerDao::getPlayersForAuction($year);
  echo "Last Name,First Name,MLB Team,Positions,Age,Fantasy Team,FPTS,Placeholder<br/>";

  $cumRanks = RankDao::calculateCumulativeRanksByYear($year);
  foreach ($auctioned as $player) {
    $team = $player->getFantasyTeam() == null ? "--" : $player->getFantasyTeam()->getAbbreviation();

    echo $player->getLastName() . "," . $player->getFirstName() . "," .
         $player->getMlbTeam()->getAbbreviation() . "," . $player->getPositionString() . "," .
         $player->getAge() . "," .
         $team . "," .
         StatDao::getStatByPlayerYear($player->getId(), $lastYear)->getStatLine()->getFantasyPoints() . "," .
         getCumRank($cumRanks, $player->getId()) .
    "<br/>";
  }

  function getCumRank($cumRanks, $playerId) {
    foreach ($cumRanks as $cumRank) {
      if ($cumRank->getPlayer()->getId() == $playerId) {
        return $cumRank->getRank();
      }
    }
    return -1;
  }
?>
