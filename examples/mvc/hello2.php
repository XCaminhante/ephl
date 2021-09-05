<?php
require_once('../../lib/controllers.php');
class HelloWorldController extends Controller {
  public function render() {
    print('Hello, world!');
  }
}
run('HelloWorldController');
