<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thomas
 * Date: 12/5/2013
 * Time: 10:52 πμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Cache\Validator;
use Edge\Core\Edge;

/**
 * Class QueryValidator
 * Check cache validity based on the results of the
 * query. The query should return just one value, ie
 * SELECT COUNT(*) FROM users or
 * SELECT MAX(id) FROM users
 * @package Edge\Core\Cache\Validator
 */
class QueryValidator extends CacheValidator{

    private $sql;

    public function __construct($sql){
        $this->sql = $sql;
    }

    protected function validate(){
        return Edge::app()->db->dbFetchOne($this->sql);
    }
}