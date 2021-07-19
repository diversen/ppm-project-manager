<?php declare (strict_types = 1);

use Pebble\Template;
use PHPUnit\Framework\TestCase;

final class TemplateTest extends TestCase
{

    public function test_render() {

        $variables = ['escape_this' => '<p>Test</p>'];

        $template = __DIR__ . "/templates/test.tpl.php";
        Template::render($template, $variables);

        $this->expectOutputString('<p>&lt;p&gt;Test&lt;/p&gt;</p>');

    }

    public function test_getOutput() {

        $variables = ['escape_this' => '<p>Test</p>'];

        $template = __DIR__ . "/templates/test.tpl.php";
        $str = Template::getOutput($template, $variables);

        $this->assertEquals('<p>&lt;p&gt;Test&lt;/p&gt;</p>', $str);

    }

    public function test_getOutput_raw() {

        $variables = ['escape_this' => '<pre>Test</pre>'];

        $template = __DIR__ . "/templates/test.tpl.php";
        $str = Template::getOutput($template, $variables, ['raw' => true]);

        $this->assertEquals('<p><pre>Test</pre></p>', $str);

    }



}
