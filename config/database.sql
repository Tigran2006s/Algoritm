-- Обновляем статус заказов
ALTER TABLE orders
ADD COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending';

-- Обновляем существующие заказы, если нужно
UPDATE orders SET status = 'pending' WHERE status IS NULL; 