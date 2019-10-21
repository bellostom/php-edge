<?php
namespace Edge\Tests\Core\View;

use Edge\Core\Tests\EdgeWebTestCase,
    Edge\Core\View\Layout;

/**
 * @runTestsInSeparateProcesses
 * Since there are static variables involved,
 * run tests in different processes
 */
class LayoutTest extends EdgeWebTestCase{

    public function setUp(){
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test/get';
        parent::initRouter();
    }

    public function tearDown(){
        parent::tearDown();
        $dir = "/tmp/edge_files";
        @unlink("/tmp/file1.js");
        @unlink("/tmp/file1.css");
        if(is_dir($dir)){
            $files = glob("$dir/*");
            foreach($files as $file){
                unlink($file);
            }
            rmdir($dir);
        }
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
        touch($file, 5000);

        Layout::addJs([$file]);
        $layout = new Layout(null, [$file], []);
        $this->assertCount(1, $layout->getJsFiles());

        Layout::addJs([$file]);
        $this->assertCount(1, $layout->getJsFiles());
        $this->assertEquals("/js/5000_06fe9333ab03ee381fce6d7aca5b0f51.js", $layout->getjsScript());
    }

    public function testaddJsWithAsterisk(){
        mkdir("/tmp/edge_files");
        $js = <<<JS
<script>
    function alert(){
        alert("edge");
    }
</script>
JS;
        $file = "/tmp/edge_files/file.js";
        file_put_contents($file, $js);
        touch($file, 5000);

        $js = <<<JS
<script>
    function alertAgain(){
        alert("edge");
    }
</script>
JS;

        $file1 = "/tmp/edge_files/file1.js";
        file_put_contents($file1, $js);
        touch($file1, 5000);

        $layout = new Layout(null, ["/tmp/edge_files/*"], []);
        $this->assertCount(1, $layout->getJsFiles());
        $this->assertEquals("/js/5000_9d4b76c60fa1ce34a4be33ec1111c7e4.js", $layout->getjsScript());
    }

    public function testaddCss(){
        $css = <<<CSS
<style>
body {
  font-size: 100%;
}

a:focus {
  outline-offset: -2px;
}
</style>
CSS;
        $file = "/tmp/file1.css";
        file_put_contents($file, $css);
        //set modification time to a known value
        touch($file, 5000);

        Layout::addCss([$file]);
        $layout = new Layout(null, [], [$file]);
        $this->assertCount(1, $layout->getCssFiles());

        Layout::addCss([$file]);
        $this->assertCount(1, $layout->getCssFiles());
        $this->assertEquals("/css/5000_88bb602854eb362a44cda120aa59b063.css", $layout->getCssScript());
    }
}