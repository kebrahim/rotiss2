<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'ballDao.php');
CommonEntity::requireFileIn('/../dao/', 'brognaDao.php');
CommonEntity::requireFileIn('/../dao/', 'contractDao.php');
CommonEntity::requireFileIn('/../dao/', 'cumulativeRankDao.php');
CommonEntity::requireFileIn('/../dao/', 'draftPickDao.php');
CommonEntity::requireFileIn('/../dao/', 'userDao.php');
CommonEntity::requireFileIn('/../util/', 'playerManager.php');
CommonEntity::requireFileIn('/../util/', 'teamManager.php');
CommonEntity::requireFileIn('/../util/', 'time.php');

/**
 * Represents a rotiss team.
 */
class Team {
  private $teamId;
  private $name;
  private $league;
  private $division;
  private $abbreviation;
  private $sportslineImageName;
  private $owners;
  private $ownersLoaded;
  private $brognas;
  private $brognasLoaded;
  private $draftPicks;
  private $draftPicksLoaded;
  private $pingPongBalls;
  private $ballsLoaded;

  private static $STPETES_IMAGE_URL_PREFIX =
      "http://stpetesorium.baseball.cbssports.com/images/team-logo/";
  private static $STPETES_BKUP_IMAGE_URL_PREFIX =
      "http://stpetesorium.baseball.cbssports.com/team-logo";

  public function __construct($teamId, $name, $league, $division, $abbreviation,
      $sportslineImageName) {
    $this->teamId = $teamId;
    $this->name = $name;
    $this->league = $league;
    $this->division = $division;
    $this->abbreviation = $abbreviation;
    $this->sportslineImageName = $sportslineImageName;
    $this->ownersLoaded = false;
    $this->brognasLoaded = false;
    $this->draftPicksLoaded = false;
    $this->ballsLoaded = false;
  }

  public function getId() {
    return $this->teamId;
  }

  public function getName() {
    return $this->name;
  }

  public function getNameLink($isTopLevel) {
  	return $this->getIdLink($isTopLevel, $this->name . " (" . $this->abbreviation . ")");
  }

  public function getIdLink($isTopLevel, $linkText) {
    return "<a href='" . ($isTopLevel ? "" : "../") . "teamPage.php?team_id=" . $this->teamId .
        "'>" . $linkText . "</a>";
  }

  public function getLeague() {
    return $this->league;
  }

  public function getDivision() {
    return $this->division;
  }

  public function getAbbreviation() {
    return $this->abbreviation;
  }

  public function getSportslineImageName() {
    return $this->sportslineImageName;
  }

  public function getSportslineImageUrl() {
    if ($this->getId() != 6) {
      return Team::$STPETES_IMAGE_URL_PREFIX . $this->sportslineImageName;
    } else {
      return Team::$STPETES_BKUP_IMAGE_URL_PREFIX . $this->sportslineImageName;
    }
  }

  public function getSportslineImg($size) {
  	return "<img class='img_" . $size . "' src='" .
  	    $this->getSportslineImageUrl() . "'>";
  }

  public function getOwners() {
    if ($this->ownersLoaded != true) {
      $this->owners = UserDao::getUsersByTeamId($this->teamId);
      $this->ownersLoaded = true;
    }
    return $this->owners;
  }

  public function getOwnersString() {
    $first_owner = 1;
    $ownerString = '';
    foreach ($this->getOwners() as $owner) {
      if ($first_owner == 0) {
        $ownerString .= ', ';
      } else {
        $first_owner = 0;
      }
      $ownerString .= $owner->getFullName();
    }
    return $ownerString;
  }

  /**
   * Returns the brogna info associated with this team.
   */
  public function getBrognas() {
    if ($this->brognasLoaded != true) {
      $this->brognas = BrognaDao::getBrognasByTeamId($this->teamId);
      $this->brognasLoaded = true;
    }
    return $this->brognas;
  }

