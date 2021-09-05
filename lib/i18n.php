<?php
//@+leo-ver=5-thin
//@+node:caminhante.20210904222845.8: * @file i18n.php
//@@first
//@@language php
//@@nocolor
/**
 * Manage internationalization.
 *
 * <p>Use PHP files with a $tr array containing key => translations.</p>
 *
 * @author Enisseo
 */
//@+others
//@+node:caminhante.20210905135259.1: ** Dictionary
/**
 * A "Dictionary" class.
 */
class Dictionary {
  protected $tr = array();
  //@+others
  //@+node:caminhante.20210905135327.1: *3* tr
  /**
   * Translates a string with optional parameters.
   *
   * @param string $message
   * @param array $params the parameters, with keys as names and string as values
   * @return string the translated message if found
   *
   * @see tr($message, $params = array())
   */
  public function tr($message, $params = array()) {
    $_translatedMessage = $message;
    foreach ($this->tr as $file => $tr) {
      if (isset($tr[$message])) {
        $_translatedMessage = $tr[$message];
      }
    }
    if (!empty($params)) {
      $_translatedMessage = call_user_func_array('sprintf', array_merge(array($_translatedMessage), array_values($params)));
      if (preg_match('/<\?/', $_translatedMessage)) {
        foreach ($params as $key => $value) {
          $$key = $value;
        }
        $_translatedMessage = preg_replace('/<\?=(.*?)\?>/', '<? $_translatedMessage .= \1; ?>', $_translatedMessage);
        $_translatedMessage = preg_replace('/(:?)\s*\?>(.*?)<\?/', '\1 $_translatedMessage .= str_replace(\'\\\'\', \'\\\\\'\', \'\2\');', $_translatedMessage);
        $_translatedMessage = preg_replace('/;?\s*\?>(.*?)<\?/', '; $_translatedMessage .= str_replace(\'\\\'\', \'\\\\\'\', \'\1\');', $_translatedMessage);
        $_translatedMessage = preg_replace('/^(.*?)<\?/', '$_translatedMessage .= str_replace(\'\\\'\', \'\\\\\'\', \'\1\');', $_translatedMessage);
        $_translatedMessage = preg_replace('/\?>(.*?)$/', '$_translatedMessage .= str_replace(\'\\\'\', \'\\\\\'\', \'\1\');', $_translatedMessage);
        $_translatedMessage = '$_translatedMessage = \'\'; ' . $_translatedMessage . ' return $_translatedMessage;';
        $_translatedMessage = eval($_translatedMessage);
      }
    }
    return $_translatedMessage;
  }
  //@+node:caminhante.20210905135346.1: *3* rt
  /**
   * Tries to find the key used to translate a message.
   *
   * @return string the key or null if not found
   */
  public function rt($message) {
    $_originalMessage = null;
    foreach ($this->tr as $file => $tr) {
      $key = array_search($message, $tr);
      if ($key !== false) {
        $_originalMessage = $key;
      }
    }
    return $_originalMessage;
  }
  //@+node:caminhante.20210905135354.1: *3* push
  /**
   * Adds a dictionary file to the list of translations.
   *
   * @param string $file
   *
   * @see pushDictionary($file)
   */
  public function push($file) {
    if (file_exists($file)) {
      include($file);
      $this->tr[$file] = empty($tr)? array(): $tr;
    }
  }
  //@+node:caminhante.20210905135401.1: *3* pop
  /**
   * Removes a dictionary or the last one loaded.
   *
   * @param string $file
   *
   * @see popDictionary($file)
   */
  public function pop($file = null) {
    if (!empty($file)) {
      unset($this->tr[$file]);
    }
    else {
      array_pop($this->tr);
    }
  }
  //@+node:caminhante.20210905135407.1: *3* generate
  /**
   * Generates a dictionary by search translatable strings in files within a folder.
   */
  public function generate($folder, $exts = 'php,js,tpl,html') {
    $tr = array();
    $extensions = preg_split('/,/', $exts);
    $folders = array($folder);
    while (count($folders)) {
      $folderPath = array_shift($folders);
      foreach (scandir($folderPath) as $file) {
        if ($file && $file[0] != '.') {
          $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
          if (is_dir($filePath)) {
            array_push($folders, $filePath);
          }
          else {
            if (in_array(strtolower(pathinfo($filePath, PATHINFO_EXTENSION)), $extensions)) {
              $content = file_get_contents($filePath);
              $trFile = array();
              $results = array();
              if (preg_match_all('/\Wtr\(([\'\"])((\\\.|[^\\1])+?)\\1/ims', $content, $results)) {
                foreach ($results[2] as $message) {
                  $message = preg_replace('/\\\(.)/', '\1', $message);
                  $trFile[$message] = $message;
                }
              }
              if (count($trFile)) {
                $tr[$filePath] = $trFile;
              }
            }
          }
        }
      }
    }
    return $tr;
  }
  //@-others
}
//@+node:caminhante.20210905135302.1: ** tr
function tr($message, $params = array()) {
  global $_dictionary;
  if (empty($_dictionary)) {
    $_dictionary = new Dictionary();
  }
  return $_dictionary->tr($message, $params);
}
//@+node:caminhante.20210905135304.1: ** rt
function rt($message) {
  global $_dictionary;
  if (empty($_dictionary)) {
    $_dictionary = new Dictionary();
  }
  return $_dictionary->rt($message);
}
//@+node:caminhante.20210905135306.1: ** dictionary_push
function dictionary_push($file) {
  global $_dictionary;
  if (empty($_dictionary)) {
    $_dictionary = new Dictionary();
  }
  $_dictionary->push($file);
}
//@+node:caminhante.20210905135308.1: ** dictionary_pop
function dictionary_pop($file = null) {
  global $_dictionary;
  if (empty($_dictionary)) {
    $_dictionary = new Dictionary();
  }
  $_dictionary->pop($file);
}
//@-others
//@-leo
