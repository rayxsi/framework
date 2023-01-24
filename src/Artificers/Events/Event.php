<?php

namespace Artificers\Events;

use Artificers\Treaties\Events\EventTreaties;

class Event implements EventTreaties {
    public const type = '';
    private bool $propagationStopped = false;

    public function stopPropagation(): void {
        $this->propagationStopped = true;
    }

    /**
    * @inheritDoc
     */
    public function isPropagationStopped(): bool {

        return $this->propagationStopped;
    }
}