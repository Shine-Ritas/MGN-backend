<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface ModelRepoInterface
{
    public function get(Request $request): mixed;

    public function collection(): mixed;
}
