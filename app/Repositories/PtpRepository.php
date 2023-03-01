<?php

namespace App\Repositories;

use App\Interfaces\PtpRepositoryInterface;

class PtpRepository implements PtpRepositoryInterface
{
  public function getSessions($programId)
  {
    abort(404, "Method not implemented");
  }
  public function getPtpById($ptpId): array
  {
    return [['id' => $ptpId, 'x' => 200, 'y' => 500]];
  }
}
