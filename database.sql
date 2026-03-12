-- AI Plagiarism Checker Database Schema
-- MySQL

-- Create database
CREATE DATABASE IF NOT EXISTS plagiarism_checker;
USE plagiarism_checker;

-- Main documents table for storing checked content
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    source VARCHAR(255) DEFAULT 'user_upload',
    title VARCHAR(255),
    similarity_score DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_content (content(100)),
    INDEX idx_created (created_at),
    INDEX idx_similarity (similarity_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Source documents table for comparison sources
CREATE TABLE IF NOT EXISTS source_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    source_type VARCHAR(50) DEFAULT 'custom',
    url VARCHAR(500),
    author VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_title (title),
    INDEX idx_content (content(100)),
    INDEX idx_source_type (source_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Similarity cache table for caching results
CREATE TABLE IF NOT EXISTS similarity_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text_hash VARCHAR(64) NOT NULL,
    compared_text_hash VARCHAR(64) NOT NULL,
    similarity_score DECIMAL(5,2) NOT NULL,
    algorithm VARCHAR(20) DEFAULT 'cosine',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_text_pair (text_hash, compared_text_hash),
    INDEX idx_text_hash (text_hash),
    INDEX idx_score (similarity_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User submissions tracking
CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    content_length INT,
    similarity_score DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip (ip_address),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample source documents for testing
INSERT INTO source_documents (title, content, source_type, url) VALUES
('Wikipedia - Introduction to Machine Learning', 
 'Machine learning is a subset of artificial intelligence that enables systems to learn and improve from experience without being explicitly programmed. It focuses on developing algorithms that can access data and use it to learn for themselves. The process of learning begins with observations or data, such as examples, direct experience, or instruction, in order to look for patterns in data and make better decisions in the future based on the examples that we provide.',
 'wikipedia', 'https://en.wikipedia.org/wiki/Machine_learning'),

('Research Paper - Deep Learning Overview',
 'Deep learning is part of a broader family of machine learning methods based on artificial neural networks with representation learning. Learning can be supervised, semi-supervised or unsupervised. Deep learning architectures such as deep neural networks, deep belief networks, deep reinforcement learning, recurrent neural networks and convolutional neural networks have been applied to fields including computer vision, speech recognition, natural language processing, audio recognition, social network filtering, machine translation, bioinformatics, drug design, medical image analysis, material inspection and board game programs.',
 'research', NULL),

('Article - What is Artificial Intelligence?',
 'Artificial intelligence is intelligence demonstrated by machines, in contrast to the natural intelligence displayed by humans and animals. Leading AI textbooks define the field as the study of intelligent agents: any device that perceives its environment and takes actions that maximize its chance of successfully achieving its goals. Some popular uses of AI include expert systems, natural language processing, speech recognition and machine vision.',
 'article', NULL),

('Blog - Getting Started with Python Programming',
 'Python is a high-level, interpreted, general-purpose programming language. Its design philosophy emphasizes code readability with the use of significant indentation. Python is dynamically-typed and garbage-collected. It supports multiple programming paradigms, including structured, procedural, reflective, object-oriented and functional programming. It has a large and comprehensive standard library.',
 'blog', NULL),

('Tutorial - Web Development Basics',
 'Web development is the work involved in developing websites and web applications for the internet or private networks. It encompasses a wide range of tasks from creating plain static pages to developing complex web applications, electronic businesses, and social network services. More broadly, it refers to the coding and markup that powers the functionality of the web, including HTML, CSS, and JavaScript.',
 'tutorial', NULL),

('Academic Paper - Neural Networks Introduction',
 'Neural networks are computing systems inspired by biological neural networks that constitute animal brains. Such systems learn to perform tasks by considering examples, generally without being programmed with task-specific rules. For example, in image recognition, they might learn to identify images that contain cats by analyzing example images that have been manually labeled as cat or no cat.',
 'academic', NULL),

('Book - Data Science Handbook',
 'Data science is a multidisciplinary field that uses scientific methods, processes, algorithms and systems to extract knowledge and insights from structured and unstructured data. It employs techniques and theories drawn from many fields within the context of mathematics, statistics, computer science, information science, and domain expertise.',
 'book', NULL),

('Documentation - SQL Basics',
 'SQL is a domain-specific language used in programming and designed for managing data held in a relational database management system, or for stream processing in a relational data stream management system. SQL offers advantages like simple syntax, powerful queries, and data integrity support. It is widely used in web applications and data analysis.',
 'documentation', NULL);

-- Sample queries:

-- Get all documents
-- SELECT * FROM documents ORDER BY created_at DESC LIMIT 100;

-- Get all source documents
-- SELECT * FROM source_documents;

-- Get documents with high similarity
-- SELECT * FROM documents WHERE similarity_score > 50 ORDER BY similarity_score DESC;

-- Add a new source document
-- INSERT INTO source_documents (title, content, source_type, url) VALUES ('Title', 'Content here...', 'custom', NULL);

-- Get statistics
-- SELECT COUNT(*) as total_documents, AVG(similarity_score) as avg_similarity FROM documents;
