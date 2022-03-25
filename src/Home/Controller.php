<?php

declare(strict_types=1);

namespace App\Home;

use Parsedown;
use Pebble\Exception\NotFoundException;
use App\AppMain;

class Controller
{
    public function __construct()
    {
        $this->auth_id = (new AppMain())->getAuth()->getAuthId();
    }

    /**
     * @route /
     * @verbs GET
     */
    public function index()
    {
        if ($this->auth_id) {
            header('Location: /overview');
        }

        $data = ['title' => 'PPM'];
        \Pebble\Template::render('Home/views/home.tpl.php', $data);
    }

    /**
     * @route /terms/:document
     * @verbs GET,POST
     */
    public function terms($params)
    {
        $markdown_file = '../src/Home/views/' . $params['document'] . '.md';

        if (!file_exists($markdown_file) || !is_file($markdown_file)) {
            throw new NotFoundException('File does not exists.');
        }

        $markdown_text = file_get_contents($markdown_file);
        $parsedown = new Parsedown();

        $parsedown->setSafeMode(false);

        $data['note_markdown'] = $parsedown->text($markdown_text);

        \Pebble\Template::render('Home/views/terms.tpl.php', $data, ['raw' => true]);
    }
}
