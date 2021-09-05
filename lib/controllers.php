<?php
//@+leo-ver=5-thin
//@+node:caminhante.20210904222845.15: * @file controllers.php
//@@first
//@@language php
//@@nocolor
/**
 * Provide the base for a clean code for PHP apps.
 *
 * @author Enisseo
 */
require_once('functions.php');
//@+others
//@+node:caminhante.20210905131310.1: ** Controller
/**
 * A class for each controller/page of your application.
 *
 * <p>You can either implements it directly or write an intermediate controller
 * with some specific code for your application (SQL connections, user-based functions...).</p>
 */
abstract class Controller {
  /**
   * Data received with the request, as multi-arrays.
   *
   * <p>This array contains several arrays with keys "get", "post", "cookie"... used
   * to get a HTTP var.</p>
   *
   * @var array the request data.
   */
  protected $request;
  //@+others
  //@+node:caminhante.20210905131933.1: *3* __construct
  /**
   * Initialize the controller with HTTP vars.
   */
  public function __construct() {
    $this->request = array(
      'post' => $_POST,
      'get' => $_GET,
      'cookie' => $_COOKIE,
      'server' => $_SERVER,
    );
    // do not miss parameters from a URL rewriting operation
    if ($this->server('REQUEST_URI')) {
      $queryArgs = parse_url($this->server('REQUEST_URI'), PHP_URL_QUERY);
      if (!empty($queryArgs)) {
        $missedArgs = array();
        parse_str($queryArgs, $missedArgs);
        $this->request['get'] = array_merge($missedArgs, $this->request['get']);
        $_GET = $this->request['get'];
      }
    }
    if (get_magic_quotes_gpc()) {
      array_walk_recursive($this->request, array($this, 'unquote'));
    }
  }
  //@+node:caminhante.20210905131945.1: *3* unquote
  public function unquote(&$item, $key) {
    $item = stripslashes($item);
  }
  //@+node:caminhante.20210905132006.1: *3* post
  /**
   * Returns the value of a variable in the posted data.
   *
   * @param string $name the name of the variable.
   * @param mixed $default the default value if not found in the posted data, null otherwise.
   */
  protected function post($name, $default = null) {
    return array_get($this->request['post'], $name, $default);
  }
  //@+node:caminhante.20210905132012.1: *3* get
  /**
   * Returns the value of a variable in the URL.
   *
   * @param string $name the name of the variable.
   * @param mixed $default the default value if not found in the URL, null otherwise.
   */
  protected function get($name, $default = null) {
    return array_get($this->request['get'], $name, $default);
  }
  //@+node:caminhante.20210905132017.1: *3* server
  /**
   * Returns the value of a variable passed by the client/from the server.
   *
   * @param string $name the name of the variable.
   * @param mixed $default the default value if not found, null otherwise.
   */
  protected function server($name, $default = null) {
    return array_get($this->request['server'], $name, $default);
  }
  //@+node:caminhante.20210905132024.1: *3* cookie
  /**
   * Returns the value of a cookie.
   *
   * @param string $name the name of the variable.
   * @param mixed $default the default value if not found in the cookie, null otherwise.
   */
  protected function cookie($name, $default = null) {
    return array_get($this->request['cookie'], $name, $default);
  }
  //@+node:caminhante.20210905132030.1: *3* redirect
  /**
   * Immediately redirects to a certain URL.
   *
   * @param string $url
   */
  protected function redirect($url) {
    header('Location: ' . $url);
    $this->leave();
    exit();
  }
  //@+node:caminhante.20210905132036.1: *3* run
  /**
   * Run the controller.
   *
   * <p>This simply calls the check(), act(), prepare() and render() methods successively. Overwrite when needed.</p>
   */
  public function run() {
    $this->check();
    $this->act();
    $this->prepare();
    $this->render();
    $this->leave();
  }
  //@+node:caminhante.20210905132041.1: *3* check
  /**
   * Check statuses/rights/others before doing anything.
   *
   * <p>Verify some pre-conditions, initialize some data: SQL connection, user authenticated?...</p>
   */
  public function check() {
  }
  //@+node:caminhante.20210905132046.1: *3* act
  /**
   * Do some things before rendering.
   *
   * <p>Write down any behavior you page has: form processing, etc.</p>
   */
  public function act() {
  }
  //@+node:caminhante.20210905132053.1: *3* prepare
  /**
   * Prepare some data to be rendered (arrays, etc.).
   *
   * <p>Use this function to set and build some attributes used by the render() method.</p>
   */
  public function prepare() {
  }
  //@+node:caminhante.20210905132104.1: *3* render
  /**
   * Render the page.
   */
  public function render() {
  }
  //@+node:caminhante.20210905132109.1: *3* leave
  /**
   * Execute last commands.
   */
  public function leave() {
  }
  //@-others
}
//@+node:caminhante.20210905132223.1: ** ControllerExtension
/**
 * A class for adding grouped features to any controller.
 *
 * <p>Use for example $this->myExt = new MyControllerExtension($this); in a controller to add the extension.</p>
 */
abstract class ControllerExtension extends Controller {
  protected $parent = null;
  /**
   * Initializes the extension with a parent controller.
   *
   * @param Controller $controller
   */
  public function __construct(&$controller) {
    $this->parent =& $controller;
    $this->request =& $controller->request;
  }
}
//@+node:caminhante.20210905132230.1: ** run
/**
 * Runs a controller.
 *
 * @param $controller Controller|string a Controller or Controller class.
 */
function run($controller) {
  if (is_string($controller)) {
    $controller = new $controller();
  }
  $controller->run();
}
//@+node:caminhante.20210905132238.1: ** controller_runlast
/**
 * Runs the last controller loaded.
 *
 * <p>Calls this function at the end of each page to run the controller defined in the page.
 * Register as shutdown_function if needed.</p>
 */
function controller_runlast() {
  $classes = get_declared_classes();
  for ($c = count($classes) - 1; $c >= 0; $c--) {
    $class = $classes[$c];
    $parentClass = $class;
    while ($parentClass = get_parent_class($parentClass)) {
      if (strtolower($parentClass) == 'controller') {
        $controller = new $class();
        $controller->run();
        return;
      }
    }
  }
}
//@-others
//@-leo
