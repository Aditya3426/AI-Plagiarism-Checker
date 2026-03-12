<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Plagiarism Checker</title>
    <!-- MDBootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .result-card {
            transition: transform 0.3s ease;
        }
        .result-card:hover {
            transform: translateY(-5px);
        }
        .similarity-score {
            font-size: 4rem;
            font-weight: bold;
        }
        .matched-sentence {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .source-item {
            border-left: 3px solid #667eea;
            padding: 10px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .progress {
            height: 25px;
            border-radius: 12px;
        }
        .progress-bar {
            border-radius: 12px;
            transition: width 1s ease;
        }
        .loading-spinner {
            display: none;
        }
        .loading-spinner.active {
            display: block;
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="text-white mb-3">
                <i class="fas fa-shield-alt me-2"></i>AI Plagiarism Checker
            </h1>
            <p class="text-white-50">Advanced text similarity detection with AI-powered analysis</p>
        </div>

        <!-- Main Input Card -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <form id="plagiarismForm" enctype="multipart/form-data">
                    <!-- Tabs -->
                    <ul class="nav nav-pills mb-4" id="inputTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="text-tab" data-mdb-toggle="pill" 
                                    data-mdb-target="#text-input" type="button" role="tab">
                                <i class="fas fa-pen me-2"></i>Paste Text
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="file-tab" data-mdb-toggle="pill" 
                                    data-mdb-target="#file-input" type="button" role="tab">
                                <i class="fas fa-file-upload me-2"></i>Upload File
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Text Input -->
                        <div class="tab-pane fade show active" id="text-input" role="tabpanel">
                            <div class="form-outline mb-4">
                                <textarea class="form-control" id="contentText" name="content" 
                                          rows="10" placeholder="Enter or paste your text here..."></textarea>
                                <label class="form-label">Text Content</label>
                            </div>
                        </div>

                        <!-- File Input -->
                        <div class="tab-pane fade" id="file-input" role="tabpanel">
                            <div class="mb-4">
                                <div class="file-upload-wrapper">
                                    <input type="file" class="form-control" id="documentFile" 
                                           name="document" accept=".txt,.doc,.docx,.pdf">
                                    <label class="form-label">Supported formats: TXT, DOC, DOCX, PDF</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Similarity Algorithm</label>
                            <select class="form-select" id="algorithm" name="algorithm">
                                <option value="cosine">Cosine Similarity</option>
                                <option value="tfidf">TF-IDF</option>
                                <option value="similar_text">PHP Similar Text</option>
                                <option value="jaccard">Jaccard Index</option>
                                <option value="combined" selected>Combined (Best)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Detection Threshold</label>
                            <input type="range" class="form-range" id="threshold" 
                                   name="threshold" min="0" max="100" value="30">
                            <small class="text-muted">Current: <span id="thresholdValue">30</span>%</small>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="useAI" name="use_ai">
                                <label class="form-check-label" for="useAI">
                                    <i class="fas fa-robot me-1"></i>Use AI Analysis
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                            <i class="fas fa-search me-2"></i>Check Plagiarism
                        </button>
                    </div>
                </form>

                <!-- Loading Spinner -->
                <div class="loading-spinner text-center mt-4" id="loadingSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Analyzing...</span>
                    </div>
                    <p class="mt-3 text-muted">Analyzing your text...</p>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" style="display: none;">
            <!-- Overall Score -->
            <div class="card mb-4 result-card">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-chart-pie me-2"></i>Plagiarism Analysis Result
                    </h4>
                    
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <div class="similarity-score" id="similarityScore">0%</div>
                            <p class="text-muted">Similarity Score</p>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label>Content Status</label>
                                <div class="progress">
                                    <div class="progress-bar" id="scoreProgress" role="progressbar" 
                                         style="width: 0%"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-align-left me-1"></i>Sentences: <strong id="totalSentences">0</strong></span>
                                <span><i class="fas fa-copy me-1"></i>Matched: <strong id="matchedSentences">0</strong></span>
                                <span><i class="fas fa-database me-1"></i>Sources: <strong id="sourcesFound">0</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Alert -->
                    <div class="alert mt-4" id="resultAlert" role="alert">
                        <!-- Dynamic content -->
                    </div>
                </div>
            </div>

            <!-- Matched Sentences -->
            <div class="card mb-4 result-card">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-highlighter me-2"></i>Matched Sentences
                    </h5>
                    <div id="matchedSentencesList">
                        <!-- Dynamic content -->
                    </div>
                </div>
            </div>

            <!-- Source Matches -->
            <div class="card mb-4 result-card">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-link me-2"></i>Source Matches
                    </h5>
                    <div id="sourceMatchesList">
                        <!-- Dynamic content -->
                    </div>
                </div>
            </div>

            <!-- Text Statistics -->
            <div class="card mb-4 result-card">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-chart-bar me-2"></i>Text Statistics
                    </h5>
                    <div class="row" id="textStats">
                        <!-- Dynamic content -->
                    </div>
                </div>
            </div>

            <!-- AI Analysis (if enabled) -->
            <div class="card mb-4 result-card" id="aiAnalysisCard" style="display: none;">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-robot me-2"></i>AI-Powered Analysis
                    </h5>
                    <div id="aiAnalysisContent">
                        <!-- Dynamic content -->
                    </div>
                </div>
            </div>

            <!-- Download Report -->
            <div class="text-center mb-4">
                <button class="btn btn-success btn-lg" id="downloadReport">
                    <i class="fas fa-download me-2"></i>Download Report
                </button>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row mt-5" id="featuresSection">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5>Fast Detection</h5>
                        <p class="text-muted">Multiple algorithms for quick and accurate plagiarism detection</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h5>AI-Powered</h5>
                        <p class="text-muted">Advanced AI integration for semantic similarity analysis</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="fas fa-database"></i>
                        </div>
                        <h5>Source Database</h5>
                        <p class="text-muted">Compare against a database of known sources and documents</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="text-center text-white mt-5">
            <p>&copy; 2024 AI Plagiarism Checker. All rights reserved.</p>
        </footer>
    </div>

    <!-- MDB JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
    
    <script>
        // Threshold slider
        document.getElementById('threshold').addEventListener('input', function(e) {
            document.getElementById('thresholdValue').textContent = e.target.value;
        });

        // Form submission
        document.getElementById('plagiarismForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('loadingSpinner');
            const resultsSection = document.getElementById('resultsSection');
            
            // Get form data
            const formData = new FormData(this);
            
            // Show loading
            submitBtn.disabled = true;
            spinner.classList.add('active');
            resultsSection.style.display = 'none';
            
            try {
                const response = await fetch('process.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    displayResults(result);
                    resultsSection.style.display = 'block';
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('An error occurred: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                spinner.classList.remove('active');
            }
        });

        function displayResults(result) {
            // Similarity Score
            const score = result.similarity_score * 100;
            document.getElementById('similarityScore').textContent = score.toFixed(1) + '%';
            
            // Progress bar
            const progressBar = document.getElementById('scoreProgress');
            progressBar.style.width = score + '%';
            
            // Color based on score
            if (score >= 70) {
                progressBar.className = 'progress-bar bg-danger';
            } else if (score >= 40) {
                progressBar.className = 'progress-bar bg-warning';
            } else {
                progressBar.className = 'progress-bar bg-success';
            }
            
            // Statistics
            document.getElementById('totalSentences').textContent = result.stats.total_sentences;
            document.getElementById('matchedSentences').textContent = result.stats.matched_sentences;
            document.getElementById('sourcesFound').textContent = result.matches.length;
            
            // Alert
            const alertDiv = document.getElementById('resultAlert');
            let alertClass, alertMessage;
            
            if (score >= 70) {
                alertClass = 'alert-danger';
                alertMessage = '<i class="fas fa-exclamation-triangle me-2"></i><strong>High Plagiarism Detected!</strong> This content shows significant similarity to other sources. Please review and cite your sources properly.';
            } else if (score >= 40) {
                alertClass = 'alert-warning';
                alertMessage = '<i class="fas fa-exclamation-circle me-2"></i><strong>Moderate Similarity Found.</strong> Some parts of your content may need additional citations or rewriting.';
            } else {
                alertClass = 'alert-success';
                alertMessage = '<i class="fas fa-check-circle me-2"></i><strong>Low Plagiarism Detected.</strong> Your content appears to be mostly original.';
            }
            
            alertDiv.className = 'alert ' + alertClass;
            alertDiv.innerHTML = alertMessage;
            
            // Matched Sentences
            const sentencesList = document.getElementById('matchedSentencesList');
            if (result.matched_sentences.length > 0) {
                sentencesList.innerHTML = result.matched_sentences.map(sentence => 
                    `<div class="matched-sentence">${escapeHtml(sentence.text)}</div>`
                ).join('');
            } else {
                sentencesList.innerHTML = '<p class="text-muted">No significant matches found.</p>';
            }
            
            // Source Matches
            const sourcesList = document.getElementById('sourceMatchesList');
            if (result.matches.length > 0) {
                sourcesList.innerHTML = result.matches.map(match => `
                    <div class="source-item">
                        <h6>${escapeHtml(match.source_title)}</h6>
                        <p class="mb-1">${escapeHtml(match.source_content)}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">Similarity: ${(match.average_score * 100).toFixed(1)}%</span>
                            <span class="badge bg-secondary">${match.level}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                sourcesList.innerHTML = '<p class="text-muted">No source matches found in database.</p>';
            }
            
            // Text Statistics
            const statsDiv = document.getElementById('textStats');
            statsDiv.innerHTML = `
                <div class="col-md-3 text-center">
                    <h4>${result.stats.word_count}</h4>
                    <small class="text-muted">Words</small>
                </div>
                <div class="col-md-3 text-center">
                    <h4>${result.stats.sentence_count}</h4>
                    <small class="text-muted">Sentences</small>
                </div>
                <div class="col-md-3 text-center">
                    <h4>${result.stats.char_count}</h4>
                    <small class="text-muted">Characters</small>
                </div>
                <div class="col-md-3 text-center">
                    <h4>${result.stats.unique_words}</h4>
                    <small class="text-muted">Unique Words</small>
                </div>
            `;
            
            // AI Analysis
            if (result.ai_analysis && result.ai_analysis.length > 0) {
                document.getElementById('aiAnalysisCard').style.display = 'block';
                const aiDiv = document.getElementById('aiAnalysisContent');
                aiDiv.innerHTML = result.ai_analysis.map(analysis => `
                    <div class="source-item">
                        <h6>${escapeHtml(analysis.source_title)}</h6>
                        <div class="d-flex justify-content-between">
                            <span>AI Score: <strong>${(analysis.ai_score * 100).toFixed(1)}%</strong></span>
                            <span class="badge bg-info">${analysis.provider}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                document.getElementById('aiAnalysisCard').style.display = 'none';
            }
            
            // Scroll to results
            resultsSection.scrollIntoView({ behavior: 'smooth' });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Download Report
        document.getElementById('downloadReport').addEventListener('click', function() {
            const score = document.getElementById('similarityScore').textContent;
            const reportContent = `
PLAGIARISM CHECK REPORT
========================
Generated: ${new Date().toLocaleString()}
Similarity Score: ${score}

STATISTICS
----------
Total Sentences: ${document.getElementById('totalSentences').textContent}
Matched Sentences: ${document.getElementById('matchedSentences').textContent}
Sources Found: ${document.getElementById('sourcesFound').textContent}

NOTE: This is a basic report. For full details, use the web interface.
            `;
            
            const blob = new Blob([reportContent], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'plagiarism-report.txt';
            a.click();
            URL.revokeObjectURL(url);
        });
    </script>
</body>
</html>
