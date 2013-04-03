<?php
namespace Framework\Core\Interfaces;

interface EventHandler {
    public function on_create();
    public function on_after_create();
    public function on_update();
    public function on_after_update();
    public function on_delete();
    public function on_after_delete();
}
?>