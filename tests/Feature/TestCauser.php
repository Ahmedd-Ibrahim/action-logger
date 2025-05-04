<?php

namespace Tests\Feature;

use BIM\ActionLogger\Contracts\CauserInterface;

class TestCauser implements CauserInterface
{
    protected int|string $id;
    protected string $name;
    protected string $type;

    public function __construct(int|string $id, string $name, string $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
    }

    public function getCauserId(): int|string
    {
        return $this->id;
    }

    public function getCauserName(): string
    {
        return $this->name;
    }

    public function getCauserType(): string
    {
        return $this->type;
    }
} 