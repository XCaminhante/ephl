<?php
require_once('../../lib/templates.php');
template_folder(dirname(__FILE__) . '/views');
template_inherits('structure');
template_start('main');
switch (@$_GET['content']):
  case 'a':
?><p>You are on the content of page A, mister!</p><?php
    break;
  case 'b':
?><p>Yep, B panel!</p><?php
    break;
  case 'c':
?><p>Weird... This is the C section.</p><?php
    break;
  default:
?><p>This is the default content! Click on a link above, dummy!</p><?php
    break;
endswitch;
template_end();
template_start('title'); ?>
<h1>Template demo</h1>
<?php template_end();
