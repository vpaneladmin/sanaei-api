<?php

namespace App\Http\Controllers;

use App\Models\Inbound;

class InboundController extends Controller
{
    public function list()
    {
        $inbounds = Inbound::all();

        return response()->json([
            'status' => 'success',
            'data' => $inbounds,
        ]);
    }

    public function traffic($id)
    {
        $inbound = Inbound::find($id);
        if ($inbound) {
            $traffics = $inbound->traffics;

            return response()->json([
                'status' => 'success',
                'data' => $traffics,
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
            ]);
        }
    }
}
