<?php

namespace App\Models\Additionals;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportExcel implements FromCollection, WithHeadings,ShouldAutoSize
{
    public $query;
  	public function __construct($query)
    {
        $this->query = $query;
    }
    public function collection()
    {
        return $this->query;
    }
    public function headings(): array
    {
        return array_keys( (array)($this->query[0]));
    }
}
