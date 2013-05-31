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
 * the file's modification time
 * @package Edge\Core\Cache\Validator
 */
class FileValidator extends CacheValidator{

    private $file;

    public function __construct($file){
        $this->file = $file;
    }

    protected function validate(){
        return filemtime($this->file);
    }
}