<?php

namespace App\Models;

/**
 * Класс для работы с конечными PTP устройствами
 */
class Device
{

  public int $id;
  public string $name;
  public array $coordinate;

  //?int $id, ?string $name
  public function __construct()
  {
    $this->id = 0;
    $this->name = '';
    $this->coordinate = ['x' => 0, 'y' => 0];
  }

  public function set($data)
  {
    foreach ($data as $key => $value) {
      $this->{$key} = $value;
    }
  }
}
