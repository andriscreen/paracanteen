-- Tambah kolom total_kupon ke tabel users jika belum ada
ALTER TABLE users
ADD COLUMN total_kupon INT DEFAULT 0;

-- Buat tabel untuk mencatat perolehan kupon dari pemesanan makanan
CREATE TABLE kupon_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    jumlah_kupon INT NOT NULL,
    tanggal_dapat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    keterangan VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES food_orders(id)
);

-- Buat tabel untuk mencatat penukaran kupon
CREATE TABLE redemption_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    kupon_used INT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    redemption_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (item_id) REFERENCES redeem_items(id)
);

-- Trigger untuk menambah total_kupon saat ada perolehan kupon baru
DELIMITER //
CREATE TRIGGER after_kupon_insert
AFTER INSERT ON kupon_history
FOR EACH ROW
BEGIN
    UPDATE users 
    SET total_kupon = total_kupon + NEW.jumlah_kupon 
    WHERE id = NEW.user_id;
END;//

-- Trigger untuk mengurangi total_kupon saat ada penukaran
CREATE TRIGGER after_redemption_insert
AFTER INSERT ON redemption_history
FOR EACH ROW
BEGIN
    UPDATE users 
    SET total_kupon = total_kupon - NEW.kupon_used 
    WHERE id = NEW.user_id;
END;//
DELIMITER ;