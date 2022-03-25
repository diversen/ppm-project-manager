<?php

declare(strict_types=1);

// This we can trust
$parsedown = new Parsedown();
$parsedown->setSafeMode(false);

$note = file_get_contents('../src/Home/views/home.md');

$note_markdown = $parsedown->text($note);

require 'templates/header.tpl.php';

echo $note_markdown;


require 'templates/footer.tpl.php';
