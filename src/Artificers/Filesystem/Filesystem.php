<?php

namespace Artificers\Filesystem;

class Filesystem {
    public const _DS_ = "/";

    public function getAbsoluteRootDir(): string {
        return dirname($this->getCurrentWorkingDir());
    }

    public function getCurrentWorkingDir(): string {
        return getcwd();
    }
}