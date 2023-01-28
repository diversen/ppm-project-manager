<?php

declare(strict_types=1);

namespace App\Template\Trait;


trait Render {

    /**
     * Render a template as HTML including a header and footer
     */
    public function renderPage(string $template, array $data = [], $options = [])
    {
        $title = $data['title'] ?? null;
        if (!$title) {
            $data['title'] = $this->config->get('App.site_name');
        }

        $description = $data['description'] ?? null;
        if (!$description) {
            $data['description'] = $title;
        }

        $this->template->render('Template/header.tpl.php', $data, $options);
        $this->template->render($template, $data, $options);
        $this->template->render('Template/footer.tpl.php', $data, $options);
    }

}