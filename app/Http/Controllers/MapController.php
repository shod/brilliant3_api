<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Repositories\PtpRepository;
use Illuminate\Support\Facades\App;

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
        $device_id = '34851825C972';
        $data = $this->ptp_repository->getPtpById($device_id);

        return view('map', [
            'base_url' => env('APP_URL'),
            'map_image_path' => '././images/map_main.png',
            'device_image_path' => '././images/devices/device_1.png?v=1',
            'data'  => json_encode($data),
            'device_id' => $device_id,
        ]);
    }

    /**
     * Show the profile for a given user.
     */
    public function showPtp($device_id): View
    {
        //$device_id = '3485182548CA';
        $data = $this->ptp_repository->getPtpById($device_id);

        return view('map', [
            'map_image_path' => '../images/map_main.png',
            'device_image_path' => '../images/devices/device_1.png',
            'data'  => json_encode($data),
            'device_id' => $device_id,
        ]);
    }
}
