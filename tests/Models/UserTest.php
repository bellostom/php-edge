<?php
namespace Edge\Tests\Models;

use Edge\Models\User,
    Edge\Core\Tests\EdgeWebTestCase;

class UserTest extends EdgeWebTestCase{

    protected $user;

    public function setUp(){
        parent::setUp();
        $user = new User(["salt" => "qEytjqCxtg1"]);
        $user->username = "thomas";
        $user->name = "Thomas";
        $user->pass = "thomas";
        $user->email = "bellosthomas@gmail.com";
        $this->user = $user;
    }

    public function tearDown(){
        $this->user->delete();
        parent::tearDown();
    }

    public function testAdd(){
        $this->user->save();
        $this->assertEquals($this->user->username, User::getUserByUsername("thomas")->username);
    }

    public function testGetByAttributes(){
        $this->user->save();
        $this->assertEquals($this->user->id, User::getUserById($this->user->id)->id);
        $this->assertEquals($this->user->email, User::getUserByEmail($this->user->email)->email);
        $this->assertTrue($this->user->authenticate("thomas"));
        $this->assertFalse($this->user->hasPrivilege("delete user"));
    }

}