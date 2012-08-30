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

  private static $STPETES_IMAGE_URL_PREFIX = "http://stpetesorium.baseball.cbssports.com/images/team-logo/";
  private static $STPETES_IMAGE_URL_SUFFIX = "-72x72.jpg";

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
    return Team::$STPETES_IMAGE_URL_PREFIX . $this->sportslineImageName .
        Team::$STPETES_IMAGE_URL_SUFFIX;
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
   *
   */
  public function getBrognas() {
    if ($this->brognasLoaded != true) {
      $this->brognas = BrognaDao::getBrognasByTeamId($this->teamId);
      $this->brognasLoaded = true;
    }
    return $this->brognas;
  }

  /**
   *
   */
  public function getDraftPicks() {
    if ($this->draftPicksLoaded != true) {
      $this->draftPicks = DraftPickDao::getDraftPicksByTeamId($this->teamId);
      $this->draftPicksLoaded = true;
    }
    return $this->draftPicks;
  }

  /**
   *
   */
  public function getPingPongBalls() {
    if ($this->ballsLoaded != true) {
      $this->pingPongBalls = BallDao::getPingPongBallsByTeamId($this->teamId);
      $this->ballsLoaded = true;
    }
    return $this->pingPongBalls;
  }

  public function displayAllContracts() {
    $currentYear = TimeUtil::getCurrentSeasonYear();
    $this->displayContracts($currentYear, 3000, false);
  }

  /**
  * Display all contract information for this team.
  */
  public function displayContracts($minYear, $maxYear, $isSelectable) {
    $contracts = ContractDao::getContractsByTeamId($this->teamId);
    if (count($contracts) == 0) {
      return;
    }
    echo "<h3>Contracts</h3>";
    echo "<table border><tr>";
    if ($isSelectable) {
      echo "<th></th>";
    }
    echo "  <th>Name</th>
          	<th>Position</th>
	        <th>Team</th>
            <th>Age</th>
            <th>Years</th>
            <th>Price</th>
            <th>Sign Date</th>
            <th>Start Year</th>
            <th>End Year</th></tr>";
    foreach ($contracts as $contract) {
      if (($contract->getEndYear() < $minYear) || ($contract->getStartYear() > $maxYear)) {
        continue;
      }
      $player = $contract->getPlayer();
      echo "<tr>";
      if ($isSelectable) {
        echo "<td><input type=checkbox name='t" . $this->getId() . "c[]'
                         value='" . $contract->getId() . "'></td>";
      }
      echo "<td><a href='displayPlayer.php?player_id=" . $player->getId() . "'>" .
      $player->getFullName() . "</a></td>
                  <td>" . $player->getPositionString() . "</td>
                  <td>" . $player->getMlbTeam()->getAbbreviation() . "</td>
                  <td>" . $player->getAge() . "</td>
                  <td>" . $contract->getTotalYears() . "</td>
                  <td>" . $contract->getPrice() . "</td>
                  <td>" . $contract->getSignDate() . "</td>
                  <td>" . $contract->getStartYear() . "</td>
                  <td>" . $contract->getEndYear() . "</td></tr>";
    }
    echo "</table>";
  }

  function displayAllDraftPicks() {
    $currentYear = TimeUtil::getCurrentSeasonYear();
    $this->displayDraftPicks($currentYear, 3000, false);
  }

  /**
   * Display all draft pick information for this team.
   */
  function displayDraftPicks($minYear, $maxYear, $isSelectable) {
    if ((count($this->getPingPongBalls()) + count($this->getDraftPicks())) == 0) {
      return;
    }

    echo "<h3>Draft Picks</h3>";
    echo "<table border><tr>";
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
        echo "<td><input type=checkbox name='t" . $this->getId() . "dpb[]'
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
        echo "<td><input type=checkbox name='t" . $this->getId() . "dpp[]'
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


  function displayAllBrognas() {
    $currentYear = TimeUtil::getCurrentSeasonYear();
    $this->displayBrognas($currentYear, 3000, false, 0);
  }

  /**
  *
  */
  function displayBrognas($minYear, $maxYear, $isSelectable, $position) {
    if (count($this->getBrognas()) == 0) {
      return;
    }

    echo "<h3>Brognas</h3>";
    echo "<table border><tr>";
    if ($isSelectable) {
      echo "<th></th>";
    }
    echo "<th>Year</th><th>Total</th><th>Banked</th><th>Traded In</th>
            <th>Traded Out</th><th>Tradeable</th>";
    if ($isSelectable) {
      echo "<th id='headerbox" . $position . "' style='display:none'>To Trade</th>";
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
          echo "<td><input type=checkbox name='t" . $this->getId() . "b'
                                         onclick='toggle(" . $position . ")'
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
        echo "<td id='tradebox" . $position . "' style='display:none'>
                  <input type='text' name='t" . $this->getId() . "bv'
                         placeholder='Enter value 1 to " . $brogna->getTradeablePoints() . "'/>
              </td>";
      }
      echo "</tr>";
    }
    echo "</table>";
  }
}
?>