  /**
   * Returns the draft picks associated with this team.
   */
  public function getDraftPicks() {
    if ($this->draftPicksLoaded != true) {
      $this->draftPicks = DraftPickDao::getDraftPicksByTeamId($this->teamId);
      $this->draftPicksLoaded = true;
    }
    return $this->draftPicks;
  }

  /**
   * Returns the ping pong balls associated with this team.
   */
  public function getPingPongBalls() {
    if ($this->ballsLoaded != true) {
      $this->pingPongBalls = BallDao::getPingPongBallsByTeamId($this->teamId);
      $this->ballsLoaded = true;
    }
    return $this->pingPongBalls;
  }

  public function displayTeamInfo() {
    echo "<h4>" . $this->getName() . "</h4>";
    echo $this->getSportslineImg(72);
    echo "<br/><p class='ptop'>" . $this->getOwnersString() . "</p>";
  }

  public function hasContracts() {
    $contracts = $this->filterContractsByYear(
        ContractDao::getContractsByTeamId($this->teamId),
        TimeUtil::getYearByEvent(Event::AUCTION), 3000, true);
    return (count($contracts) > 0);
  }

  public function displayAllContracts() {
    $currentYear = TimeUtil::getYearByEvent(Event::AUCTION);
    $this->displayContracts($currentYear, 3000, false);
  }

  /**
   * Display all contract information for this team.
   */
  public function displayContracts($minYear, $maxYear, $isSelectable) {
    $contracts = $this->filterContractsByYear(
        ContractDao::getContractsByTeamId($this->teamId), $minYear, $maxYear, true);
    if (count($contracts) == 0) {
      return;
    }
    echo "<a id='contracts'></a><h4>Contracts</h4>";
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
            <thead><tr>";
    if ($isSelectable) {
      echo "<th></th>";
    }
    echo "  <th colspan='2'>Player</th>
          	<th>Position</th>
	        <th>Team</th>
            <th>Age</th>
            <th>Years</th>
            <th>Price</th>
            <th>Sign Date</th>
            <th>Start Year</th>
            <th>End Year</th>
            <th>Type</th></tr></thead>";
    foreach ($contracts as $contract) {
      $player = $contract->getPlayer();
      echo "<tr>";
      if ($isSelectable) {
        echo "<td><input type=checkbox name='trade_t" . $this->getId() . "c[]'
                         value='" . $contract->getId() . "'></td>";
      }
      echo PlayerManager::getNameAndHeadshotRow($player) . "
                  <td>" . $player->getPositionString() . "</td>
                  <td>" . $player->getMlbTeam()->getImageTag(32) . "</td>
                  <td>" . $player->getAge() . "</td>
                  <td>" . $contract->getTotalYears() . "</td>
                  <td>" . $contract->getPrice() . "</td>
                  <td>" . $contract->getFormattedSignDate() . "</td>
                  <td>" . $contract->getStartYear() . "</td>
                  <td>" . $contract->getEndYear() . "</td>
                  <td>" . $contract->getType() . "</td></tr>";
    }
    echo "</table>";
  }

  public function displayContractsForTrade($minYear, $maxYear) {
  	$contracts = $this->filterContractsByYear(
  	    ContractDao::getContractsByTeamId($this->teamId), $minYear, $maxYear, true);
  	if (count($contracts) == 0) {
  	  return;
  	}

  	echo "<button type='button' class='btn btn-link' data-toggle='collapse'
  	              data-target='#contract-collapse" . $this->teamId . "'>
  	        <h5>Contracts</h5>
  	      </button>";

  	echo "<div id='contract-collapse" . $this->teamId . "' class='collapse out'>
  	      <table class='table vertmiddle table-striped table-condensed table-bordered center
  	                    smallfonttable'>
  	        <thead><tr>
  	          <th class='checkth'></th><th colspan='2'>Player</th><th>Years</th><th>Price</th>
  	          <th>Start</th><th>End</th><th>Type</th>
  	        </tr></thead>";
  	foreach ($contracts as $contract) {
  		$player = $contract->getPlayer();
  		echo "<tr>";
  		echo "<td><input type=checkbox name='trade_t" . $this->getId() . "c[]'
  			             value='" . $contract->getId() . "'></td>";
  		echo PlayerManager::getNameAndHeadshotRowAtLevel($player, false) . "
  		<td>" . $contract->getTotalYears() . "</td>
  		<td>" . $contract->getPrice() . "</td>
  		<td>" . $contract->getStartYear() . "</td>
  		<td>" . $contract->getEndYear() . "</td>
  		<td>" . $contract->getType() . "</td></tr>";
  	}
  	echo "</table></div>";
  }

