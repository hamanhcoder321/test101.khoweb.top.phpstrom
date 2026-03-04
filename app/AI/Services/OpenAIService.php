<?php

namespace App\AI\Services;

class OpenAIService
{
    protected $url;
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->url    = env('OPENAI_ENDPOINT', 'https://api.openai.com/v1/chat/completions');
        $this->apiKey = env('OPENAI_API_KEY');
        $this->model  = env('OPENAI_MODEL', 'gpt-4o-mini');
    }

    public function chat($message = '', $system = null, $maxTokens = 1000)
    {
        $messages = [];
        if ($system) {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $data = [
            'model'      => $this->model,
            'messages'   => $messages,
            'max_tokens' => $maxTokens,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response  = curl_exec($ch);
        $curlError = curl_errno($ch) ? curl_error($ch) : null;
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception('Curl error: ' . $curlError);
        }

        $json = json_decode($response, true);

        if ($httpCode !== 200 || !isset($json['choices'][0]['message']['content'])) {
            $errorMsg = isset($json['error']['message'])
                ? '[' . $httpCode . '] ' . $json['error']['message']
                : 'OpenAI HTTP ' . $httpCode . ' – ' . mb_substr($response ?? '', 0, 300);
            throw new \Exception($errorMsg);
        }

        return $json['choices'][0]['message']['content'];
    }
}
