-- Rent reminders log table
CREATE TABLE IF NOT EXISTS rent_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    tenant_id INT NOT NULL,
    house_id INT NOT NULL,
    month VARCHAR(7) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    days_before_due INT NOT NULL,
    sent_at DATETIME NOT NULL,
    status ENUM('sent', 'failed', 'pending') DEFAULT 'sent',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE,
    INDEX idx_owner_id (owner_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_month (month),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;