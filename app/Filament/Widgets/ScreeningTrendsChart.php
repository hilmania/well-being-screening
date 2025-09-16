<?php

namespace App\Filament\Widgets;

use App\Models\WellBeingScreening;
use App\Models\VolunteersResponse;
use App\Models\PsychologistResponse;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ScreeningTrendsChart extends ChartWidget
{
    protected ?string $heading = 'Trend Screening Bulanan';
    
    protected ?string $description = 'Grafik perkembangan screening per bulan';

    protected function getData(): array
    {
        // Ambil data 12 bulan terakhir
        $months = [];
        $screeningData = [];
        $volunteerData = [];
        $psychologistData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');
            
            // Count screening per bulan
            $screeningCount = WellBeingScreening::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            $screeningData[] = $screeningCount;
            
            // Count volunteer responses per bulan
            $volunteerCount = VolunteersResponse::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            $volunteerData[] = $volunteerCount;
            
            // Count psychologist responses per bulan
            $psychologistCount = PsychologistResponse::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            $psychologistData[] = $psychologistCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Screening Dilakukan',
                    'data' => $screeningData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Ditangani Relawan',
                    'data' => $volunteerData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
                [
                    'label' => 'Ditangani Psikolog',
                    'data' => $psychologistData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
