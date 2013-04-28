<?php
namespace Framework\Models\Adapters;
use Framework\Models\ActiveRecord;

interface AdapterInterface{
    public function find(array $options, $class);
    public function save(ActiveRecord $entry);
    public function delete(ActiveRecord $entry, array $criteria=array());
    public function update(ActiveRecord $entry);
}
?>