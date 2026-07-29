<?php

namespace App\Jobs;

use App\Enums\Sentiment;
use App\Models\AnalysisResult;
use App\Services\LLM\SentimentAnalyzerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class AnalyzeTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public AnalysisResult $result
    ) {}

    public function handle(SentimentAnalyzerService $analyzer): void
    {
        $this->result->update(['status' => 'processing']);

        try {
            $sentiment = $analyzer->analyze($this->result->text);

            $this->result->update([
                'sentiment' => $sentiment->value,
                'status' => 'completed',
            ]);
        } catch (Throwable $e) {
            Log::error("AnalyzeTextJob failed for result ID: {$this->result->id}", [
                'error' => $e->getMessage()
            ]);

            $this->result->update(['status' => 'failed']);

            throw $e;
        }
    }
}
