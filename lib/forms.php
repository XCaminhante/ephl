<?php
//@+leo-ver=5-thin
//@+node:caminhante.20210904222845.13: * @file forms.php
//@@first
//@@language php
//@@nocolor
require_once('functions.php');
/**
 * Provide an abstraction for form generation.
 *
 * @author Enisseo
 */
//@+others
//@+node:caminhante.20210905132829.1: ** Form
/**
 * A form, with components and actions.
 */
abstract class Form {
  public $method = 'post';
  public $action = '';
  public $enctype = 'application/x-www-form-urlencoded';
  public $components = array();
  public $actions = array();
  //@+others
  //@+node:caminhante.20210905132843.1: *3* __construct
  /**
   * Initializes a form with a set of params.
   */
  public function __construct($params = array()) {
    $this->set($params);
  }
  //@+node:caminhante.20210905132849.1: *3* set
  public function set($params = array()) {
    foreach ($params as $key => $value) {
      $this->$key = $value;
    }
  }
  //@+node:caminhante.20210905132859.1: *3* addComponent
  public function addComponent(FormComponent $component) {
    $this->components[] = $component;
    $component->setForm($this);
    return $this;
  }
  //@+node:caminhante.20210905132906.1: *3* addComponents
  public function addComponents($components) {
    foreach ($components as $component) {
      $this->addComponent($component);
    }
    return $this;
  }
  //@+node:caminhante.20210905132915.1: *3* getComponentByName
  public function getComponentByName($name) {
    foreach ($this->components as $component) {
      if ($component instanceof FormGroup) {
        $foundComponent = $component->getComponentByName($name);
        if (!empty($foundComponent)) {
          return $foundComponent;
        }
      }
      elseif ($component instanceof FormField) {
        if ($component->name == $name) {
          return $component;
        }
      }
    }
    return null;
  }
  //@+node:caminhante.20210905132921.1: *3* addAction
  public function addAction(FormAction $action) {
    $this->actions[$action->getName()] = $action;
    return $this;
  }
  //@+node:caminhante.20210905132926.1: *3* addActions
  public function addActions($actions) {
    $this->actions = array_merge($this->actions, $actions);
    return $this;
  }
  //@+node:caminhante.20210905132934.1: *3* isTriggered
  /**
   * Indicates if a form action has been triggered.
   *
   * @return string the name of the action triggered, false otherwise.
   */
  public function isTriggered(&$data) {
    foreach ($this->actions as $action) {
      if ($action->isTriggered($data)) {
        return $action->getName();
      }
    }
    return false;
  }
  //@+node:caminhante.20210905132942.1: *3* isTriggeredWithRequest
  public function isTriggeredWithRequest() {
    if (strtolower($this->method) == 'post') {
      return $this->isTriggered($_POST);
    }
    else {
      return $this->isTriggered($_GET);
    }
  }
  //@+node:caminhante.20210905132950.1: *3* populate
  /**
   * Populates the fields/components with the given data.
   *
   * @param $data array
   */
  public function populate(&$data) {
    foreach ($this->components as $component) {
      $component->populate($data);
    }
  }
  //@+node:caminhante.20210905132959.1: *3* populateWithRequest
  public function populateWithRequest() {
    if (strtolower($this->method) == 'post') {
      $this->populate($_POST);
    }
    else {
      $this->populate($_GET);
    }
  }
  //@+node:caminhante.20210905133005.1: *3* validate
  /**
   * Validates a form with its fields.
   *
   * @param array $data the array to populate with validated data.
   */
  public function validate(&$data) {
    $validate = true;
    foreach ($this->components as $component) {
      $validate &= $component->validate($data);
    }
    return $validate;
  }
  //@-others
  abstract public function render();
}
//@+node:caminhante.20210905133040.1: ** FormComponent
/**
 * A form component, could be anything in the form (field, fieldset, text...).
 */
abstract class FormComponent {
  public $form = null;
  /**
   * Initializes a component with a set of params.
   */
  public function __construct($params = array()) {
    $this->set($params);
  }
  public function set($params = array()) {
    foreach ($params as $key => $value) {
      $this->$key = $value;
    }
  }
  /**
   * Sets the form.
   */
  public function setForm(&$form) {
    $this->form =& $form;
  }
  /**
   * Populates the component/inner components.
   */
  abstract public function populate(&$data);
  /**
   * Validates the component/inner components in their current state.
   *
   * @return boolean
   */
  abstract public function validate(&$data);
  /**
   * Renders the component.
   */
  abstract public function render();
}
//@+node:caminhante.20210905133047.1: ** FormField
/**
 * A field within a form.
 */
abstract class FormField extends FormComponent {
  public $name = null;
  public $value = null;
  public function populate(&$data) {
    if (array_key_exists($this->name, $data)) {
      $this->value = $data[$this->name];
    }
  }
  public function validate(&$data) {
    $data[$this->name] = $this->value;
    return true;
  }
  public function getName() {
    return $this->name;
  }
  public function getValue() {
    return $this->value;
  }
}
//@+node:caminhante.20210905133056.1: ** FormGroup
abstract class FormGroup extends FormComponent {
  public $components = array();
  //@+others
  //@+node:caminhante.20210905133132.1: *3* setForm
  public function setForm(&$form) {
    parent::setForm($form);
    foreach ($this->components as $component) {
      $component->setForm($this->form);
    }
  }
  //@+node:caminhante.20210905133139.1: *3* addComponent
  public function addComponent(FormComponent $component) {
    $this->components[] = $component;
    $component->setForm($this->form);
    return $this;
  }
  //@+node:caminhante.20210905133144.1: *3* addComponents
  public function addComponents($components) {
    foreach ($components as $component) {
      $this->addComponent($component);
    }
    return $this;
  }
  //@+node:caminhante.20210905133153.1: *3* getComponentByName
  public function getComponentByName($name) {
    foreach ($this->components as $component) {
      if ($component instanceof FormGroup) {
        $foundComponent = $component->getComponentByName($name);
        if (!empty($foundComponent)) {
          return $foundComponent;
        }
      }
      elseif ($component instanceof FormField) {
        if ($component->name == $name) {
          return $component;
        }
      }
    }
    return null;
  }
  //@+node:caminhante.20210905133158.1: *3* populate
  public function populate(&$data) {
    foreach ($this->components as $component) {
      $component->populate($data);
    }
  }
  //@+node:caminhante.20210905133203.1: *3* validate
  public function validate(&$data) {
    $validated = true;
    foreach ($this->components as $component) {
      $validated &= $component->validate($data);
    }
    return $validated;
  }
  //@-others
}
//@+node:caminhante.20210905133103.1: ** FormAction
/**
 * An action the user can execute on the form (submit button, cancel...).
 */
abstract class FormAction {
  public $name = null;
  public function __construct($params = array()) {
    $this->set($params);
  }
  public function set($params = array()) {
    foreach ($params as $key => $value) {
      $this->$key = $value;
    }
  }
  /**
   * Indicates if the action has been triggered.
   *
   * @param array $data the posted data from the formular.
   */
  public function isTriggered($data) {
    return isset($data[$this->name]);
  }
  public function getName() {
    return $this->name;
  }
}
//@-others
//@-leo
