<?php
namespace Edge\Core\Database\ResultSet;

class CachedObjectSet extends ResultSet {

    public function getRecord($offset) {
        $data = $this->result[$offset];
        return new $this->className($data);
    }

    protected function setRows(){
        return count($this->result);
    }
}
?>