  /**
   * Display contracts for the keeper page, filtered by year.
   */
  function displayContractsForKeepers($minYear, $maxYear, $readOnly) {
  	echo "<h4>Contracts</h4>";

  	$contracts = $this->filterContractsByYear(
  	    ContractDao::getContractsByTeamId($this->teamId), $minYear, $maxYear, true);

  	echo "<div id='keeperdiv'";
  	if (count($contracts) == 0) {
  	  echo " style='display:none'>";
  	} else {
  	  echo ">";
  	}

  	echo "<table id='keepertable' class='table vertmiddle table-striped
  	                                     table-condensed table-bordered center'><thead><tr>";
  	echo "  <th colspan=2>Player</th>
        	<th>Years Left</th>
  	        <th>Price</th>
  	        <th colspan=2>Years</th>
  	        <th";
  	if ($readOnly) {
  	  echo " style='display:none'";
  	}
  	echo      ">Buy Out</th>
  	        <th id='keeperRemoveColumn' style='display:none'>Actions</th></tr></thead>";
  	$keeperCount = 0;
  	foreach ($contracts as $contract) {
  	  $player = $contract->getPlayer();
  	  echo "<tr>";
	  echo "<td>" . $player->getMiniHeadshotImg() . "</td>
	 	    <td>" . $player->getFullName() . " (" . $player->getPositionString() . ") - " .
  	            $player->getMlbTeam()->getAbbreviation() . "</td>
  			<td>" . $contract->getYearsLeft() . "</td>
  			<td>" . $contract->getPrice() . "</td>
  			<td>" . $contract->getStartYear() . "</td>
  			<td>" . $contract->getEndYear() . "</td>";
      if (!$readOnly) {
  	    echo "<td><input type=checkbox name='keeper_buyout[]'
	             value='" . $contract->getId() . "'></td>";
      }
	  echo "</tr>";
	  $keeperCount++;
  	}
  	echo "</table></div>";
  	echo "<input type='hidden' name='keeper_savedkeepercount' value='" . $keeperCount . "'>";
  	echo "<input type='hidden' name='keeper_newkeepercount' value='0'>";
  }

  public function displayContractsForManagement($minYear, $maxYear) {
    $allContracts = ContractDao::getContractsByTeamId($this->teamId);

    // show contracts of players currently on team
    $contracts = $this->filterContractsByYearAndOwnership($allContracts, $minYear, $maxYear, true);
    $this->displayContractTable($contracts, "Contracts", true);

    // button to pickup existing contract
    echo "<p><button class='btn' name='addcontract' type='button'
                     onclick='addContract(". $this->getId() . ")'>
               Pick Up Existing Contract</button>
          </p><br/>";

    // show dropped contracts
    $droppedContracts = $this->filterContractsByYearAndOwnership(
        $allContracts, $minYear, $maxYear, false);
    $this->displayContractTable($droppedContracts, "Dropped Contracts", false);
  }

