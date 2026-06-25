-- Property documents (rules, regulations, policies)
CREATE TABLE IF NOT EXISTS property_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    property_id INT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('rules', 'regulations', 'policy', 'other') DEFAULT 'rules',
    version VARCHAR(20) DEFAULT '1.0',
    is_active BOOLEAN DEFAULT TRUE,
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    INDEX idx_owner_id (owner_id),
    INDEX idx_property_id (property_id),
    INDEX idx_type (type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;