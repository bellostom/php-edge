<?php
namespace Edge\Core\Interfaces;

interface EventHandler {
    public function onCreate();
    public function onAfterCreate();
    public function onUpdate();
    public function onAfterUpdate();
    public function onDelete();
    public function onAfterDelete();
}
?>