  function displayContractTable($contracts, $title, $ownsContracts) {
    echo "<div id='" . ($ownsContracts ?  "contractdiv" : "droppeddiv") . "'";
    if (count($contracts) == 0) {
      echo " style='display:none'>";
    } else {
      echo ">";
    }

    echo "<h4>$title</h4>";
    echo "<table ";
    if ($ownsContracts) {
      echo "id='contracttable' ";
    }
    echo "class='table vertmiddle table-striped table-condensed table-bordered center'><thead><tr>";
    echo "  <th colspan=2>Player</th>
            <th>Years</th>
            <th>Price</th>
            <th>Start Year</th>
            <th>End Year</th>
            <th>Type</th>";
    if ($ownsContracts) {
      echo "<th>Drop</th>
            <th id='contractRemoveColumn' style='display:none'>Actions</th>";
    }
    echo "</tr></thead>";

    $contractCount = 0;
    foreach ($contracts as $contract) {
      $player = $contract->getPlayer();
      echo "<tr>" .
              PlayerManager::getPlayerDetailsAndHeadshotRowAtLevel($player,
                  $player->getFullName() . " (" . $player->getPositionString() . ") - " .
                      $player->getMlbTeam()->getAbbreviation(), false) . "
              <td>" . $contract->getTotalYears() . "</td>
              <td>" . $contract->getPrice() . "</td>
              <td>" . $contract->getStartYear() . "</td>
              <td>" . $contract->getEndYear() . "</td>
              <td>" . $contract->getType() . "</td>";
      if ($ownsContracts) {
        echo "<td><input type=checkbox name='contract_drop[]'
                         value='" . $contract->getId() . "'></td>";
        $contractCount++;
      }
      echo "</tr>";
    }
    echo "</table></div>";
    if ($ownsContracts) {
      echo "<input type='hidden' name='contract_savedcontractcount'
                   value='" . $contractCount . "'>";
      echo "<input type='hidden' name='contract_newcontractcount' value='0'>";
    }
  }

  /**
   * Filters the specified array of contracts by min/max years; if includeAuction is false, then
   * auction contracts are also filtered out.
   */
  function filterContractsByYear($contracts, $minYear, $maxYear, $includeAuction) {
  	$filteredContracts = array();
  	foreach ($contracts as $contract) {
  	  if (($contract->getEndYear() >= $minYear) && ($contract->getStartYear() <= $maxYear)
  	      && ($includeAuction || ($contract->getType() != Contract::AUCTION_TYPE))) {
	    $filteredContracts[] = $contract;
  	  }
  	}
  	return $filteredContracts;
  }

  /**
   * Filters the specified array of contracts by min/max years and whether they belong to this team.
   */
  function filterContractsByYearAndOwnership($contracts, $minYear, $maxYear, $isOwned) {
    $filteredContracts = array();
    foreach ($contracts as $contract) {
      if (($contract->getEndYear() >= $minYear) && ($contract->getStartYear() <= $maxYear)
          && ($isOwned == $this->hasPlayer($contract->getPlayer()))) {
        $filteredContracts[] = $contract;
      }
    }
    return $filteredContracts;
  }

  /**
   * Returns true if this team has the specified player on its roster.
   */
  function hasPlayer(Player $player) {
    return ($player->getFantasyTeam() != null) &&
        ($player->getFantasyTeam()->getId() == $this->getId());
  }

  function displayAllDraftPicks() {
    $currentYear = TimeUtil::getYearByEvent(Event::SEASON_START);
    $this->displayDraftPicks($currentYear + 1, 3000, false);
  }

