<?php declare (strict_types = 1);

use Diversen\Lang;
use Pebble\Special;

require 'App/templates/header.tpl.php'; 


$md = new Parsedown();
$md->setSafeMode(true);

$name = $user['name'] ?? '';
$bio = $user['bio'] ?? '';

if (empty($name)) {
    $title = Lang::translate('User has not entered any user info yet');
} else {
    $title = Lang::translate('User') . ' :: ' .  $user['name']; 
}

echo "<h3>$title</h3>";

if (empty($bio)) {
    $bio = "<p>" . Lang::translate('No user info') . "<p>";
} else {
    $bio = $md->parse(Special::decodeStr($bio));
}

echo $bio;


require 'App/templates/footer.tpl.php';
