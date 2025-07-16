<?php

namespace CrossSiteLinker;

class Plugin
{
    private static $instance;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init()
    {
        // Load other components
        new Api\Provider();
        new Admin\Settings();
        new Editor\Sidebar();
        new Editor\LinkInserter();
    }
}
