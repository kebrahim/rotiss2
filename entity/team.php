<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'ballDao.php');
CommonEntity::requireFileIn('/../dao/', 'brognaDao.php');
CommonEntity::requireFileIn('/../dao/', 'contractDao.php');
CommonEntity::requireFileIn('/../dao/', 'draftPickDao.php');
CommonEntity::requireFileIn('/../dao/', 'userDao.php');
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
  	return "<a href='" . ($isTopLevel ? "" : "../") . "summaryPage.php?team_id=" .
  			$this->teamId . "'>" . $this->name . " (" . $this->abbreviation . ")</a>";
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

  public function displayAllContracts() {
    $currentYear = TimeUtil::getYearBasedOnEndOfSeason();
    $this->displayContracts($currentYear, 3000, false, "left");
  }

  public function displayTeamInfo() {
    echo "<h3>" . $this->getName() . "</h3>";
    echo "<img src='" . $this->getSportslineImageUrl() . "'><br/><br/>";
    echo $this->getOwnersString() . "<br/>";
  }

  /**
  * Display all contract information for this team.
  */
  public function displayContracts($minYear, $maxYear, $isSelectable, $tableClass) {
    $contracts = $this->filterContractsByYear(
        ContractDao::getContractsByTeamId($this->teamId), $minYear, $maxYear, true);
    if (count($contracts) == 0) {
      return;
    }
    echo "<h4>Contracts</h4>";
    echo "<table class='$tableClass' border><tr>";
    if ($isSelectable) {
      echo "<th></th>";
    }
    echo "  <th></th><th>Name</th>
          	<th>Position</th>
	        <th>Team</th>
            <th>Age</th>
            <th>Years</th>
            <th>Price</th>
            <th>Sign Date</th>
            <th>Start Year</th>
            <th>End Year</th>
            <th>Type</th></tr>";
    foreach ($contracts as $contract) {
      $player = $contract->getPlayer();
      echo "<tr>";
      if ($isSelectable) {
        echo "<td><input type=checkbox name='trade_t" . $this->getId() . "c[]'
                         value='" . $contract->getId() . "'></td>";
      }
      echo "<td><img src='" . $player->getHeadshotUrl() . "' width=24 height=32 /></td>
                  <td>" . $player->getNameLink(true) . "</td>
                  <td>" . $player->getPositionString() . "</td>
                  <td>" . $player->getMlbTeam()->getAbbreviation() . "</td>
                  <td>" . $player->getAge() . "</td>
                  <td>" . $contract->getTotalYears() . "</td>
                  <td>" . $contract->getPrice() . "</td>
                  <td>" . $contract->getSignDate() . "</td>
                  <td>" . $contract->getStartYear() . "</td>
                  <td>" . $contract->getEndYear() . "</td>
                  <td>" . ($contract->isAuction() ? "Auction" : "Regular") . "</td></tr>";
    }
    echo "</table>";
  }

  /**
   * Display non-auction contracts for the keeper page, filtered by year.
   */
  function displayContractsForKeepers($minYear, $maxYear) {
  	echo "<h4>Contracts</h4>";

  	$contracts = $this->filterContractsByYear(
  	    ContractDao::getContractsByTeamId($this->teamId), $minYear, $maxYear, false);

  	echo "<div id='keeperdiv'";
  	if (count($contracts) == 0) {
  	  echo " style='display:none'>";
  	} else {
  	  echo ">";
  	}

  	echo "<table id='keepertable' class='center smallfonttable' border><tr>";
  	echo "  <th></th>
  	        <th>Player</th>
        	<th>Years Left</th>
  	        <th>Price</th>
  	        <th>Start Year</th>
  	        <th>End Year</th>
  	        <th>Buy Out</th>
  	        <th id='keeperRemoveColumn' style='display:none'>Remove</th></tr>";
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
  			<td>" . $contract->getEndYear() . "</td>
	        <td><input type=checkbox name='keeper_buyout[]'
	             value='" . $contract->getId() . "'></td></tr>";
	  $keeperCount++;
  	}
  	echo "</table><br/></div>";
  	echo "<input type='hidden' name='keeper_savedkeepercount' value='" . $keeperCount . "'>";
  	echo "<input type='hidden' name='keeper_newkeepercount' value='0'>";
  }

  /**
   * Filters the specified array of contracts by min/max years; if includeAuction is false, then
   * auction contracts are also filtered out.
   */
  function filterContractsByYear($contracts, $minYear, $maxYear, $includeAuction) {
  	$filteredContracts = array();
  	foreach ($contracts as $contract) {
  	  if (($contract->getEndYear() >= $minYear) && ($contract->getStartYear() <= $maxYear)
  	      && ($includeAuction || !$contract->isAuction())) {
	    $filteredContracts[] = $contract;
  	  }
  	}
  	return $filteredContracts;
  }

  function displayAllDraftPicks() {
    $currentYear = TimeUtil::getYearBasedOnStartOfSeason();
    $this->displayDraftPicks($currentYear + 1, 3000, false, "left");
  }

  /**
   * Display all draft pick information for this team.
   */
  function displayDraftPicks($minYear, $maxYear, $isSelectable, $tableClass) {
    if ((count($this->getPingPongBalls()) + count($this->getDraftPicks())) == 0) {
      return;
    }

    echo "<h4>Draft Picks</h4>";
    echo "<table class='$tableClass' border><tr>";
    if ($isSelectable) {
      echo "<th></th>";
    }
    echo "<th>Year</th><th>Round</th><th>Pick</th>
                       <th>Player</th><th>Orig Team</th></tr>";
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
      echo "<td>" . $pingPongBall->getYear() . "</td><td>Ping Pong</td>
                  <td>" . $pingPongBall->getCost() . "</td>
                  <td>" . $pingPongBall->getPlayerName() . "</td>
                  <td>--</td></tr>";
    }
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

      echo "<td>" . $draftPick->getYear() . "</td><td>" . $draftPick->getRound() . "</td>
                  <td>" . $draftPick->getPick() . "</td>
                  <td>" . $draftPick->getPlayerName() . "</td>
                  <td>" . $draftPick->getOriginalTeamName() . "</td></tr>";
    }
    echo "</table>";
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

    echo "<table id='ppballtable' class='center smallfonttable' border>
            <tr><th>Number</th><th>Price</th>
                <th id='ppRemoveColumn' style='display:none'>Remove</th></tr>";
    $ballCount = 0;
    foreach ($pingPongBalls as $pingPongBall) {
      $ballCount++;
      echo "<tr><td>" . $ballCount . "</td>
                <td>" . $pingPongBall->getCost() . "</td></tr>";
    }
    echo "</table><br/></div>";
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
    $currentYear = TimeUtil::getYearBasedOnKeeperNight();
    $this->displayBrognas($currentYear, 3000, false, 0, "left");
  }

  /**
   * Display all brogna information for this team, between the specified years. $isSelectable
   * controls whether the rows can be selected & a value can be entered [for trading].
   * $tradePosition indicates which of the two teams is trading brognas.
   */
  function displayBrognas($minYear, $maxYear, $isSelectable, $tradePosition, $tableClass) {
    if (count($this->getBrognas()) == 0) {
      return;
    }

    echo "<h4>Brognas</h4>";
    echo "<table class='$tableClass' id='brognaTable' border><tr>";
    if ($isSelectable) {
      echo "<th></th>";
    }
    echo "<th>Year</th><th>Total</th><th>Banked</th><th>Traded In</th>
            <th>Traded Out</th><th>Tradeable</th>";
    if ($isSelectable) {
      echo "<th id='headerbox" . $tradePosition . "' style='display:none'>To Trade</th>";
    }
    echo "</tr>";
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
                  <td><strong>" . $brogna->getTotalPoints() . "</strong></td>
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

  /**
   * Displays all of the players currently belonging to this team.
   */
  // TODO show cumulative rank, if exists
  function displayPlayers() {
    $players = PlayerDao::getPlayersByTeam($this);
    if (count($players) == 0) {
      return;
    }

    echo "<h4>Players</h4>";
    echo "<table class='left' border><tr>";
    echo "<th></th><th>Name</th>
          <th>Position</th>
    	  <th>Team</th>
          <th>Age</th></tr>";
    foreach ($players as $player) {
      echo "<tr><td><img src='" . $player->getHeadshotUrl() . "' width=24 height=32 /></td>
                <td>" . $player->getNameLink(true) . "</td>
                <td>" . $player->getPositionString() . "</td>
                <td>" . $player->getMlbTeam()->getAbbreviation() . "</td>
                <td>" . $player->getAge() . "</td></tr>";
    }
    echo "</table>";
  }
}
?>