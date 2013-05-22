<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thomas
 * Date: 12/5/2013
 * Time: 10:52 πμ
 * To change this template use File | Settings | File Templates.
 */

namespace Framework\Core\Cache\Validator;
use Framework\Core\Database\DB;

/**
 * Class QueryValidator
 * Check cache validity based on the results of the
 * query. The query should return just one value, ie
 * SELECT COUNT(*) FROM users or
 * SELECT MAX(id) FROM users
 * @package Framework\Core\Cache\Validator
 */
class QueryValidator extends CacheValidator{

    private $sql;

    public function __construct($sql){
        $this->sql = $sql;
    }

    protected function validate(){
        $db = DB::getInstance();
        return $db->db_fetch_one($this->sql);
    }
}