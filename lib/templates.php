<?php
//@+leo-ver=5-thin
//@+node:caminhante.20210904222845.1: * @file templates.php
//@@first
//@@language php
//@@nocolor
/**
 * Provides simple template management.
 *
 * @author Enisseo
 */
//@+others
//@+node:caminhante.20210905142345.1: ** Template
/**
 * The base class for templates.
 */
class Template {
  protected $file = null;
  protected $blocks = array();
  protected $folders = array();
  protected $inherits = null;
  protected $inheritsVars = array();
  protected $lastBlock = array();
  //@+others
  //@+node:caminhante.20210905142418.1: *3* __construct
  /**
   * Initialize the template with the file and folder(s).
   *
   * @param string $file
   * @param string... $folders
   */
  public function __construct() {
    $args = func_get_args();
    if (count($args)) {
      $this->folders = $args;
    }
  }
  //@+node:caminhante.20210905142423.1: *3* findFile
  protected function findFile($name) {
    foreach ($this->folders as $folder) {
      $file = $folder . DIRECTORY_SEPARATOR . $name . (strpos($name, '.') !== false? '': '.php');
      if (file_exists($file)) {
        return $file;
      }
    }
    user_error('No template found for "' . $name . '"', E_USER_WARNING);
    return false;
  }
  //@+node:caminhante.20210905142429.1: *3* addFolders
  /**
   * Adds folders to look for templates.
   *
   * <p>Folders are looked from the last one to the first one, in order to implements "overwriting".</p>
   *
   * @param string... $folders
   */
  public function addFolders() {
    $args = func_get_args();
    foreach ($args as $folder) {
      $this->addFolder($folder);
    }
  }
  //@+node:caminhante.20210905142435.1: *3* addFolder
  /**
   * Adds one folder to look for templates.
   *
   * <p>Folders are looked from the last one to the first one, in order to implements "overwriting".</p>
   */
  public function addFolder($folder) {
    array_unshift($this->folders, $folder);
  }
  //@+node:caminhante.20210905142443.1: *3* inherits
  /**
   * Indicates that the template inherits a bigger one and defines blocks.
   *
   * @param string $file the name of the file, with or without the .php extension.
   * @param array $vars additional vars to the inherited template.
   */
  public function inherits($file, $vars = array()) {
    $this->inherits = $file;
    $this->inheritsVars = $vars;
  }
  //@+node:caminhante.20210905142448.1: *3* render
  /**
   * Renders the template.
   */
  public function render($_name = null, $_vars = array()) {
    if (!empty($_name)) {
      foreach ($_vars as $key => $value) {
        $$key = $value;
      }
      include($this->findFile($_name));
    }
    if (!empty($this->inherits)) {
      foreach ($this->inheritsVars as $key => $value) {
        $$key = $value;
      }
      include($this->findFile($this->inherits));
    }
  }
  //@+node:caminhante.20210905142453.1: *3* insert
  /**
   * Inserts a template file with parameters.
   */
  public function insert($_file, $_vars = array()) {
    foreach ($_vars as $key => $value) {
      $$key = $value;
    }
    include($this->findFile($_file));
  }
  //@+node:caminhante.20210905142509.1: *3* blockStart
  /**
   * Defines the beginning of a block.
   */
  public function blockStart($name) {
    ob_start();
    array_push($this->lastBlock, $name);
  }
  //@+node:caminhante.20210905142515.1: *3* blockEnd
  /**
   * Defines the end of a block.
   */
  public function blockEnd($name = null) {
    $blockContents = ob_get_clean();
    if (empty($name)) {
      $name = array_pop($this->lastBlock);
    }
    $this->blocks[$name] = $blockContents;
  }
  //@+node:caminhante.20210905142520.1: *3* block
  /**
   * Prints a defined block.
   */
  public function block($name) {
    print($this->blocks[$name]);
  }
  //@-others
}
//@+node:caminhante.20210905142348.1: ** template_folders
function template_folders() {
  global $_template;
  if (empty($_template)) {
    $_template = new Template();
  }
  $_template->addFolders(func_get_args());
}
//@+node:caminhante.20210905142354.1: ** template_folder
function template_folder($folder) {
  global $_template;
  if (empty($_template)) {
    $_template = new Template();
  }
  $_template->addFolder($folder);
}
//@+node:caminhante.20210905142356.1: ** template_inherits
function template_inherits($file, $vars = array()) {
  global $_template;
  if (empty($_template)) user_error('No template folder specified with template_folder[s]()', E_USER_WARNING);
  $_template->inherits($file, $vars);
  register_shutdown_function('template_render');
}
//@+node:caminhante.20210905142358.1: ** template_include
function template_include($fileName, $vars = array()) {
  global $_template;
  if (empty($_template)) user_error('No template folder specified with template_folder[s]()', E_USER_WARNING);
  $_template->insert($fileName, $vars);
}
//@+node:caminhante.20210905142400.1: ** template
function template($blockName) {
  global $_template;
  if (empty($_template)) {
    $_template = new Template();
  }
  $_template->block($blockName);
}
//@+node:caminhante.20210905142402.1: ** template_start
function template_start($blockName) {
  global $_template;
  if (empty($_template)) {
    $_template = new Template();
  }
  $_template->blockStart($blockName);
}
//@+node:caminhante.20210905142404.1: ** template_end
function template_end($blockName = null) {
  global $_template;
  if (empty($_template)) {
    $_template = new Template();
  }
  $_template->blockEnd($blockName);
}
//@+node:caminhante.20210905142406.1: ** template_render
function template_render() {
  global $_template;
  if (empty($_template)) {
    $_template = new Template();
  }
  $_template->render();
}
//@-others
//@-leo
