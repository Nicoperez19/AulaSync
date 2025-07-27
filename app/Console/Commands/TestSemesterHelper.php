<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\SemesterHelper;
use Carbon\Carbon;

class TestSemesterHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:semester-helper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the SemesterHelper with different dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing SemesterHelper with different dates...');
        $this->newLine();

        // Test dates
        $testDates = [
            '2025-01-15' => 'Mid January (should be semester 1)',
            '2025-06-15' => 'Mid June (should be semester 1)',
            '2025-07-15' => 'Mid July (should be semester 1)',
            '2025-07-21' => 'July 21st (should be semester 2)',
            '2025-07-25' => 'Late July (should be semester 2)',
            '2025-08-15' => 'Mid August (should be semester 2)',
            '2025-12-15' => 'Mid December (should be semester 2)',
            '2025-02-15' => 'Mid February (should be semester 1)',
        ];

        foreach ($testDates as $dateStr => $description) {
            $date = Carbon::parse($dateStr);
            $semester = SemesterHelper::getCurrentSemester($date);
            $year = SemesterHelper::getCurrentAcademicYear($date);
            $period = SemesterHelper::getCurrentPeriod($date);
            
            $this->line("📅 {$description}:");
            $this->line("   Date: {$date->format('Y-m-d')}");
            $this->line("   Semester: {$semester}");
            $this->line("   Academic Year: {$year}");
            $this->line("   Period: {$period}");
            $this->newLine();
        }

        // Test current date
        $this->info('Current date:');
        $currentSemester = SemesterHelper::getCurrentSemester();
        $currentYear = SemesterHelper::getCurrentAcademicYear();
        $currentPeriod = SemesterHelper::getCurrentPeriod();
        
        $this->line("📅 Current Date: " . Carbon::now()->format('Y-m-d'));
        $this->line("   Current Semester: {$currentSemester}");
        $this->line("   Current Academic Year: {$currentYear}");
        $this->line("   Current Period: {$currentPeriod}");
        
        $this->newLine();
        $this->info('✅ SemesterHelper test completed!');
    }
} 