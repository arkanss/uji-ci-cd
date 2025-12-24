<?php

namespace App\Livewire\CsvReport;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ScheduledCsvJob;
use App\Models\ScheduledCsvData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('CSV Reports')]
class CsvReportIndex extends Component
{
    use WithPagination;

    public $selectedJobId;
    protected $paginationTheme = 'tailwind';

    public function showDetail(string $jobId)
    {
        $this->selectedJobId = $jobId;
        $this->modal('csv-detail-modal')->show();
    }

    public function render()
    {
        $jobs = ScheduledCsvJob::with(['creator'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $details = $this->selectedJobId 
            ? ScheduledCsvData::where('job_id', $this->selectedJobId)->get() 
            : [];

        return view('livewire.csv-report.csv-report-index', [
            'jobs' => $jobs,
            'details' => $details,
            'selectedJob' => $this->selectedJobId ? ScheduledCsvJob::find($this->selectedJobId) : null,
        ]);
    }
}