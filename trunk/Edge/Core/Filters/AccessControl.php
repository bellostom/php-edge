<?php
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Exceptions\Forbidden,
    Edge\Core\Exceptions\EdgeException,
    Edge\Core\Http;

/**
 * Preprocess filter that checks whether the current user
 * has permission to invoke the requested method.
 * You define acl rules in the routes.php file
 * Example
 *
 *
    return array(
        'POST' => array(
            '/users/delete/:id' => array("Application\\Controllers\\User", "delete",
                                       "acl"=>array("Delete Users"))
        )
    );

    public function filters(){
        return array_merge(parent::filters(), array(
            array(
                'Edge\Core\Filters\AccessControl',
                "permissions" => Edge:app()->router->getPermissions()
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
        parent::__construct($attrs);
        $this->permissions = $attrs['permissions'];
    }

    public function preProcess(Http\Response $response, Http\Request $request){
        if(Edge::app()->user()->isAdmin()){
            if(!$this->permissions){
                Edge::app()->logger->warn("No permissions defined for URL ". Edge::app()->request->getRequestUrl());
            }
            return true;
        }
        if(!$this->permissions){
            throw new EdgeException("No permissions defined for URL ". Edge::app()->request->getRequestUrl());
        }
        foreach($this->permissions as $perm){
            if(Edge::app()->user()->hasPrivilege($perm)){
                return true;
            }
        }
        throw new Forbidden("User has not the privilege to invoke ". $request->getRequestUrl());
    }
}