  /**
   * Display all draft pick information for this team.
   */
  function displayDraftPicks($minYear, $maxYear, $isSelectable) {
    if ((count($this->getPingPongBalls()) + count($this->getDraftPicks())) == 0) {
      return;
    }

    echo "<a id='draft'></a><h4>Draft Picks</h4>";
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
            <thead><tr>";
    if ($isSelectable) {
      echo "<th></th>";
    }
    echo "<th>Year</th><th>Overall</th><th>Round</th><th>Pick</th>
                       <th colspan=2>Player</th><th>Original Team</th></tr></thead>";
    foreach ($this->getPingPongBalls() as $pingPongBall) {
      if (($pingPongBall->getYear() < $minYear) || ($pingPongBall->getYear() > $maxYear)) {
        continue;
      }
      echo "<tr>";
      if ($isSelectable) {
        echo "<td><input type=checkbox name='trade_t" . $this->getId() . "dpb[]'
                         value='" . $pingPongBall->getId() . "'>
              </td>";
      }
      echo "<td>" . $pingPongBall->getYear() . "</td>
            <td>" . $pingPongBall->getOrdinal() . "</td>
            <td>Ping Pong</td>
            <td>" . $pingPongBall->getCost() . "</td>" .
            PlayerManager::getNameAndHeadshotRow($pingPongBall->getPlayer()) .
           "<td>--</td></tr>";
    }

    $yearToNumBalls = $this->getBallsArray();
    foreach ($this->getDraftPicks() as $draftPick) {
      if (($draftPick->getYear() < $minYear) || ($draftPick->getYear() > $maxYear)) {
        continue;
      }
      echo "<tr>";
      if ($isSelectable) {
        echo "<td><input type=checkbox name='trade_t" . $this->getId() . "dpp[]'
                         value='" . $draftPick->getId() . "'>
              </td>";
      }
      $numBalls = array_key_exists($draftPick->getYear(), $yearToNumBalls) ?
          $yearToNumBalls[$draftPick->getYear()] : 0;
      echo "<td>" . $draftPick->getYear() . "</td>
            <td>" . $draftPick->getOverallPick($numBalls) . "</td>
            <td>" . $draftPick->getRound() . "</td>
            <td>" . $draftPick->getPick() . "</td>" .
            PlayerManager::getNameAndHeadshotRow($draftPick->getPlayer()) .
           "<td>" . $draftPick->getOriginalTeamName() . "</td></tr>";
    }
    echo "</table>";
  }

  private function getBallsArray() {
    $yearToNumBalls = array();
    for ($bYear = BallDao::getMinimumYear(); $bYear <= BallDao::getMaximumYear(); $bYear++) {
      $yearToNumBalls[$bYear] = BallDao::getNumPingPongBallsByYear($bYear);
    }
    return $yearToNumBalls;
  }

  function displayDraftPicksForTrade($minYear, $maxYear) {
  	if ((count($this->getPingPongBalls()) + count($this->getDraftPicks())) == 0) {
  	  return;
  	}

  	echo "<button type='button' class='btn btn-link' data-toggle='collapse'
  	              data-target='#draft-collapse" . $this->teamId . "'>
  	        <h5>Draft Picks</h5>
  	      </button>";

  	echo "<div id='draft-collapse" . $this->teamId . "' class='collapse out'>
  	        <table class='table vertmiddle table-striped table-condensed table-bordered center
  	                    smallfonttable'><thead><tr>";
  	echo "<th class='checkth'></th><th>Year</th><th>Overall</th><th>Round</th><th>Pick</th>
  	      <th colspan=2>Original Team</th></tr></thead>";
  	foreach ($this->getPingPongBalls() as $pingPongBall) {
  	  // only show ping pong balls which have not been used yet, in the specified range of years
  	  if (($pingPongBall->getYear() < $minYear) || ($pingPongBall->getYear() > $maxYear) ||
  	      ($pingPongBall->getPlayer() != null)) {
  	    continue;
  	  }
  	  echo "<tr><td><input type=checkbox name='trade_t" . $this->getId() . "dpb[]'
  			               value='" . $pingPongBall->getId() . "'>
  			</td>
  	        <td>" . $pingPongBall->getYear() . "</td>
  	        <td>" . $pingPongBall->getOrdinal() . "</td>
  	        <td>PP</td>
  		    <td>" . $pingPongBall->getCost() . "</td>" .
  		   "<td colspan=2>--</td></tr>";
  	}
  	$yearToNumBalls = $this->getBallsArray();
  	foreach ($this->getDraftPicks() as $draftPick) {
  	  // only show draft picks which have not been used yet, in the specified range of years
  	  if (($draftPick->getYear() < $minYear) || ($draftPick->getYear() > $maxYear) ||
  	      ($draftPick->getPlayer() != null)) {
  	    continue;
  	  }
      $numBalls = array_key_exists($draftPick->getYear(), $yearToNumBalls) ?
          $yearToNumBalls[$draftPick->getYear()] : 0;
  	  echo "<tr>
  	          <td><input type=checkbox name='trade_t" . $this->getId() . "dpp[]'
  			               value='" . $draftPick->getId() . "'></td>
  	          <td>" . $draftPick->getYear() . "</td>
  	          <td>" . $draftPick->getOverallPick($numBalls) . "</td>
  	          <td>" . $draftPick->getRound() . "</td>
  		      <td>" . $draftPick->getPick() . "</td>" .
  		      TeamManager::getAbbreviationAndLogoRowAtLevel($draftPick->getOriginalTeam(), false) .
  		   "</tr>";
  	}
  	echo "</table></div>";
  }

