<?php

namespace Artificers\Database\Raintter\Concern;

use Artificers\Database\Raintter\Relations\ManyToMany;
use Artificers\Database\Raintter\Relations\OneToMany;
use Artificers\Database\Raintter\Relations\OneToOne;

trait HasRelationship {
    public function oneToOne(array $classes): OneToOne {
        return new OneToOne($classes);
    }

    public function oneToMany(array $classes): OneToMany {
        return new OneToMany();
    }

    public function manyToMany(array $classes): ManyToMany {
        return new ManyToMany();
    }

    public function belongsTo(array $classes) {

    }
}