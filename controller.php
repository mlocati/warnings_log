<?php 
namespace Concrete\Package\WarningsLog;

defined('C5_EXECUTE') or die('Access Denied.');

use Package;
use Page;
use SinglePage;
use Exception;
use Concrete\Package\WarningsLog\Src\Configuration;
use Application\Concrete\Error\Provider\WhoopsServiceProvider;

class Controller extends Package
{
    protected $pkgHandle = 'warnings_log';
    protected $appVersionRequired = '8.0.0b3';
    protected $pkgVersion = '1.3.8';

    public function getPackageName()
    {
        return t("Warnings Log");
    }

    public function getPackageDescription()
    {
        return t("Log all the warnings.");
    }

    public function testForInstall($testForAlreadyInstalled = true)
    {
        $result = parent::testForInstall($testForAlreadyInstalled);
        if ($result === true) {
            $errors = [];
            $fs = new \Illuminate\Filesystem\Filesystem();
            if (!$fs->isDirectory(DIR_APPLICATION.'/src') && $fs->isWritable(DIR_APPLICATION.'/src')) {
                $errors[] = t('The folder %s must be writable', 'application/src');
            }
            if (!class_exists('\PDO') || !is_callable('\PDO::getAvailableDrivers')) {
                $errors[] = t('%s PHP extension is not installed', 'PDO');
            } else {
                if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
                    $errors[] = t('%s PDO driver is not installed', 'SQLite');
                }
            }
            if (!empty($errors)) {
                $result = $this->app->make('error');
                foreach ($errors as $error) {
                    $result->add($error);
                }
            }
        }

        return $result;
    }

    public function install()
    {
        $pkg = parent::install();
        self::installReal('');
    }

    public function uninstall()
    {
        $config = $this->app->make('config');
        if ($config->get(Configuration::FULL_KEY) === WhoopsServiceProvider::class) {
            $config->save(Configuration::FULL_KEY, Configuration::getDefaultProvider());
        }
        $fs = new \Illuminate\Filesystem\Filesystem();
        $filesMap = $this->getFilesMap();
        $destDir = str_replace('/', DIRECTORY_SEPARATOR, DIR_APPLICATION).DIRECTORY_SEPARATOR.'src';
        $stripDestDir = strlen($destDir) + 1;
        $affectedDirs = [];
        foreach ($filesMap as $installedFile) {
            if ($fs->isFile($installedFile)) {
                $fullDirectory = id(new \SplFileInfo($installedFile))->getPath();
                $fs->delete($installedFile);
                if (strlen($fullDirectory) > $stripDestDir && strpos($fullDirectory, $destDir.DIRECTORY_SEPARATOR) === 0) {
                    $now = $destDir;
                    foreach (explode(DIRECTORY_SEPARATOR, substr($fullDirectory, $stripDestDir)) as $chunk) {
                        $now .= DIRECTORY_SEPARATOR.$chunk;
                        if (!in_array($now, $affectedDirs, true)) {
                            $affectedDirs[] = $now;
                        }
                    }
                }
            }
        }
        usort($affectedDirs, function ($a, $b) {
            return strlen($b) - strlen($a);
        });
        foreach ($affectedDirs as $dir) {
            if (empty($fs->directories($dir)) && empty($fs->files($dir))) {
                @$fs->deleteDirectory($dir);
            }
        }
        parent::uninstall();
    }

    public function upgrade()
    {
        $currentVersion = $this->getPackageVersion();
        parent::upgrade();
        self::installReal($currentVersion);
    }

    protected function getFilesMap()
    {
        $result = [];
        $fs = new \Illuminate\Filesystem\Filesystem();
        $sourceDir = str_replace('/', DIRECTORY_SEPARATOR, $this->getPackagePath().'/app_src_files');
        $destDir = str_replace('/', DIRECTORY_SEPARATOR, DIR_APPLICATION).DIRECTORY_SEPARATOR.'src';
        $files = $fs->allFiles($sourceDir);
        foreach ($files as $file) {
            /* @var  $file */
            if ($file->getExtension() === 'source') {
                $fromPath = str_replace('/', DIRECTORY_SEPARATOR, $file->getPathname());
                $toPath = $destDir.substr(substr($fromPath, strlen($sourceDir)), 0, -strlen('source')).'php';
                $result[$fromPath] = $toPath;
            }
        }

        return $result;
    }

    protected function copyFiles()
    {
        $fs = new \Illuminate\Filesystem\Filesystem();
        $filesMap = $this->getFilesMap();
        foreach ($filesMap as $fromPath => $toPath) {
            $toDirectory = id(new \SplFileInfo($toPath))->getPath();
            if (!$fs->isDirectory($toDirectory)) {
                if (!$fs->makeDirectory($toDirectory, DIRECTORY_PERMISSIONS_MODE_COMPUTED, true)) {
                    throw new Exception(t('Failed to create the destination directory'));
                }
            }
            if ($fs->copy($fromPath, $toPath) === false) {
                throw new Exception(t('Failed to copy file to the destination directory'));
            }
        }
    }

    protected function installSinglePages()
    {
        $sp = Page::getByPath('/dashboard/reports/warnings_log');
        if (!is_object($sp) || $sp->getError() === COLLECTION_NOT_FOUND) {
            $sp = SinglePage::add('/dashboard/reports/warnings_log', $this);
            $sp->update([
                'cName' => t('Warnings Log'),
            ]);
        }
        $sp = Page::getByPath('/dashboard/reports/warnings_log/settings');
        if (!is_object($sp) || $sp->getError() === COLLECTION_NOT_FOUND) {
            $sp = SinglePage::add('/dashboard/reports/warnings_log/settings', $this);
            $sp->update([
                'cName' => t('Warnings Log Settings'),
            ]);
        }
    }

    protected function installReal($fromVersion)
    {
        $this->copyFiles();
        $this->installSinglePages();
    }
}
