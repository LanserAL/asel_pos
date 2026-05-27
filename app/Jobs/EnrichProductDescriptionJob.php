<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\AIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EnrichProductDescriptionJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $productId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AIService $aiService): void
    {
        $product = Product::find($this->productId);
        
        if (!$product || empty($product->raw_title)) {
            return;
        }

        $description = $aiService->generateProductDescription($product->raw_title);

        if ($description) {
            $product->description = $description;
            $product->save();
        }
    }
}
