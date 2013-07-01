<?php
namespace Edge\Models\Adapters;
use Edge\Models\Record;

interface AdapterInterface{
    public function find(array $options, $class);
    public function save(Record $entry);
    public function delete(Record $entry, array $criteria=array());
    public function update(Record $entry);
    public function getDbConnection();
    public function getResultSet($rs, $class);
    public function fetchAll($rs);
    public function fetchArray($rs);
    public function manyToMany($model, array $attrs);
}
?>