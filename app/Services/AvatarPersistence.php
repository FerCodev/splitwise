<?php

namespace App\Services;

use Throwable;

class AvatarPersistence
{
    public function __construct(private AvatarStorage $storage)
    {
    }

    public function replace(?string $oldFilename, string $newFilename, callable $persist): void
    {
        try {
            $persist();
        } catch (Throwable $e) {
            $this->storage->delete($newFilename);
            throw $e;
        }
        if ($oldFilename !== $newFilename) {
            $this->storage->delete($oldFilename);
        }
    }

    public function remove(?string $oldFilename, callable $persist): void
    {
        $persist();
        $this->storage->delete($oldFilename);
    }
}
