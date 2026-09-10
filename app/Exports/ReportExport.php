<?php

namespace App\Exports;

use App\Enums\Community;
use App\Models\Report;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportExport implements FromView, WithTitle, ShouldAutoSize
{
    public function __construct(
        private Community $community,
        private array $filters = [],
    ) {}

    public function view(): View
    {
        $period = '';
        if (! empty($this->filters['month']) && ! empty($this->filters['year'])) {
            $period = sprintf('%s %d', $this->monthName((int) $this->filters['month']), (int) $this->filters['year']);
        } elseif (! empty($this->filters['year'])) {
            $period = (string) (int) $this->filters['year'];
        } else {
            $period = 'Semua periode';
        }

        $reports = Report::filtered($this->community, $this->filters)
            ->with(['category:id,name', 'user:id,name'])
            ->get();

        return view('exports.reports', [
            'communityName' => $this->community->config()['title'],
            'period' => $period,
            'reports' => $reports,
        ]);
    }

    public function title(): string
    {
        return mb_substr('Laporan '.$this->community->config()['short'], 0, 31);
    }

    private function monthName(int $m): string
    {
        return ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$m - 1];
    }
}