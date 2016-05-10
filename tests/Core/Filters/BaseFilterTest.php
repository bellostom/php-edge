<?php
namespace Edge\Tests\Core\Filters;

use Edge\Core\Tests\EdgeTestCase,
    Edge\Core\Filters\Authentication;

class BaseFilterTest extends EdgeTestCase{

    protected $filter;

    protected function getFilter(array $params){
        $params['url'] = "http://login/url";
        return new Authentication($params);
    }

    public function testActionIncludedInApplyTo(){
        $filter = $this->getFilter([
            "applyTo" => ["method"]
        ]);
        $this->assertTrue($filter->appliesTo("method"));
        $this->assertFalse($filter->appliesTo("login"));
    }

    public function testActionIncludedInApplyToDefault(){
        $filter = $this->getFilter([]);
        $this->assertTrue($filter->appliesTo("method"));
        $this->assertTrue($filter->appliesTo("login"));
    }

    public function testActionExclusion(){
        $filter = $this->getFilter([
            "exceptions" => ["method"]
                                   ]);
        $this->assertFalse($filter->appliesTo("method"));
        $this->assertTrue($filter->appliesTo("login"));
    }
}