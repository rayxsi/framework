<?php
namespace Artificers\Foundation\Bootstrap;

use Artificers\Config\Repository;
use Artificers\Foundation\Rayxsi;
use Artificers\Treaties\Bootstrap\BootstrapListenerTreaties;
use Symfony\Component\Finder\Finder;

class LoadConfigFiles implements BootstrapListenerTreaties {

    /**
     * Load all config file.
     *
     * @param object $event
     * @return void
     */
    public function load($event): void {
        //Create config repository instance
        $event->rXsiApp->setInstance('config', $repo = new Repository());

        $this->loadConfigFiles($event->rXsiApp, $repo);
    }

    /**
     * Load config files.
     *
     * @param Rayxsi $rXsiApp
     * @param Repository $repository
     * @return void
     */
    protected function loadConfigFiles(Rayxsi $rXsiApp, Repository $repository): void {
        $files = $this->getConfigFiles($rXsiApp);

        foreach($files as $key=>$path) {
            $repository->set($key, require_once $path);
        }
    }

    /**
     * Resolve all config file.
     *
     * @param Rayxsi $rXsiApp
     * @return array
     */
    protected function getConfigFiles(Rayxsi $rXsiApp): array {
        $files = [];
        foreach(Finder::create()->files()->name('*.php')->in($rXsiApp['path.config']) as $file) {
            $files[$file->getFilenameWithoutExtension()] = $file->getRealPath();
        }

        return $files;
    }
}