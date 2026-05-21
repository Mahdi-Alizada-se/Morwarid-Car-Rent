<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Response;
use Illuminate\View\View;

class GpsTrackerController extends Controller
{
    public function track(Vehicle $vehicle, string $token): View|Response
    {
        if ($vehicle->tracker_token !== $token) {
            abort(403, 'Invalid tracker token');
        }

        return view('gps.tracker', compact('vehicle'));
    }
}