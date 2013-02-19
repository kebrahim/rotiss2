<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'changelogDao.php');
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');
CommonEntity::requireFileIn('/../util/', 'mail.php');

/**
 * Represents a contract transaction [pickup, drop, seltzer].
 */
class ContractScenario {
  private $team;
  private $droppedContracts;
  private $pickedUpContracts;
  private $seltzerContract;

  public function getTeamId() {
    if ($this->team != null) {
      return $this->team->getId();
    }
    return 0;
  }

  public function parseContractsFromPost() {
    $this->parseContractsFromArray($_POST, true);
  }

  public function parseContractsFromSession() {
    $this->parseContractsFromArray($_SESSION, false);
  }

  private function parseContractsFromArray($assocArray, $isPost) {
    if (array_key_exists("contract_savedcontractcount", $assocArray)) {
      $this->team = TeamDao::getTeamById($assocArray['contract_teamid']);
      $this->droppedContracts = $this->parseDroppedContracts($assocArray, $isPost);
      $this->pickedUpContracts = $this->parsePickedUpContracts($assocArray, $isPost);
      SessionUtil::updateSession('contract_teamid', $assocArray, $isPost);
    }

    if (array_key_exists("seltzer_player", $assocArray)) {
      $this->team = TeamDao::getTeamById($assocArray['seltzer_teamid']);
      $this->seltzerContract = $this->parseSeltzerContract($assocArray, $isPost);
      SessionUtil::updateSession('seltzer_teamid', $assocArray, $isPost);
    }
  }

  private function parseSeltzerContract($assocArray, $isPost) {
    $playerKey = 'seltzer_player';
    $typeKey = 'seltzer_type';
    $yearKey = 'seltzer_length';
    $priceKey = 'seltzer_price';
    $minorPriceKey = 'seltzer_minor_price';

    $contractType = null;
    $price = null;
    if (intval($assocArray[$typeKey]) == 1) {
      $contractType = Contract::SELTZER_TYPE;
      $price = $assocArray[$priceKey];
    } else if (intval($assocArray[$typeKey]) == 2) {
      $contractType = Contract::MINOR_SELTZER_TYPE;
      $price = $assocArray[$minorPriceKey];
    }
    $numYears = intval($assocArray[$yearKey]);
    $startYear = TimeUtil::getCurrentYear() + 1;
    $seltzerContract = new Contract(-1, $assocArray[$playerKey], $this->team->getId(), $numYears,
        $price, TimeUtil::getTodayString(), $startYear, ($startYear + $numYears) - 1, false,
        $contractType);

    SessionUtil::updateSession($playerKey, $assocArray, $isPost);
    SessionUtil::updateSession($typeKey, $assocArray, $isPost);
    SessionUtil::updateSession($yearKey, $assocArray, $isPost);
    SessionUtil::updateSession($priceKey, $assocArray, $isPost);
    SessionUtil::updateSession($minorPriceKey, $assocArray, $isPost);

    return $seltzerContract;
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
    if (array_key_exists($savedContractString, $assocArray)) {
      $contractSavedCount = $assocArray[$savedContractString];
    } else {
      $contractSavedCount = 0;
    }
    if (array_key_exists($newContractString, $assocArray)) {
      $contractNewCount = $assocArray[$newContractString];
    } else {
      $contractNewCount = 0;
    }

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

    // display seltzer contract
    if ($this->seltzerContract) {
      echo "<h4>Seltzer Contract:</h4>";
      echo $this->seltzerContract->getDetails() . "<br/>";
    }

    echo "<input type='hidden' name='team_id' value='" . $this->team->getId() . "'>";

    echo "<br/></div>"; // span8
    echo "</div>"; // row-fluid
  }

  public function validateContracts() {
    // confirm contracts were selected
    if (!$this->droppedContracts && !$this->pickedUpContracts && !$this->seltzerContract) {
      $this->printError("No contracts selected!");
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

    // validate seltzer contract
    if ($this->seltzerContract) {
      if ($this->seltzerContract->getPlayer() == null) {
        $this->printError("Invalid player id for contract: " .
            $this->seltzerContract->getPlayerId());
	  	return false;
      } else if ($this->seltzerContract->getType() == null) {
        $this->printError("Invalid type of seltzer contract");
        return false;
	  } else if (!is_numeric($this->seltzerContract->getTotalYears())
	      || $this->seltzerContract->getTotalYears() < 1
	      || $this->seltzerContract->getTotalYears() > 2) {
        $this->printError("Invalid length of contract: " . $this->seltzerContract->getTotalYears() .
            " years");
	  	return false;
	  } else if (!is_numeric($this->seltzerContract->getPrice()) ||
	      ($this->seltzerContract->getType() == Contract::SELTZER_TYPE &&
	       $this->seltzerContract->getPrice() < Contract::MINIMUM_SELTZER_CONTRACT) ||
	  	  ($this->seltzerContract->getType() == Contract::MINOR_SELTZER_TYPE &&
	  	   $this->seltzerContract->getPrice() < Contract::UNCALLED_MINOR_CONTRACT)) {
	    $this->printError("Invalid price for " . $this->seltzerContract->getType() .
	        " contract: $" . $this->seltzerContract->getPrice());
	  	return false;
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
    $changes = array();

    // drop contracts
    if ($this->droppedContracts) {
      foreach ($this->droppedContracts as $contract) {
        // drop player from team, but leave contract's team alone
        TeamDao::dropPlayer($contract->getPlayer());
        echo "<strong>Dropped:</strong> " . $contract->getDetails() . "<br/>";

        // update changelog
        $changes[] = ChangelogDao::createChange(new Changelog(-1, Changelog::CONTRACT_DROP_TYPE,
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
        $changes[] = ChangelogDao::createChange(new Changelog(-1, Changelog::CONTRACT_PICKUP_TYPE,
            SessionUtil::getLoggedInUser()->getId(), $timestamp, $contract->getId(),
            $this->team->getId(), $oldTeam->getId()));
      }
    }

    // sign seltzer contract
    if ($this->seltzerContract) {
      if (ContractDao::createContract($this->seltzerContract) != null) {
        echo "<strong>Signed:</strong> " . $this->seltzerContract->getDetails() . "<br/>";

        // update changelog
        $changes[] = ChangelogDao::createChange(new Changelog(-1, Changelog::CONTRACT_SIGNED_TYPE,
            SessionUtil::getLoggedInUser()->getId(), $timestamp, $this->seltzerContract->getId(),
            $this->team->getId(), null));
      }
    }

    // send email with all changes
    MailUtil::sendChangesEmailToTeam($changes, $this->team);

    echo "<input type='hidden' name='team_id' value='" . $this->team->getId() . "'>";

    echo "<br/></div>"; // span8
    echo "</div>"; // row-fluid
  }

  private function printError($errorMsg) {
    echo "<br/><div class='alert alert-error'><strong>Error: </strong>" . $errorMsg . "</div>";
  }
}

?>