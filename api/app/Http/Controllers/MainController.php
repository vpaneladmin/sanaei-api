<?php

namespace App\Http\Controllers;

use MohsenAbrishami\Stethoscope\Services\Cpu;
use MohsenAbrishami\Stethoscope\Services\Memory;
use MohsenAbrishami\Stethoscope\Services\Storage;

class MainController extends Controller
{
    public function server(Cpu $cpu, Memory $memory, Storage $storage)
    {
        return response()->json([
            'cpu' => $cpu->check(),
            'memory' => $memory->check(),
            'storage' => $storage->check(),
        ]);
    }
}
