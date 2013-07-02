<?php

namespace Edge\Core\Cache\Validator;
use Edge\Core\Edge;

/**
 * Class FileValidator
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