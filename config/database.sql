-- Добавляем поля для платежей в таблицу orders
ALTER TABLE orders
ADD COLUMN payment_id VARCHAR(255) NULL,
ADD COLUMN status ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
ADD COLUMN payment_date DATETIME NULL;

-- Обновляем существующие заказы, если нужно
UPDATE orders SET status = 'pending' WHERE status IS NULL; 