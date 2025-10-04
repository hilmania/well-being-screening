<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ScreeningQuestionTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                'Secara umum, bagaimana perasaan Anda dalam 2 minggu terakhir?',
                'likert',
                '',
                'basic_assessment',
                1,
                'true'
            ],
            [
                'Ceritakan lebih detail tentang perasaan atau emosi yang Anda alami saat ini',
                'text',
                'Jelaskan perasaan Anda dengan detail...',
                'mood_emotion',
                2,
                'true'
            ],
            [
                'Seberapa sering Anda merasa cemas atau khawatir dalam seminggu terakhir?',
                'likert',
                '',
                'anxiety_stress',
                3,
                'true'
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'question_text',
            'question_type',
            'placeholder',
            'group_name',
            'order',
            'is_active'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Style data rows
            'A2:F100' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50,  // question_text
            'B' => 15,  // question_type
            'C' => 30,  // placeholder
            'D' => 20,  // group_name
            'E' => 10,  // order
            'F' => 12,  // is_active
        ];
    }
}
