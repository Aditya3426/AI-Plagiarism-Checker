# AI Plagiarism Checker

A powerful, web-based plagiarism detection system built with PHP, MySQL, and optional AI integration. Uses multiple similarity algorithms including Cosine Similarity, TF-IDF, Jaccard Index, and more.

## Features

- **Multiple Detection Algorithms**
  - Cosine Similarity
  - TF-IDF (Term Frequency-Inverse Document Frequency)
  - PHP Similar Text
  - Jaccard Index
  - Dice Coefficient
  - Combined scoring (weighted average)

- **Text Processing**
  - Preprocessing (lowercase, remove punctuation)
  - Stop word removal
  - Sentence tokenization
  - N-gram extraction

- **File Support**
  - Plain text (.txt)
  - Word documents (.doc, .docx)
  - PDF files (.pdf)

- **AI Integration** (Optional)
  - OpenAI API
  - Google Gemini API
  - Hugging Face Models

- **Modern UI**
  - MDBootstrap 5
  - Responsive design
  - Real-time progress
  - Downloadable reports

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- PHP extensions: php-mysql, php-xml, php-zip
- Web server (Apache/Nginx)

## Installation

### 1. Database Setup

1. Create a MySQL database named `plagiarism_checker`
2. Import the `database.sql` file:

```bash
mysql -u root -p plagiarism_checker < database.sql
```

Or use phpMyAdmin to import the SQL file.

### 2. Configure Database

Edit `config/database.php` and update the credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'plagiarism_checker');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### 3. Web Server Configuration

- Point your web server to the project directory
- Ensure the `uploads/` directory is writable
- Enable URL rewriting (optional)

### 4. (Optional) AI Configuration

To enable AI features, set environment variables:

```bash
# OpenAI
setx OPENAI_API_KEY "your-api-key"

# Google Gemini
setx GEMINI_API_KEY "your-api-key"

# Hugging Face
setx HUGGINGFACE_API_KEY "your-api-key"
```

Or update the constants in `includes/ai_integration.php`.

## Usage

1. Open the application in a web browser
2. Choose input method:
   - Paste text directly
   - Upload a file (.txt, .doc, .docx, .pdf)
3. Configure settings:
   - Select algorithm
   - Adjust threshold (0-100%)
   - Enable AI analysis (if configured)
4. Click "Check Plagiarism"
5. View results:
   - Similarity score
   - Matched sentences
   - Source matches
   - Text statistics

## Project Structure

```
.
├── index.php              # Main frontend
├── process.php            # Backend processing
├── database.sql           # Database schema
├── config/
│   ├── config.php        # Application config
│   └── database.php      # Database connection
├── includes/
│   ├── preprocessing.php # Text preprocessing
│   ├── similarity.php    # Similarity algorithms
│   └── ai_integration.php # AI API integration
├── uploads/              # File uploads (create manually)
└── README.md
```

## Algorithms Explained

### Cosine Similarity
Measures the cosine angle between two text vectors. Best for comparing document similarity.

### TF-IDF
Term Frequency-Inverse Document Frequency. Weights terms by their importance across documents.

### Jaccard Index
Measures the intersection over union of word sets. Good for detecting copied text.

### Similar Text
PHP's built-in function. Returns percentage of similarity between two strings.

## Similarity Thresholds

| Score | Classification | Action |
|-------|---------------|--------|
| 0-40% | Low | Generally acceptable |
| 40-70% | Medium | Review and cite sources |
| 70-100% | High | Likely plagiarism |

## Adding Custom Sources

Add source documents to compare against:

```php
// In process.php or via database
addSourceDocument(
    'Document Title',
    'Content to compare against...',
    'custom', // source_type
    'https://example.com/source' // optional URL
);
```

## Performance Tips

1. **Enable Caching**: Set `ENABLE_CACHE` to true in config
2. **Optimize Database**: Add indexes to frequently queried columns
3. **Use CDN**: Serve static assets from a CDN
4. **Limit File Size**: Set reasonable upload limits

## Security Considerations

- Sanitize all user inputs
- Use CSRF protection
- Limit upload file types
- Rate limit API requests
- Never expose API keys in client-side code

## Troubleshooting

### No results appearing
- Check database connection
- Verify source documents exist
- Check error logs

### High false positives
- Increase threshold value
- Add more source documents
- Adjust preprocessing settings

### File upload not working
- Check file permissions on uploads/
- Verify PHP upload settings
- Check file size limits

## License

MIT License - Feel free to use and modify for your projects.

## Credits

- [MDBootstrap](https://mdbootstrap.com/) - UI Framework
- [Font Awesome](https://fontawesome.com/) - Icons
- [OpenAI](https://openai.com/) - AI Integration
- [Google Gemini](https://gemini.google.com/) - AI Integration
- [Hugging Face](https://huggingface.co/) - ML Models
