<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\DataPtpResource;
use App\Http\Resources\DataPtpResourceCollection;
use App\Repositories\PtpRepository;


class DataController extends Controller
{

    private PtpRepository $ptp_repository;

    public function __construct()
    {
        $this->ptp_repository = new PtpRepository();
    }
    /**
     * Show location info for ptp
     */
    public function index(): DataPtpResourceCollection
    {
        return new DataPtpResourceCollection([]);
    }

    /**
     * Show location info for ptp
     */
    public function show($id): DataPtpResource
    {
        //'[{"2" :{"id": 2, "x": 200, "y": 520}}];'
        //$data = [["id" => $id, "x" => 200, "y" => 500]];

        $data = $this->ptp_repository->getPtpById($id);
        return new DataPtpResource($data);
    }
}
