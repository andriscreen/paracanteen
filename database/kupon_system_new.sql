-- Tabel kupon_history untuk mencatat perolehan kupon
CREATE TABLE kupon_history (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    order_id INT NOT NULL,
    jumlah_kupon INT NOT NULL,
    tanggal_dapat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    keterangan VARCHAR(255),
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel redemption_history untuk mencatat penukaran kupon
CREATE TABLE redemption_history (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    kupon_used INT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    redemption_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES redeem_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Trigger untuk menambah kupon saat ada order baru dengan kupon
DELIMITER //

CREATE TRIGGER after_order_menu_insert
AFTER INSERT ON order_menus
FOR EACH ROW
BEGIN
    DECLARE v_user_id INT UNSIGNED;
    
    -- Ambil user_id dari tabel orders
    SELECT user_id INTO v_user_id 
    FROM orders 
    WHERE id = NEW.order_id;
    
    -- Jika kolom kupon = 1, tambahkan ke kupon_history
    IF NEW.kupon = 1 THEN
        -- Tambahkan record ke kupon_history
        INSERT INTO kupon_history (user_id, order_id, jumlah_kupon, keterangan)
        VALUES (v_user_id, NEW.order_id, 1, 'Kupon dari pemesanan makanan');
        
        -- Update total_kupon di tabel users
        UPDATE users 
        SET total_kupon = total_kupon + 1 
        WHERE id = v_user_id;
    END IF;
END //

-- Trigger untuk kurangi kupon saat ada penukaran
CREATE TRIGGER after_redemption_insert
AFTER INSERT ON redemption_history
FOR EACH ROW
BEGIN
    UPDATE users 
    SET total_kupon = total_kupon - NEW.kupon_used 
    WHERE id = NEW.user_id;
END //

DELIMITER ;