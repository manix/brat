<?php

namespace Manix\Brat\Components;

interface Gateway {
    
    public function persist($data, ...$id);

    public function retrieve(...$id);
    
    public function wipe(...$id);
}