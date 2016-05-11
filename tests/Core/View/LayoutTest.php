<?php
namespace Edge\Tests\Core\View;

use Edge\Core\Tests\EdgeWebTestCase,
    Edge\Core\View\Layout;

class LayoutTest extends EdgeWebTestCase{

    public function tearDown(){
        parent::tearDown();
        @unlink("/tmp/file1.js");
    }

    public function testInlineCss(){
        $css = <<<CSS
<style>
html {
  font-size: 100%;
}

a:focus {
  outline-offset: -2px;
}
</style>
CSS;

        Layout::addInlineCss($css);
        $layout = new Layout(null, [], []);
        $minified = '<style>html{font-size:100%}a:focus{outline-offset:-2px}</style>';
        $this->assertEquals($minified, $layout->getInlineCss());

        //make sure duplicates are removed
        Layout::addInlineCss($css);
        $this->assertEquals($minified, $layout->getInlineCss());

        $css1 = <<<CSS
<style>
body {
  font-size: 100%;
}

a:focus {
  outline-offset: -2px;
}
</style>
CSS;
        //add a new style
        Layout::addInlineCss($css1);
        $minified = '<style>html{font-size:100%}a:focus{outline-offset:-2px}</style><style>body{font-size:100%}a:focus{outline-offset:-2px}</style>';
        $this->assertEquals($minified, $layout->getInlineCss());

        $layout->setMinify(false);
        $this->assertEquals($css."\n".$css1, $layout->getInlineCss());
    }

    public function testInlineJs(){
        $js = <<<JS
<script>
    function alert(){
        alert("edge");
    }
</script>
JS;

        Layout::addInlineJs($js);
        $layout = new Layout(null, [], []);
        $minified = '<script>function alert(){alert("edge");}</script>';
        $this->assertEquals($minified, $layout->getInlineJs());

        Layout::addInlineJs($js);
        $this->assertEquals($minified, $layout->getInlineJs());
        $js1 = <<<JS
<script>
    function alertAgain(){
        alert("edge");
    }
</script>
JS;
        Layout::addInlineJs($js1);
        $minified = '<script>function alert(){alert("edge");}</script><script>function alertAgain(){alert("edge");}</script>';
        $this->assertEquals($minified, $layout->getInlineJs());

        $layout->setMinify(false);
        $this->assertEquals($js."\n".$js1, $layout->getInlineJs());
    }

    public function testaddJs(){
        $js = <<<JS
<script>
    function alert(){
        alert("edge");
    }
</script>
JS;
        $file = "/tmp/file1.js";
        file_put_contents($file, $js);

        Layout::addJs([$file]);
        $layout = new Layout(null, [$file], []);
        $this->assertCount(1, $layout->getJsFiles());

        Layout::addJs([$file]);
        $this->assertCount(1, $layout->getJsFiles());
    }
}