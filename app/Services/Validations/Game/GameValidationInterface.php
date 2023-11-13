<?php

namespace App\Services\Validations\Game;

interface GameValidationInterface
{
    public function vote($id): mixed;
    
}