  /**
   * Displays the ping pong ball information only, between the specified years.
   */
  function displayPingPongBalls($minYear, $maxYear) {
    echo "<h4>Ping Pong Balls</h4>";

    $pingPongBalls = $this->filterBallsByYear($this->getPingPongBalls(), $minYear, $maxYear);

    echo "<div id='ppballdiv'";
    if (count($pingPongBalls) == 0) {
      echo " style='display:none'>";
    } else {
      echo ">";
    }

    echo "<table id='ppballtable' class='table vertmiddle table-striped
  	                                     table-condensed table-bordered center'>
            <thead><tr><th>#</th><th>Price</th>
                <th id='ppRemoveColumn' style='display:none'>Actions</th></tr></thead>";
    $ballCount = 0;
    foreach ($pingPongBalls as $pingPongBall) {
      $ballCount++;
      echo "<tr><td>" . $ballCount . "</td>
                <td>" . $pingPongBall->getCost() . "</td></tr>";
    }
    echo "</table></div>";
    echo "<input type='hidden' name='keeper_savedppballcount' value='" . $ballCount . "'>";
    echo "<input type='hidden' name='keeper_newppballcount' value='0'>";
  }

  /**
   * Filters the specified array of ping pong balls by the min & max years.
   */
  function filterBallsByYear($balls, $minYear, $maxYear) {
    $filteredBalls = array();
    foreach ($balls as $ball) {
      if (($ball->getYear() >= $minYear) && ($ball->getYear() <= $maxYear)) {
        $filteredBalls[] = $ball;
      }
    }
    return $filteredBalls;
  }

  function displayAllBrognas() {
    $currentYear = TimeUtil::getYearByEvent(Event::KEEPER_NIGHT) + 1;
    $this->displayBrognas($currentYear, $currentYear, false, 0);
  }

