<?php
namespace Framework\Core\Interfaces;

interface ACLControl {
    public function on_request();
    public function get_login_url();
}

?>