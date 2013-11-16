<?php
  require_once 'commonScript.php';
  CommonScript::requireFileIn('/../../dao/', 'playerDao.php');
  CommonScript::requireFileIn('/../../dao/', 'rankDao.php');

  function getTeamBrognasAndType($playerId, $year) {
    $contracts = ContractDao::getContractsByPlayerYear($playerId, $year);
    if (count($contracts) == 0) {
      $balls = BallDao::getPingPongBallsByPlayerYear($playerId, $year);
      if (count($balls) == 0) {     
        return "-,-,-";
      } elseif (count($balls) == 1) {
      	$ball = $balls[0];
        return $ball->getTeam()->getAbbreviation() . "," . $ball->getCost() . ",Ball";
      } else {
        return "toomanyballs," . count($balls) . ",-";
      }
    } elseif (count($contracts) == 1) {
      $contract = $contracts[0];
      return $contract->getTeam()->getAbbreviation() . "," . $contract->getPrice() . "," .
             $contract->getType();
    } else {
      return "toomanycontracts," . count($contracts) . ",-";
    }
  }

  echo "Year,Last Name,First Name,Position,team,Brognas,Type,FPTS<br/>";
  $stats = StatDao::getAllStats();
  foreach ($stats as $stat) {
  	$player = $stat->getPlayer();

    echo $stat->getYear() . "," .
         $player->getLastName() . "," . $player->getFirstName() . "," .
         $player->getPositionString() . "," .
         getTeamBrognasAndType($stat->getPlayerId(), $stat->getYear()) . "," .
         $stat->getStatLine()->getFantasyPoints();
    echo "<br/>";
  }
?>
