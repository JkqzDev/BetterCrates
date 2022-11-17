<?php

declare(strict_types=1);

namespace juqn\bettercrates\session;

use juqn\bettercrates\session\handler\PlaceCrateHandler;

final class Session {

    private ?PlaceCrateHandler $placeCrateHandler = null;

    public function getPlaceCrateHandler(): ?PlaceCrateHandler {
        return $this->placeCrateHandler;
    }

    public function startPlaceCrateHandler(string $crateName): void {
        $this->placeCrateHandler = new PlaceCrateHandler($crateName);
    }

    public function stopPlaceCrateHandler(): void {
        $this->placeCrateHandler = null;
    }
}