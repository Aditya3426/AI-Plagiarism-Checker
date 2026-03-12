<?php
/**
 * Plagiarism Checker Processing Script
 * AI Plagiarism Checker
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/preprocessing.php';
require_once __DIR__ . '/includes/similarity.php';
require_once __DIR__ . '/includes/ai_integration.php';

// Set response type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

/**
 * Main plagiarism check function
 */
function checkPlagiarism($content, $algorithm = 'combined', $threshold = 30, $useAI = false) {
    $result = [
        'success' => true,
        'similarity_score' => 0,
        'matched_sentences' => [],
        'matches' => [],
        'stats' => [],
        'ai_analysis' => []
    ];
    
    // Get text content
    if (empty($content)) {
        return [
            'success' => false,
            'error' => 'No content provided'
        ];
    }
    
    // Preprocess the text
    $processedContent = preprocess($content, false);
    
    if (strlen($processedContent) < 20) {
        return [
            'success' => false,
            'error' => 'Content is too short for analysis'
        ];
    }
    
    // Get text statistics
    $result['stats'] = getTextStats($content);
    $result['stats']['char_count'] = strlen($content);
    
    // Get source documents from database
    $sources = getSourceDocuments();
    
    // If no sources in database, add some sample sources for demonstration
    if (empty($sources)) {
        $sources = getSampleSources();
    }
    
    // Process each sentence
    $sentences = splitIntoSentences($processedContent);
    $totalSentences = count($sentences);
    $matchedSentences = [];
    $allSimilarityScores = [];
    
    // Compare each sentence against sources
    foreach ($sentences as $sentenceIndex => $sentence) {
        if (strlen($sentence) < 15) continue;
        
        $sentenceMatches = [];
        
        foreach ($sources as $sourceIndex => $source) {
            $processedSource = preprocess($source['content'], false);
            $sourceSentences = splitIntoSentences($processedSource);
            
            // Compare against each source sentence
            foreach ($sourceSentences as $sourceSentence) {
                if (strlen($sourceSentence) < 15) continue;
                
                // Calculate similarity using different methods
                $cosineScore = calculateCosineSimilarity($sentence, $sourceSentence);
                $similarTextScore = calculateSimilarText($sentence, $sourceSentence);
                $jaccardScore = jaccardSimilarity($sentence, $sourceSentence);
                
                // Combined score (weighted average)
                $combinedScore = ($cosineScore * 0.4) + ($similarTextScore * 0.4) + ($jaccardScore * 0.2);
                
                if ($combinedScore >= ($threshold / 100)) {
                    $sentenceMatches[] = [
                        'source_index' => $sourceIndex,
                        'source_title' => $source['title'],
                        'score' => $combinedScore,
                        'cosine' => $cosineScore,
                        'similar_text' => $similarTextScore,
                        'jaccard' => $jaccardScore
                    ];
                    
                    $allSimilarityScores[] = $combinedScore;
                }
            }
        }
        
        // If sentence matches any source, add to matched sentences
        if (!empty($sentenceMatches)) {
            $bestMatch = array_reduce($sentenceMatches, function($carry, $item) {
                return ($item['score'] > $carry['score']) ? $item : $carry;
            }, $sentenceMatches[0]);
            
            $matchedSentences[] = [
                'text' => $sentence,
                'index' => $sentenceIndex,
                'score' => $bestMatch['score'],
                'source' => $bestMatch['source_title']
            ];
        }
    }
    
    // Calculate overall similarity score
    if (!empty($allSimilarityScores)) {
        // Use weighted average based on matched content
        $result['similarity_score'] = array_sum($allSimilarityScores) / count($allSimilarityScores);
        
        // Also factor in the percentage of matched sentences
        $sentenceMatchRate = count($matchedSentences) / max($totalSentences, 1);
        $result['similarity_score'] = ($result['similarity_score'] * 0.7) + ($sentenceMatchRate * 0.3);
    }
    
    $result['matched_sentences'] = $matchedSentences;
    $result['stats']['total_sentences'] = $totalSentences;
    $result['stats']['matched_sentences'] = count($matchedSentences);
    
    // Get unique sources that matched
    $sourceMatches = [];
    foreach ($matchedSentences as $match) {
        $sourceTitle = $match['source'];
        if (!isset($sourceMatches[$sourceTitle])) {
            $sourceMatches[$sourceTitle] = [
                'source_title' => $sourceTitle,
                'match_count' => 0,
                'max_score' => 0,
                'average_score' => 0,
                'source_content' => '',
                'level' => 'Low',
                'average_score' => 0
            ];
        }
        $sourceMatches[$sourceTitle]['match_count']++;
        $sourceMatches[$sourceTitle]['max_score'] = max($sourceMatches[$sourceTitle]['max_score'], $match['score']);
        $sourceMatches[$sourceTitle]['total_score'] = isset($sourceMatches[$sourceTitle]['total_score']) 
            ? $sourceMatches[$sourceTitle]['total_score'] + $match['score'] 
            : $match['score'];
    }
    
    // Calculate average and set level
    foreach ($sourceMatches as &$match) {
        $match['average_score'] = $match['total_score'] / $match['match_count'];
        $match['level'] = getSimilarityLevel($match['average_score']);
        
        // Get source content preview
        foreach ($sources as $source) {
            if ($source['title'] === $match['source_title']) {
                $match['source_content'] = substr($source['content'], 0, 200);
                break;
            }
        }
    }
    
    $result['matches'] = array_values($sourceMatches);
    
    // AI Analysis (if enabled and configured)
    if ($useAI && isAIConfigured()) {
        $result['ai_analysis'] = analyzeWithAI($processedContent, $sources);
    }
    
    // Save to database
    $docId = saveDocument(
        $content, 
        'user_upload', 
        'Check ' . date('Y-m-d H:i:s'), 
        $result['similarity_score'] * 100
    );
    
    return $result;
}

