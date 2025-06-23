<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class DataImport implements ToArray, WithHeadingRow
{
    protected $data = [];

    public function array(array $rows)
    {
        $this->data = $rows;
        return $rows;
    }

    public function getData()
    {
        return $this->data;
    }
} 