  /**
   * Display all brogna information for this team, between the specified years. $isSelectable
   * controls whether the rows can be selected & a value can be entered [for trading].
   * $tradePosition indicates which of the two teams is trading brognas.
   */
  function displayBrognas($minYear, $maxYear, $isSelectable, $tradePosition) {
    if (count($this->getBrognas()) == 0) {
      return;
    }

    echo "<a id='brognas'></a><h4>Brognas</h4>";
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'
                 id='brognaTable'><thead><tr>";
    if ($isSelectable) {
      echo "<th></th>";
    }
    echo "<th>Year</th>
          <th><abbr title='Total brognas owned'>Total</abbr></th>
          <th><abbr title='Amount committed to contracts'>Contracts</abbr></th>
          <th><abbr title='Amount not committed to contracts'>Available</abbr></th>
          <th><abbr title='Banked from previous season'>Banked</abbr></th>
          <th>Traded In</th>
          <th>Traded Out</th>
          <th>Tradeable</th>";
    if ($isSelectable) {
      echo "<th id='headerbox" . $tradePosition . "' style='display:none'>To Trade</th>";
    }
    echo "</tr></thead>";
    foreach ($this->getBrognas() as $brogna) {
      // Only show brogna info between the min and max years.
      if (($brogna->getYear() < $minYear) || ($brogna->getYear() > $maxYear)) {
        continue;
      }
      echo "<tr>";
      if ($isSelectable) {
        if ($brogna->getTradeablePoints() > 0) {
          echo "<td><input type=checkbox name='trade_t" . $this->getId() . "b'
                                         onclick='toggle(" . $tradePosition . ")'
                                         value='" . $brogna->getYear() . "'>
                </td>";
        } else {
          echo "<td><input type=checkbox disabled></td>";
        }
      }
      echo "<td>" . $brogna->getYear() . "</td>
                  <td";
      if ($brogna->getTotalPoints() < 0) {
        echo " class='warning'";
      }
      $contractBrognas = ContractDao::getTotalPriceByTeamYear($this->getId(), $brogna->getYear());
      if (!$contractBrognas) {
        $contractBrognas = 0;
      }
      echo          "><strong>" . $brogna->getTotalPoints() . "</strong></td>
                  <td>" . $contractBrognas . "</td>
                  <td class='conf_msg'><strong>" . ($brogna->getTotalPoints() - $contractBrognas) . "</strong></td>
                  <td>" . $brogna->getBankedPoints() . "</td>
                  <td>" . $brogna->getTradedInPoints() . "</td>
                  <td>" . $brogna->getTradedOutPoints() . "</td>
                  <td>" . $brogna->getTradeablePoints() . "</td>";
      if ($isSelectable) {
        echo "<td id='tradebox" . $tradePosition . "' style='display:none'>
                  <input type='text' name='trade_t" . $this->getId() . "bv'
                         placeholder='Enter value 1 to " . $brogna->getTradeablePoints() . "'/>
              </td>";
      }
      echo "</tr>";
    }
    echo "</table>";
  }

  function displayBrognasForTrade($minYear, $maxYear, $tradePosition) {
  	if (count($this->getBrognas()) == 0) {
  	  return;
  	}

  	echo "<button type='button' class='btn btn-link' data-toggle='collapse'
  	              data-target='#brognas-collapse" . $this->teamId . "'>
  	        <h5>Brognas</h5>
   	      </button>";

  	echo "<div id='brognas-collapse" . $this->teamId . "' class='collapse out'>
  	        <table class='table vertmiddle table-striped table-condensed table-bordered center
  	                      smallfonttable brognatrade' id='brognaTable'><thead><tr>";
  	echo "<th class='checkth'></th><th>Year</th><th>Total</th><th>Tradeable</th>";
  	echo "<th id='headerbox" . $tradePosition . "' style='display:none'>To Trade</th></tr></thead>";
  	foreach ($this->getBrognas() as $brogna) {
  	  // Only show brogna info between the min and max years.
  	  if (($brogna->getYear() < $minYear) || ($brogna->getYear() > $maxYear)) {
  	    continue;
  	  }
  	  echo "<tr>";
	  if ($brogna->getTradeablePoints() > 0) {
  	    echo "<td><input type=checkbox name='trade_t" . $this->getId() . "b'
  		                 onclick='toggle(" . $tradePosition . ")'
  		                 value='" . $brogna->getYear() . "'>
  		      </td>";
  	  } else {
  	    echo "<td><input type=checkbox disabled></td>";
  	  }
      echo "<td>" . $brogna->getYear() . "</td>
  		    <td><strong>" . $brogna->getTotalPoints() . "</strong></td>
  		    <td>" . $brogna->getTradeablePoints() . "</td>
  		    <td id='tradebox" . $tradePosition . "' style='display:none'>
  	          <input type='text' name='trade_t" . $this->getId() . "bv'
  	                 class='input-medium center'
  			         placeholder='Enter value 1 to " . $brogna->getTradeablePoints() . "'/>
  		    </td>
        </tr>";
    }
  	echo "</table></div>";
  }

