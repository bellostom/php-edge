<?php
namespace Framework\Core\Database\ResultSet;

abstract class ResultSet extends EmptyIterator implements Countable {

    public function count(){
        return 0;
    }
    public function slice($star, $length) {
        return array();
    }
}
?>