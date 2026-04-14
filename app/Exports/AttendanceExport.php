<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $reports;
    protected $dateRange;

    public function __construct($reports, $dateRange)
    {
        $this->reports = $reports;
        $this->dateRange = $dateRange;
    }

    public function collection()
    {
        return collect($this->reports);
    }

    /**
     * Updated Headings to match your requested format exactly.
     */
    public function headings(): array
    {
        return [
            ['ATTENDANCE REPORT'],
            ['Date Range: ' . $this->dateRange],
            [
                'Employee Name',
                'Department',
                'Lates (hr)',
                'Undertime (hr)',
                'Paid Absences',
                'Unpaid Absences',
                'Regular Hol.',
                'Special Hol.',
                'RD Regular Hol.',
                'RD Special Hol.',
                'Regular OT',
                'RD OT',
                'Regular Holiday OT',
                'Special Holiday OT',
                'RD Regular Holiday OT',
                'RD Special Holiday OT',
                'Total Worked Time'
            ]
        ];
    }

    /**
     * Updated Mapping to match the column order.
     */
    public function map($report): array
    {
        return [
            $report['employee_name'],
            $report['department_name'],
            $this->formatMins($report['late_minutes']),
            $this->formatMins($report['undertime_minutes']),
            $report['paid_leaves'] ?: '0',
            $report['unpaid_leaves'] ?: '0',

            // Holiday Counts
            $report['reg_hol'] ?: '0',
            $report['spec_hol'] ?: '0',
            $report['rd_reg_hol'] ?: '0',
            $report['rd_spec_hol'] ?: '0',

            // OT Breakdown
            $this->formatMins($report['ot_reg']),
            $this->formatMins($report['ot_rd']),
            $this->formatMins($report['ot_hol_reg']),
            $this->formatMins($report['ot_spec']),
            $this->formatMins($report['ot_rd_reg']),
            $this->formatMins($report['ot_rd_spec']),
            $this->formatTotalTime($report['total_summary'])
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // A to Q covers 17 columns
        $sheet->mergeCells('A1:Q1');
        $sheet->mergeCells('A2:Q2');

        $lastRow = count($this->reports) + 3;
        $brandGreen = '52a447';

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['italic' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $brandGreen]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $brandGreen]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            'A3:Q' . $lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => $brandGreen],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
            ],
        ];
    }

    private function formatMins($minutes)
    {
        if (!$minutes || $minutes <= 0) return '0';
        $h = floor($minutes / 60);
        $m = $minutes % 60;
        $res = [];
        if ($h > 0) $res[] = $h . 'h';
        if ($m > 0) $res[] = $m . 'm';
        return implode(' ', $res);
    }

    private function formatTotalTime($total)
    {
        $res = [];
        if (($total['days'] ?? 0) > 0) $res[] = $total['days'] . 'd';
        if (($total['hours'] ?? 0) > 0) $res[] = $total['hours'] . 'h';
        if (($total['minutes'] ?? 0) > 0) $res[] = $total['minutes'] . 'm';
        return count($res) > 0 ? implode(' ', $res) : '0m';
    }
}
