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
     * Define headings as an array of arrays to occupy specific rows.
     */
    public function headings(): array
    {
        return [
            ['ATTENDANCE REPORT'],               // Row 1
            ['Date Range: ' . $this->dateRange], // Row 2
            [                                    // Row 3
                'Employee Name',
                'Department',
                'Lates',
                'Undertime',
                'Paid Absences',
                'Unpaid Absences',
                'Holiday',
                'Overtime',
                'Total Worked Time'
            ]
        ];
    }

    public function map($report): array
    {
        return [
            $report['employee_name'],
            $report['department_name'] ?? 'N/A',
            $report['late_minutes'] > 0
                ? $this->formatTime(floor($report['late_minutes'] / 60), $report['late_minutes'] % 60)
                : '0',
            ($report['undertime_hours']['h'] > 0 || $report['undertime_hours']['m'] > 0)
                ? $this->formatTime($report['undertime_hours']['h'], $report['undertime_hours']['m'])
                : '0',
            $report['paid_leaves'] ?: '0',
            $report['unpaid_leaves'] ?: '0',
            $report['holiday_count'] ?: '0',
            ($report['overtime_hours']['h'] > 0 || $report['overtime_hours']['m'] > 0)
                ? $this->formatTime($report['overtime_hours']['h'], $report['overtime_hours']['m'])
                : '0',
            $this->formatTotalTime($report['total_summary'])
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge Title and Date Range across all 9 columns (A to I)
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');

        $lastRow = count($this->reports) + 3; // +3 for the 3 heading rows
        $brandGreen = '52a447';

        return [
            // Row 1: Main Title
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E7D32'] // Darker green for depth
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Row 2: Date Range
            2 => [
                'font' => ['italic' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $brandGreen]
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Row 3: Column Headings
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $brandGreen]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Applied to the table area: Green Borders and Centered Text
            'A3:I' . $lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => $brandGreen], // GREEN BORDERS
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    private function formatTime($h, $m)
    {
        $res = [];
        if ($h > 0) $res[] = $h . 'h';
        if ($m > 0) $res[] = $m . 'm';
        return count($res) > 0 ? implode(' ', $res) : '0';
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
