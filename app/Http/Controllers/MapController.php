<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Repositories\PtpRepository;

class MapController extends Controller
{
    private PtpRepository $ptp_repository;

    public function __construct()
    {
        $this->ptp_repository = new PtpRepository();
    }

    /**
     * Show the profile for a given user.
     */
    public function index(): View
    {
        $device_id = 1;
        $data = $this->ptp_repository->getPtpById($device_id);

        return view('map', [
            'map_image_path' => 'images/map_main.png',
            'device_image_path' => 'images/devices/device_1.png',
            'data'  => json_encode($data),
            'device_id' => $device_id,
        ]);
    }
}
