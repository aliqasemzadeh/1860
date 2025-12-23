<?php

namespace App\Imports;

use App\Models\Accounting\Cheque;
use Maatwebsite\Excel\Concerns\ToModel;

class CheuesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Cheque([
            //
        ]);
    }
}
