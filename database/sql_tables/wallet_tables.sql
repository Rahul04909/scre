-- Add wallet_balance to centers table if it doesn't exist
-- Note: Check managed iteratively in PHP script, this file is for reference/manual run

-- New Table: Center Wallet Transactions
CREATE TABLE IF NOT EXISTS center_wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    center_id INT NOT NULL,
    transaction_id VARCHAR(100) NOT NULL COMMENT 'Razorpay Payment ID or Custom',
    razorpay_order_id VARCHAR(100) DEFAULT NULL,
    amount_credit DECIMAL(10,2) NOT NULL COMMENT 'The amount added to wallet',
    amount_paid DECIMAL(10,2) NOT NULL COMMENT 'The amount actually paid by center',
    royalty_percentage DECIMAL(5,2) NOT NULL COMMENT 'Royalty at time of tx',
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE
);
