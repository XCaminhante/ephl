<?php
require_once('../../lib/controllers.php');
class HelloWorldController extends Controller {
    public function render() {
        include('views/helloworld.tpl');
    }
}
run('HelloWorldController');
