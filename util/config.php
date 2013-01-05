<?php

/**
 * Reads settings from the configuration file.
 */
class ConfigUtil {

  const CONFIG_FILE = 'config.ini';

  // config keys
  const VERSION = 'version';
  const ENVIRONMENT = 'environment';

  // features
  const KEEPER_FEATURE = 'keeper_feature';

  /**
   * Returns the config value with the specified key.
   */
  public static function getValue($key, $isTopLevel) {
    $configs = ConfigUtil::parseConfigFile($isTopLevel);
    return $configs[$key];
  }

  /**
   * Returns true if this is the production environment.
   */
  public static function isProduction($isTopLevel) {
    return (ConfigUtil::getValue(ConfigUtil::ENVIRONMENT, $isTopLevel) == "PROD");
  }

  /**
   * Returns true if the specified feature is enabled.
   */
  public static function isFeatureEnabled($feature, $isTopLevel) {
    return (ConfigUtil::getValue($feature, $isTopLevel) == true);
  }

  private static function parseConfigFile($isTopLevel) {
    $settings = parse_ini_file(($isTopLevel ? "" : "../") . ConfigUtil::CONFIG_FILE);
    return $settings;
  }
}

?>