/**
 * Get sample sources for demonstration
 */
function getSampleSources() {
    return [
        [
            'id' => 1,
            'title' => 'Wikipedia - Introduction to Machine Learning',
            'content' => 'Machine learning is a subset of artificial intelligence that enables systems to learn and improve from experience without being explicitly programmed. It focuses on developing algorithms that can access data and use it to learn for themselves. The process of learning begins with observations or data, such as examples, direct experience, or instruction, in order to look for patterns in data and make better decisions in the future based on the examples that we provide.'
        ],
        [
            'id' => 2,
            'title' => 'Research Paper - Deep Learning Overview',
            'content' => 'Deep learning is part of a broader family of machine learning methods based on artificial neural networks with representation learning. Learning can be supervised, semi-supervised or unsupervised. Deep learning architectures such as deep neural networks, deep belief networks, deep reinforcement learning, recurrent neural networks and convolutional neural networks have been applied to fields including computer vision, speech recognition, natural language processing, audio recognition, social network filtering, machine translation, bioinformatics, drug design, medical image analysis, material inspection and board game programs, where they have produced results comparable to and in some cases surpassing human expert performance.'
        ],
        [
            'id' => 3,
            'title' => 'Article - What is Artificial Intelligence?',
            'content' => 'Artificial intelligence is intelligence demonstrated by machines, in contrast to the natural intelligence displayed by humans and animals. Leading AI textbooks define the field as the study of "intelligent agents": any device that perceives its environment and takes actions that maximize its chance of successfully achieving its goals. Some popular uses of AI include expert systems, natural language processing, speech recognition and machine vision.'
        ],
        [
            'id' => 4,
            'title' => 'Blog - Getting Started with Python',
            'content' => 'Python is a high-level, interpreted, general-purpose programming language. Its design philosophy emphasizes code readability with the use of significant indentation. Python is dynamically-typed and garbage-collected. It supports multiple programming paradigms, including structured, procedural, reflective, object-oriented and functional programming. It has a large and comprehensive standard library. Python is often described as a "batteries included" language due to its comprehensive standard library.'
        ],
        [
            'id' => 5,
            'title' => 'Tutorial - Web Development Basics',
            'content' => 'Web development is the work involved in developing websites and web applications for the internet or private networks. It encompasses a wide range of tasks from creating plain static pages to developing complex web applications, electronic businesses, and social network services. More broadly, it refers to the coding and markup that powers the functionality of the web, including HTML, CSS, and JavaScript. Web development can range from developing a simple single static page of plain text to complex web applications, electronic businesses, and social network services.'
        ]
    ];
}

/**
 * Handle file upload and extraction
 */
function handleFileUpload($file) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return null;
    }
    
    $content = '';
    $filename = $file['name'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    switch ($extension) {
        case 'txt':
            $content = file_get_contents($file['tmp_name']);
            break;
            
        case 'doc':
        case 'docx':
            // For DOCX files, use simple XML parsing
            if ($extension === 'docx') {
                $content = readDocx($file['tmp_name']);
            } else {
                // For older DOC format, return as-is (limited support)
                $content = file_get_contents($file['tmp_name']);
            }
            break;
            
        case 'pdf':
            // Note: Requires PDF extension or library
            if (function_exists('pdf2text')) {
                $content = pdf2text($file['tmp_name']);
            } else {
                // Basic extraction attempt
                $content = file_get_contents($file['tmp_name']);
            }
            break;
            
        default:
            return null;
    }
    
    return $content;
}

/**
 * Read DOCX file content
 */
function readDocx($filePath) {
    $content = '';
    
    // Open the DOCX file as a ZIP
    $zip = new ZipArchive();
    if ($zip->open($filePath) === true) {
        // Read the document.xml file
        $docXml = $zip->getFromName('word/document.xml');
        $zip->close();
        
        if ($docXml) {
            // Parse XML and extract text
            $xml = simplexml_load_string($docXml);
            $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            
            $textNodes = $xml->xpath('//w:t');
            foreach ($textNodes as $node) {
                $content .= (string)$node . ' ';
            }
        }
    }
    
    return trim($content);
}

// Main execution
try {
    $response = [
        'success' => false,
        'error' => 'Invalid request method'
    ];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get parameters
        $content = '';
        $algorithm = isset($_POST['algorithm']) ? $_POST['algorithm'] : 'combined';
        $threshold = isset($_POST['threshold']) ? intval($_POST['threshold']) : 30;
        $useAI = isset($_POST['use_ai']) && $_POST['use_ai'] === 'on';
        
        // Check if text content or file
        if (isset($_POST['content']) && !empty($_POST['content'])) {
            $content = $_POST['content'];
        } elseif (isset($_FILES['document']) && !empty($_FILES['document']['name'])) {
            $content = handleFileUpload($_FILES['document']);
            
            if ($content === null) {
                $response = [
                    'success' => false,
                    'error' => 'Unsupported file format or empty file'
                ];
                echo json_encode($response);
                exit;
            }
        }
        
        if (empty($content)) {
            $response = [
                'success' => false,
                'error' => 'No content provided. Please enter text or upload a file.'
            ];
            echo json_encode($response);
            exit;
        }
        
        // Run plagiarism check
        $response = checkPlagiarism($content, $algorithm, $threshold, $useAI);
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
