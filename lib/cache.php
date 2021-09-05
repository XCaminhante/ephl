<?php
//@+leo-ver=5-thin
//@+node:caminhante.20210904222845.17: * @file cache.php
//@@first
//@@language php
//@@nocolor
/**
 * Provides simple cache management.
 *
 * @author Enisseo
 */
//@+others
//@+node:caminhante.20210905125032.1: ** Cache
/**
 * The base class for caching output.
 */
class Cache {
  protected $folders = array();
  protected $lastBlock = array();
  //@+others
  //@+node:caminhante.20210905131624.1: *3* __construct
  /**
   * Initialize the cache with the name and folder(s).
   *
   * @param string $name
   * @param string... $folders
   */
  public function __construct() {
    $args = func_get_args();
    if (count($args)) {
      $this->folders = $args;
    }
  }
  //@+node:caminhante.20210905131633.1: *3* findFile
  protected function findFile($name) {
    foreach ($this->folders as $folder) {
      $file = $folder . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $name)
        . (strpos($name, '.') !== false? '': '.cache');
      if (file_exists($file)) {
        return $file;
      }
    }
    return false;
  }
  //@+node:caminhante.20210905131642.1: *3* saveFile
  protected function saveFile($name, $cacheData) {
    $file = $this->folders[count($this->folders) - 1] . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $name) . (strpos($name, '.') !== false? '': '.cache');
    @mkdir(dirname($file), 0777, true);
    file_put_contents($file, $cacheData);
  }
  //@+node:caminhante.20210905131648.1: *3* addFolders
  /**
   * Adds folders to look for cache files.
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
  //@+node:caminhante.20210905131654.1: *3* addFolder
  /**
   * Adds one folder to look for cache files.
   *
   * <p>Folders are looked from the last one to the first one, in order to implements "overwriting".</p>
   */
  public function addFolder($folder) {
    array_unshift($this->folders, $folder);
  }
  //@+node:caminhante.20210905131701.1: *3* blockStart
  /**
   * Defines the beginning of a cache block.
   */
  public function blockStart($name) {
    ob_start();
    array_push($this->lastBlock, $name);
  }
  //@+node:caminhante.20210905131707.1: *3* blockEnd
  /**
   * Defines the end of a cache block.
   */
  public function blockEnd($name = null) {
    $blockContents = ob_get_clean();
    if (empty($name)) {
      $name = array_pop($this->lastBlock);
    }
    $this->saveFile($name, $blockContents);
    print($blockContents);
  }
  //@+node:caminhante.20210905131712.1: *3* load
  /**
   * Loads the cache file with the given name.
   */
  public function load($name, $ttl = 0) {
    if ($file = $this->findFile($name)) {
      $mtime = filemtime($file);
      if ((time() - $mtime) <= $ttl) {
        include($file);
        return true;
      }
    }
    return false;
  }
  //@+node:caminhante.20210905131718.1: *3* notCached
  /**
   * Makes a "load or start" behavior, returning true if not cached.
   */
  public function notCached($name, $ttl = 0) {
    if (!$this->load($name, $ttl)) {
      $this->blockStart($name);
      return true;
    }
    return false;
  }
  //@-others
}
//@+node:caminhante.20210905125050.1: ** cache_folders
function cache_folders() {
  global $_cache;
  if (empty($_cache)) {
    $_cache = new Cache();
  }
  $_cache->addFolders(func_get_args());
}
//@+node:caminhante.20210905125054.1: ** cache_folder
function cache_folder($folder) {
  global $_cache;
  if (empty($_cache)) {
    $_cache = new Cache();
  }
  $_cache->addFolder($folder);
}
//@+node:caminhante.20210905125100.1: ** cache
function cache($name, $ttl = 0) {
  if (!cache_load($name, $ttl)) {
    cache_start($name);
    return false;
  }
  return true;
}
//@+node:caminhante.20210905125106.1: ** cache_load
function cache_load($name, $ttl = 0) {
  global $_cache;
  if (empty($_cache)) user_error('No cache folder specified with cache_folder[s]()', E_USER_WARNING);
  return $_cache->load($name, $ttl);
}
//@+node:caminhante.20210905125110.1: ** cache_start
function cache_start($name) {
  global $_cache;
  if (empty($_cache)) user_error('No cache folder specified with cache_folder[s]()', E_USER_WARNING);
  $_cache->blockStart($name);
}
//@+node:caminhante.20210905125113.1: ** cache_end
function cache_end($name = null) {
  global $_cache;
  if (empty($_cache)) user_error('No cache folder specified with cache_folder[s]()', E_USER_WARNING);
  $_cache->blockEnd($name);
}
//@-others
//@-leo
