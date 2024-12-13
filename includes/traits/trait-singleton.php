<?php

namespace GLColorPalette\Traits;

trait Singleton {
    protected static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
} 
