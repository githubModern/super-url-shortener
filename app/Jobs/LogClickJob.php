<?php
// © Atia Hegazy — atiaeno.com

namespace App\Jobs;

use App\Models\Click;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LogClickJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $linkId,
        private array $clickData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Click::create([
            'link_id' => $this->linkId,
            ...$this->clickData,
        ]);
    }
}
