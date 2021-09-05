<?php declare (strict_types = 1);

// This we can trust
$parsedown = new Parsedown();
$parsedown->setSafeMode(false);

$note = file_get_contents('App/Home/views/home.md');

$note_markdown = $parsedown->text($note);

require 'App/templates/header.tpl.php';

echo $note_markdown;


require 'App/templates/footer.tpl.php';
