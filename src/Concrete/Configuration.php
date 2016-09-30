<?php
namespace Concrete\Package\WarningsLog;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Configuration
{
    const FULL_KEY = 'app.providers.core_whoops';
    const SUB_KEY = 'providers.core_whoops';

    private static $defaultProvider = null;

    public static function getDefaultProvider()
    {
        if (self::$defaultProvider === null) {
            $filename = DIR_BASE_CORE.'/config/app.php';
            $fs = new \Illuminate\Filesystem\Filesystem();
            $data = $fs->getRequire($filename);
            self::$defaultProvider = array_get($data, static::SUB_KEY);
        }

        return self::$defaultProvider;
    }
}
