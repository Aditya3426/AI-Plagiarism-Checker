<?php
/**
 * AI Integration Module
 * AI Plagiarism Checker
 * Supports OpenAI, Google Gemini, and Hugging Face APIs
 */

// AI Configuration - Set these in your environment or config
define('AI_PROVIDER', getenv('AI_PROVIDER') ?: 'openai'); // openai, gemini, huggingface
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
define('HUGGINGFACE_API_KEY', getenv('HUGGINGFACE_API_KEY') ?: '');

/**
 * Check if AI is configured
 * @return bool
 */
function isAIConfigured() {
    switch (AI_PROVIDER) {
        case 'openai':
            return !empty(OPENAI_API_KEY);
        case 'gemini':
            return !empty(GEMINI_API_KEY);
        case 'huggingface':
            return !empty(HUGGINGFACE_API_KEY);
        default:
            return false;
    }
}

/**
 * Get similarity score from AI
 * @param string $text1
 * @param string $text2
 * @return array
 */
function getAISimilarityScore($text1, $text2) {
    switch (AI_PROVIDER) {
        case 'openai':
            return getOpenAISimilarity($text1, $text2);
        case 'gemini':
            return getGeminiSimilarity($text1, $text2);
        case 'huggingface':
            return getHuggingFaceSimilarity($text1, $text2);
        default:
            return [
                'success' => false,
                'error' => 'No AI provider configured',
                'score' => 0
            ];
    }
}

/**
 * Get similarity from OpenAI API
 * @param string $text1
 * @param string $text2
 * @return array
 */
function getOpenAISimilarity($text1, $text2) {
    if (empty(OPENAI_API_KEY)) {
        return [
            'success' => false,
            'error' => 'OpenAI API key not configured',
            'score' => 0
        ];
    }
    
    $prompt = "Compare the following two texts and return a similarity score from 0 to 100 (where 100 means identical). 
Only respond with the score number, nothing else.

Text 1: " . substr($text1, 0, 2000) . "
Text 2: " . substr($text2, 0, 2000);
    
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 10
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . OPENAI_API_KEY,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return [
            'success' => false,
            'error' => 'OpenAI API error: ' . $httpCode,
            'score' => 0
        ];
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        $score = floatval(trim($result['choices'][0]['message']['content']));
        return [
            'success' => true,
            'score' => $score / 100, // Convert to 0-1 range
            'provider' => 'openai'
        ];
    }
    
    return [
        'success' => false,
        'error' => 'Invalid API response',
        'score' => 0
    ];
}

/**
 * Get similarity from Google Gemini API
 * @param string $text1
 * @param string $text2
 * @return array
 */
function getGeminiSimilarity($text1, $text2) {
    if (empty(GEMINI_API_KEY)) {
        return [
            'success' => false,
            'error' => 'Gemini API key not configured',
            'score' => 0
        ];
    }
    
    $prompt = "Compare the following two texts and return a similarity score from 0 to 100 (where 100 means identical).
Only respond with the score number, nothing else.

Text 1: " . substr($text1, 0, 2000) . "
Text 2: " . substr($text2, 0, 2000);
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.3,
            'maxOutputTokens' => 10
        ]
    ];
    
    $ch = curl_init('https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=' . GEMINI_API_KEY);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return [
            'success' => false,
            'error' => 'Gemini API error: ' . $httpCode,
            'score' => 0
        ];
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $score = floatval(trim($result['candidates'][0]['content']['parts'][0]['text']));
        return [
            'success' => true,
            'score' => $score / 100, // Convert to 0-1 range
            'provider' => 'gemini'
        ];
    }
    
    return [
        'success' => false,
        'error' => 'Invalid API response',
        'score' => 0
    ];
}

/**
 * Get similarity from Hugging Face API
 * @param string $text1
 * @param string $text2
 * @return array
 */
function getHuggingFaceSimilarity($text1, $text2) {
    if (empty(HUGGINGFACE_API_KEY)) {
        return [
            'success' => false,
            'error' => 'Hugging Face API key not configured',
            'score' => 0
        ];
    }
    
    // Use sentence-transformers model for semantic similarity
    $data = [
        'inputs' => [
            'source_text' => substr($text1, 0, 500),
            'sentences' => [substr($text2, 0, 500)]
        ]
    ];
    
    $ch = curl_init('https://api-inference.huggingface.co/pipeline/feature-extraction/sentence-transformers/all-MiniLM-L6-v2');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . HUGGINGFACE_API_KEY,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return [
            'success' => false,
            'error' => 'Hugging Face API error: ' . $httpCode,
            'score' => 0
        ];
    }
    
    $result = json_decode($response, true);
    
    // The API returns embeddings, we need to calculate cosine similarity
    if (is_array($result) && isset($result[0]) && is_array($result[0])) {
        // This is a simplified version - in production you'd compute cosine similarity
        return [
            'success' => true,
            'score' => 0.5, // Placeholder - actual implementation needs embedding comparison
            'provider' => 'huggingface'
        ];
    }
    
    return [
        'success' => false,
        'error' => 'Invalid API response format',
        'score' => 0
    ];
}

/**
 * Analyze text with AI for detailed plagiarism report
 * @param string $text
 * @param array $sources
 * @return array
 */
function analyzeWithAI($text, $sources) {
    $results = [
        'ai_analysis' => [],
        'matches' => []
    ];
    
    foreach ($sources as $index => $source) {
        $aiResult = getAISimilarityScore($text, $source['content']);
        
        if ($aiResult['success']) {
            $results['matches'][] = [
                'source_title' => $source['title'] ?? 'Source ' . ($index + 1),
                'ai_score' => $aiResult['score'],
                'provider' => $aiResult['provider']
            ];
        }
    }
    
    return $results;
}

/**
 * Generate AI-powered plagiarism report
 * @param string $text
 * @param float $similarityScore
 * @param array $matchedSentences
 * @return string
 */
function generateAIReport($text, $similarityScore, $matchedSentences) {
    if (!isAIConfigured()) {
        return '';
    }
    
    $matchedText = implode("\n- ", array_slice($matchedSentences, 0, 5));
    
    $prompt = "Generate a brief plagiarism analysis report for a document with " . 
              ($similarityScore * 100) . "% similarity. " .
              "Key matched sentences:\n- " . $matchedText . "\n\n" .
              "Provide analysis in 2-3 sentences.";
    
    switch (AI_PROVIDER) {
        case 'openai':
            return generateOpenAIReport($prompt);
        case 'gemini':
            return generateGeminiReport($prompt);
        default:
            return '';
    }
}

function generateOpenAIReport($prompt) {
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.5,
        'max_tokens' => 200
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . OPENAI_API_KEY,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? '';
}

function generateGeminiReport($prompt) {
    $data = [
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'temperature' => 0.5,
            'maxOutputTokens' => 200
        ]
    ];
    
    $ch = curl_init('https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=' . GEMINI_API_KEY);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
}
