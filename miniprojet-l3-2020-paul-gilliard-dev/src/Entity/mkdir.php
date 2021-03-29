<?php

namespace App\Entity;

class mkdir
{
protected $nameDir;

public function getnameDir(): ?string
{
    return $this->nameDir;
}

public function setnameDir(string $nameDir): void
{
$this->nameDir = $nameDir;
}
}