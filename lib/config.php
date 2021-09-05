<?php
//@+leo-ver=5-thin
//@+node:caminhante.20210904222845.16: * @file config.php
//@@first
//@@language php
//@@nocolor
/**
 * Manage configuration file.
 *
 * <p>Configuration variables are stored in a .php file in the format of a $config array.</p>
 *
 * @author Enisseo
 */
//@+others
//@+node:caminhante.20210905131756.1: ** Configuration
/**
 * The Configuration base class.
 */
class Configuration {
  protected $config = array();
  //@+others
  //@+node:caminhante.20210905131823.1: *3* __construct
  public function __construct($file = null) {
    if (!empty($file)) {
      $this->load($file);
    }
  }
  //@+node:caminhante.20210905131829.1: *3* load
  /**
   * @see loadConfig($file)
   */
  public function load($file) {
    include($file);
    $this->config = array_merge($this->config, $config);
  }
  //@+node:caminhante.20210905131834.1: *3* set
  /**
   * @see setConfig($key, $value)
   */
  public function set($key, $value) {
    $this->config[$key] = $value;
  }
  //@+node:caminhante.20210905131839.1: *3* get
  /**
   * @see config($key, $default)
   */
  public function get($key, $default = null) {
    return isset($this->config[$key])? $this->config[$key]: $default;
  }
  //@-others
}
//@+node:caminhante.20210905131802.1: ** config_load
function config_load($file) {
  global $_configuration;
  if (empty($_configuration)) {
    $_configuration = new Configuration();
  }
  $_configuration->load($file);
}
//@+node:caminhante.20210905131807.1: ** config_set
function config_set($key, $value) {
  global $_configuration;
  if (empty($_configuration)) user_error('No configuration file loaded with loadConfig()', E_USER_WARNING);
  $_configuration->set($key, $value);
}
//@+node:caminhante.20210905131810.1: ** config
function config($key, $default = null) {
  global $_configuration;
  if (empty($_configuration)) user_error('No configuration file loaded with loadConfig()', E_USER_WARNING);
  return $_configuration->get($key, $default);
}
//@-others
//@-leo
