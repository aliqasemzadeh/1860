<?php

namespace App\Imports;

use App\Models\Accounting\Cheque;
use Maatwebsite\Excel\Concerns\ToModel;
use Morilog\Jalali\Jalalian;

class CheuesImport implements ToModel
{
    /**
     * @param  array  $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Expecting:
        // E column (index 4): amount (with thousands separators)
        // F column (index 5): due_at in Jalali (Y/m/d)
        // G column (index 6): description

        // Skip header row (first row usually contains column titles)
        if (isset($row[4]) && is_string($row[4]) && mb_strpos($row[4], 'مبلغ') !== false) {
            return null;
        }

        $amountRaw = (string) ($row[4]/10 ?? '');
        $dueAtJalali = (string) ($row[5] ?? '');
        $description = trim((string) ($row[6] ?? ''));

        if ($amountRaw === '' && $dueAtJalali === '' && $description === '') {
            return null;
        }

        // Normalize amount (remove commas)
        $amount = (float) str_replace(',', '', $amountRaw);

        // Normalize Jalali date: allow 1403-01-01 or 1403/01/01
        $dueAtJalali = str_replace('-', '/', trim($dueAtJalali));

        try {
            $dueAt = Jalalian::fromFormat('Y/m/d', $dueAtJalali)->toCarbon();
        } catch (\Throwable $e) {
            // If date is invalid, skip this row.
            return null;
        }

        return new Cheque([
            'description' => $description,
            'amount' => $amount,
            'due_at' => $dueAt,
        ]);
    }
}
