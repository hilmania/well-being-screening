<?php

namespace App\Filament\Widgets;

use App\Models\WellBeingScreening;
use Filament\Widgets\ChartWidget;

class StatusDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Status Penanganan';
    
    protected ?string $description = 'Pembagian status penanganan responden';

    protected function getData(): array
    {
        // Hitung distribusi status
        $totalScreening = WellBeingScreening::count();
        $handledByVolunteer = WellBeingScreening::whereHas('volunteerResponses')->count();
        $handledByPsychologist = WellBeingScreening::whereHas('psychologistResponses')->count();
        $notHandled = $totalScreening - $handledByVolunteer;

        return [
            'datasets' => [
                [
                    'data' => [$handledByPsychologist, $handledByVolunteer - $handledByPsychologist, $notHandled],
                    'backgroundColor' => [
                        '#f59e0b', // warning - ditangani psikolog
                        '#10b981', // success - ditangani relawan saja  
                        '#ef4444', // danger - belum ditangani
                    ],
                ],
            ],
            'labels' => ['Ditangani Psikolog', 'Ditangani Relawan', 'Belum Ditangani'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
