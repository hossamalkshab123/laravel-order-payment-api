<?php

namespace App\Core\Traits;

trait HasEvents
{
    protected array $events = [];

    protected function recordEvent(string $event): void
    {
        $this->events[] = $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}
