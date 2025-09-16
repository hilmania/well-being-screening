<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\WellBeingScreening;
use App\Models\VolunteersResponse;
use App\Models\PsychologistResponse;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Total responden yang telah mengisi screening
        $totalResponden = WellBeingScreening::distinct('user_id')->count();
        
        // Total responden yang sudah ditangani oleh relawan
        $respondenDitanganiRelawan = WellBeingScreening::whereHas('volunteerResponses')->distinct('user_id')->count();
        
        // Total responden yang sudah ditangani oleh psikolog  
        $respondenDitanganiPsikolog = WellBeingScreening::whereHas('psychologistResponses')->distinct('user_id')->count();
        
        // Total screening yang dilakukan
        $totalScreening = WellBeingScreening::count();

        return [
            Stat::make('Total Responden Mengisi', $totalResponden)
                ->description('Responden yang telah mengisi screening')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
                
            Stat::make('Ditangani Relawan', $respondenDitanganiRelawan)
                ->description('Responden yang sudah divalidasi relawan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Ditangani Psikolog', $respondenDitanganiPsikolog)
                ->description('Responden yang sudah ditangani psikolog')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning'),
                
            Stat::make('Total Screening', $totalScreening)
                ->description('Total screening yang dilakukan')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),
        ];
    }
}