  function displayBrognasForAuction($year) {
    if (count($this->getBrognas()) == 0) {
      return;
    }

    echo "<h4>Brognas</h4>";
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'
                 id='brognaTable'><thead><tr>";
    echo "  <th>Year</th>
            <th><abbr title='Total brognas owned'>Total</abbr></th>
            <th><abbr title='Amount committed to contracts'>Contracts</abbr></th>
            <th><abbr title='Amount not committed to contracts'>Available</abbr></th>
          </tr></thead>";
    foreach ($this->getBrognas() as $brogna) {
      // Only show brogna info between the min and max years.
      if ($brogna->getYear() != $year) {
        continue;
      }
      $contractBrognas = ContractDao::getTotalPriceByTeamYear($this->getId(), $year);
      if (!$contractBrognas) {
        $contractBrognas = 0;
      }
      echo "<tr>
              <td>" . $brogna->getYear() . "</td>
              <td><strong>" . $brogna->getTotalPoints() . "</strong></td>
              <td>" . $contractBrognas . "</td>
              <td class='conf_msg'><strong>" . ($brogna->getTotalPoints() - $contractBrognas) .
                  "</strong></td>
            </tr>";
    }
    echo "</table>";
  }

  /**
   * Displays all of the players currently belonging to this team.
   */
  function displayPlayers() {
    $players = PlayerDao::getPlayersByTeam($this);
    if (count($players) == 0) {
      return;
    }

    $rankYear = TimeUtil::getYearByEvent(Event::OFFSEASON_START);
    $prevYear = $rankYear - 1;
    echo "<a id='roster'></a><h4>Roster</h4>";
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered center'>
            <thead><tr>";
    echo "<th></th>
          <th colspan=2>Player</th>
          <th>Position</th>
    	    <th>Team</th>
          <th>Age</th>
          <th>$prevYear Fantasy Pts</th>
          <th>$rankYear Rank</th></tr></thead>";
    $playerCount = 0;
    foreach ($players as $player) {
      $rank = CumulativeRankDao::getCumulativeRankByPlayerYear($player->getId(), $rankYear);
      echo "<tr>
              <td>" . ++$playerCount . "</td>" .
               PlayerManager::getNameAndHeadshotRow($player) .
               "<td>" . $player->getPositionString() . "</td>
                <td>" . $player->getMlbTeam()->getImageTag(32) . "</td>
                <td>" . $player->getAge() . "</td>
                <td>" . $player->getStatLine($prevYear)->getFantasyPoints() . "</td>" .
                $this->getRankCell($rank) .
           "</tr>";
    }
    echo "</table>";
  }

  /**
   * Returns a table cell containing the specified rank information.
   */
  function getRankCell($rank) {
    if ($rank == null) {
      return "<td>-</td>";
    }
    return "<td>" . $rank->getRank() . ($rank->isPlaceholder() ? " (PH)" : "") . "</td>";
  }

  public function displayPlayersForTrade($year) {
    // display players not currently under contract
    $players = PlayerDao::getPlayersToBeDroppedForKeepers($this, $year);
    if (count($players) == 0) {
      return;
    }

    echo "<button type='button' class='btn btn-link' data-toggle='collapse'
                  data-target='#players-collapse" . $this->teamId . "'>
            <h5>Players</h5>
          </button>";
    echo "<div id='players-collapse" . $this->teamId . "' class='collapse out'>";
    echo "<table class='table vertmiddle table-striped table-condensed table-bordered center
                        smallfonttable'>
            <thead><tr>
              <th class='checkth'></th>
              <th colspan=2>Player</th>
              <th>Position</th>
              <th>Team</th>
              <th>Age</th>
            </tr></thead>";
    foreach ($players as $player) {
      echo "<tr>
              <td><input type=checkbox name='trade_t" . $this->getId() . "p[]'
                         value='" . $player->getId() . "'></td>" .
              PlayerManager::getNameAndHeadshotRowAtLevel($player, false) . "
              <td>" . $player->getPositionString() . "</td>
              <td>" . $player->getMlbTeam()->getImageTag(32) . "</td>
              <td>" . $player->getAge() . "</td>
            </tr>";
    }
    echo "</table></div>";
  }
}
?>
