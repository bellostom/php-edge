<?php
namespace Framework\Core\Database\ResultSet;

class CachedObjectSet extends ObjectSet {

    protected function setRows() {
        $this->totalRows = count($this->result);
    }

    public function offsetGet($offset) {
        $data = $this->result[$offset];
        return new $this->class_name($data);
    }
}
?>