<?php
declare(strict_types=1);
namespace Artificers\Treaties\Support;

interface Jsonable {
    public function toJson(int $option=0);
}