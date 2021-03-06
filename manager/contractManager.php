<?php
  require_once 'commonManager.php';
  CommonManager::requireFileIn('/../dao/', 'contractDao.php');

  /**
   * Handles contract-related methods
   */
  class ContractManager {

    /**
     * Displays a drop-down of contracts currently available to be picked up.
     */
    public static function displayAvailableContracts($rowNumber) {
      $currentYear = TimeUtil::getYearByEvent(Event::RANKINGS_OPEN);
      $contracts = ContractDao::getAvailableContracts($currentYear);
      echo "<select class='input-large' id='contract_pickup" . $rowNumber . "'
                    name='contract_pickup" . $rowNumber . "' onchange='selectContract(this.value, "
                        .  $rowNumber . ")'><option value='0'>-- Select Contract --</option>";
      foreach ($contracts as $contract) {
        $player = $contract->getPlayer();
        echo "<option value='" . $contract->getId() . "'" . ">" . $player->getFullName() .
          " (" . $player->getPositionString() . ") - " . $player->getMlbTeam()->getAbbreviation() .
           "</option>";
      }
      echo "</select>";
    }

    /**
     * Displays the specified attribute of the specified contract.
     */
    public static function displayAttribute($contract, $attribute) {
      if ($contract == null) {
        echo "";
        return;
      }
      switch ($attribute) {
        case "headshot": {
          echo $contract->getPlayer()->getMiniHeadshotImg();
          break;
        }
        case "years": {
          echo $contract->getTotalYears();
          break;
        }
        case "price": {
          echo $contract->getPrice();
          break;
        }
        case "start": {
          echo $contract->getStartYear();
          break;
        }
        case "end": {
          echo $contract->getEndYear();
          break;
        }
        case "type": {
          echo $contract->getType();
          break;
        }
      }
    }

    /**
     * Returns the seltzer contract value based on the week number in the season.
     */
    public static function getMajorSeltzerContractValue($weekNumber) {
      return Contract::MINIMUM_SELTZER_CONTRACT +
          (Contract::MAJOR_SELTZER_MULTIPLIER * ($weekNumber - 1));
    }
  }

  $displayType = null;
  if (isset($_REQUEST["type"])) {
    $displayType = $_REQUEST["type"];
  }

  if ($displayType == "dropped") {
    if (isset($_REQUEST["row"])) {
      $rowNumber = $_REQUEST["row"];
    }
    ContractManager::displayAvailableContracts($rowNumber);
  } else if ($displayType == "attribute") {
    $contract = null;
    if (isset($_REQUEST["contract_id"])) {
      $contract = ContractDao::getContractById($_REQUEST["contract_id"]);
    }
  	ContractManager::displayAttribute($contract, $_REQUEST["attr"]);
  }
?>
