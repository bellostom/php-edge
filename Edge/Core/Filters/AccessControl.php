<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Exceptions\Forbidden,
    Edge\Core\Http;

/**
 * Prep rocess filter that checks whether the current user
 * has permission to invoke the requested method.
 * The usual way to apply the filter is to create an instance method
 * in the controller, which defines a mapping between the controller's methods
 * and the permissions each method requires, along with the filter itself
 * Basic example
 *
 *
    protected function getAclMap(){
        return array(
            'index' => array('create user')
        );
    }

    public function filters(){
        return array_merge(parent::filters(), array(
            array(
                'Edge\Core\Filters\AccessControl',
                "permissions" => $this->getAclMap()[Edge::app()->router->getAction()]
            )
        ));
    }
 *
 *
 * @package Edge\Core\Filters
 */
class AccessControl extends BaseFilter{

    private $permissions;

    public function __construct(array $attrs){
        parent::__construct(array_key_exists('applyTo', $attrs)?$attrs['applyTo']:array("*"));
        $this->permissions = $attrs['permissions'];
    }

    public function preProcess(Http\Response $response, Http\Request $request){
        foreach($this->permissions as $perm){
            if(!Edge::app()->user()->hasPrivilege($perm)){
                throw new Forbidden("User has not the privilege to invoke ". $request->getRequestUrl());
            }
        }
    }
}