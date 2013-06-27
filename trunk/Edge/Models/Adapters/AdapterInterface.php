<?php
namespace Edge\Models\Adapters;
use Edge\Models\ActiveRecord;

interface AdapterInterface{
    public function find(array $options, $class);
    public function save(ActiveRecord $entry);
    public function delete(ActiveRecord $entry, array $criteria=array());
    public function update(ActiveRecord $entry);
    public function getDbConnection();
    public function getResultSet($rs, $class);
    public function fetchAll($rs);
    public function fetchArray($rs);
    public function manyToMany($model, array $attrs);
}
?>