<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'changelogDao.php');
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');

/**
 * Represents a contract transaction [pickup, drop, seltzer].
 */
class ContractScenario {
  private $team;
  private $droppedContracts;
  private $pickedUpContracts;
  private $seltzerContracts;

  public function parseContractsFromPost() {
    $this->team = TeamDao::getTeamById($_POST['contract_teamid']);
    $this->droppedContracts = $this->parseDroppedContracts($_POST, true);
    $this->pickedUpContracts = $this->parsePickedUpContracts($_POST, true);

    // TODO parse seltzer contracts

    SessionUtil::updateSession('contract_teamid', $_POST, true);
  }

  public function parseContractsFromSession() {
    $this->team = TeamDao::getTeamById($_SESSION['contract_teamid']);
    $this->droppedContracts = $this->parseDroppedContracts($_SESSION, false);
    $this->pickedUpContracts = $this->parsePickedUpContracts($_SESSION, false);

    // TODO parse seltzer contracts

    SessionUtil::updateSession('contract_teamid', $_SESSION, false);
  }

  private function parseDroppedContracts($assocArray, $isPost) {
    $dropString = 'contract_drop';
    $contractsToDrop = array();
    if (isset($assocArray[$dropString]) && is_array($assocArray[$dropString])) {
      foreach ($assocArray[$dropString] as $contractId) {
        $contractsToDrop[] = ContractDao::getContractById($contractId);
      }
      SessionUtil::updateSession($dropString, $assocArray, $isPost);
    }
    return $contractsToDrop;
  }

  private function parsePickedUpContracts($assocArray, $isPost) {
    $savedContractString = 'contract_savedcontractcount';
    $newContractString = 'contract_newcontractcount';
    $contractSavedCount = $assocArray[$savedContractString];
    $contractNewCount = $assocArray[$newContractString];

    $pickedUpContracts = array();
    for ($i = ($contractSavedCount + 1); $i <= ($contractSavedCount + $contractNewCount); $i++) {
      $contractKey = 'contract_pickup' . $i;
      $pickedUpContracts[] = ContractDao::getContractById($assocArray[$contractKey]);
      SessionUtil::updateSession($contractKey, $assocArray, $isPost);
    }

    SessionUtil::updateSession($savedContractString, $assocArray, $isPost);
    SessionUtil::updateSession($newContractString, $assocArray, $isPost);
    return $pickedUpContracts;
  }

  public function showContractSummary() {
    echo "<h4>Contract Summary for " . $this->team->getName() . "</h4><hr class='bothr'/>";

    // Team info
    echo "<div class='row-fluid'>
    <div class='span4 center'>";
    echo "<br/>" . $this->team->getSportslineImg(72);
    echo "<br/><p class='ptop'>" . $this->team->getOwnersString() . "</p>";
    echo "  </div>"; // span4
    echo "  <div class='span8 center'>";

    // display dropped contracts
    if ($this->droppedContracts) {
      echo "<h4>Contract(s) to Drop:</h4>";
      foreach ($this->droppedContracts as $contract) {
        echo $contract->getDetails() . "<br/>";
      }
    }

    // display picked up contracts
    if ($this->pickedUpContracts) {
      echo "<h4>Contract(s) to Pick Up:</h4>";
      foreach ($this->pickedUpContracts as $contract) {
        echo $contract->getDetails() . "<br/>";
      }
    }
    echo "<input type='hidden' name='team_id' value='" . $this->team->getId() . "'>";

    echo "<br/></div>"; // span8
    echo "</div>"; // row-fluid
  }

  public function validateContracts() {
    // confirm contracts were selected
    if (!$this->droppedContracts && !$this->pickedUpContracts) {
      $this->printError("No contracts selected for pickup/drop!");
      return false;
    }

    // confirm contract with id = 0 wasn't selected and same contract wasn't added twice
    if ($this->pickedUpContracts) {
      $contractIds = array();
      foreach ($this->pickedUpContracts as $contract) {
        if ($contract == null) {
          $this->printError("Invalid contract to be picked up!");
          return false;
        } else if (in_array($contract->getId(), $contractIds)) {
          $this->printError("Cannot pickup same contract twice: " .
              $contract->getPlayer()->getFullName());
          return false;
        } else {
          // add contract id to array
          $contractIds[] = $contract->getId();
        }
      }
    }
    return true;
  }

  public function initiateTransaction() {
    echo "<h4>Contracts Updated for " . $this->team->getName() . "</h4><hr class='bothr'/>";

    // Team info
    echo "<div class='row-fluid'>
             <div class='span4 center'>";
    echo "<br/>" . $this->team->getSportslineImg(72);
    echo "<br/><p class='ptop'>" . $this->team->getOwnersString() . "</p>";
    echo "  </div>"; // span4

    echo "  <div class='span8 center'>
               <h4>Summary</h4>";
    $timestamp = TimeUtil::getTimestampString();

    // drop contracts
    if ($this->droppedContracts) {
      foreach ($this->droppedContracts as $contract) {
        // drop player from team, but leave contract's team alone
        TeamDao::dropPlayer($contract->getPlayer());
        echo "<strong>Dropped:</strong> " . $contract->getDetails() . "<br/>";

        // update changelog
        ChangelogDao::createChange(new Changelog(-1, Changelog::CONTRACT_DROP_TYPE,
            SessionUtil::getLoggedInUser()->getId(), $timestamp, $contract->getId(),
            $this->team->getId(), null));
      }
    }

    // pick up contracts
    if ($this->pickedUpContracts) {
      foreach ($this->pickedUpContracts as $contract) {
        // set new team on contract
        $oldTeam = $contract->getTeam();
        $contract->setTeam($this->team);
        ContractDao::updateContract($contract);

        // assign player to team
        TeamDao::assignPlayerToTeam($contract->getPlayer(), $this->team->getId());
        echo "<strong>Picked up:</strong> " . $contract->getDetails() . "<br/>";

        // update changelog
        ChangelogDao::createChange(new Changelog(-1, Changelog::CONTRACT_PICKUP_TYPE,
            SessionUtil::getLoggedInUser()->getId(), $timestamp, $contract->getId(),
            $this->team->getId(), $oldTeam->getId()));
      }
    }
    echo "<input type='hidden' name='team_id' value='" . $this->team->getId() . "'>";

    echo "<br/></div>"; // span8
    echo "</div>"; // row-fluid
  }

  private function printError($errorMsg) {
    echo "<br/><div class='alert alert-error'><strong>Error: </strong>" . $errorMsg . "</div>";
  }
}

?>