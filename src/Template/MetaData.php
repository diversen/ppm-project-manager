<?php

declare(strict_types=1);

namespace App\Template;

class MetaData {

    public array $head_elements = [];

    public function setHeadElement(string $element) {
        $this->head_elements[] = $element;
    }

    /**
     * @return array<mixed>
     */
    public function getHeadElements(): array {
        return $this->head_elements;
    }
}