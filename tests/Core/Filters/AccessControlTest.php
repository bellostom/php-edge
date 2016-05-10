<?php
namespace Edge\Tests\Core\Filters;

use Edge\Core\Tests\EdgeWebTestCase,
    Edge\Core\Edge,
    Edge\Core\Filters\AccessControl;

class AccessControlTest extends EdgeWebTestCase{

    protected $filter;

    public function setUp(){
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test/method';
    }

    protected function getFilter(array $params){
        return new AccessControl($params);
    }

    /**
     * @expectedException \Edge\Core\Exceptions\Forbidden
     */
    public function testThrowForbidden(){
        $filter = $this->getFilter([
            "permissions" => ["Delete User"],
            "user" => Edge::app()->user()
        ]);
        $filter->preProcess(Edge::app()->response, Edge::app()->request);
    }

    /**
     * @expectedException \Edge\Core\Exceptions\EdgeException
     */
    public function testGuestUserNoPermissions(){
        $filter = $this->getFilter([
           "permissions" => [],
           "user" => Edge::app()->user()
        ]);
        $filter->preProcess(Edge::app()->response, Edge::app()->request);
    }

    public function testAdminUserNoPermissions(){
        $user = parent::getUser([
                    "username" => "admin"
                ], ["hasPrivilege"]);
        $filter = $this->getFilter([
           "permissions" => [],
           "user" => $user
        ]);
        $this->assertTrue($filter->preProcess(Edge::app()->response, Edge::app()->request));
    }

    public function testUserWithPermissions(){
        $user = parent::getUser([
                    "username" => "edge"
                ], ["hasPrivilege"]);
        $user->method('hasPrivilege')
             ->willReturn(true);
        $filter = $this->getFilter([
                       "permissions" => ["Delete User"],
                       "user" => $user
                   ]);
        $this->assertTrue($filter->preProcess(Edge::app()->response, Edge::app()->request));
    }

    /**
     * @expectedException \Edge\Core\Exceptions\Forbidden
     */
    public function testUserWithNoPermissions(){
        $user = parent::getUser([
                    "username" => "edge"
                ], ["hasPrivilege"]);
        $user->method('hasPrivilege')
             ->willReturn(false);
        $filter = $this->getFilter([
                   "permissions" => ["Delete User"],
                   "user" => $user
               ]);
        $filter->preProcess(Edge::app()->response, Edge::app()->request);
    }
}