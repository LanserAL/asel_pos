<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    public function generateProductDescription(string $title): ?string
    {
        $apiKey = config('services.openai.key') ?? env('OPENAI_API_KEY');
        
        if (!$apiKey) {
            Log::warning('OpenAI API Key not configured.');
            return null;
        }

        $prompt = "Actúa como un copywriter experto en e-commerce. Genera una descripción atractiva, optimizada para SEO y vendedora de un producto basado estrictamente en el siguiente título: [{$title}]. Devuelve únicamente el texto de la descripción en formato Markdown limpio, sin introducciones ni textos explicativos adicionales.";

        try {
            $response = Http::withToken($apiKey)
                ->retry(3, 100)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an expert e-commerce copywriter.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 150,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('OpenAI API Error', ['response' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('OpenAI Connection Error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
