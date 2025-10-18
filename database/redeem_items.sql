-- Tabel untuk daftar barang yang bisa ditukar kupon

CREATE TABLE redeem_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kupon INT NOT NULL,
    gambar VARCHAR(255) NOT NULL,
    keterangan TEXT,
    aktif TINYINT(1) DEFAULT 1
);

-- Contoh data awal
INSERT INTO redeem_items (nama, kupon, gambar, keterangan) VALUES
('Beras', 5, '../assets/img/barang/beras.jpg', 'Beras premium kualitas terbaik 1kg'),
('Minyak Goreng', 3, '../assets/img/barang/minyak.jpg', 'Minyak goreng kemasan 1 liter'),
('Gula', 2, '../assets/img/barang/gula.jpg', 'Gula pasir murni 1kg'),
('Kopi Sachet', 1, '../assets/img/barang/kopi.jpg', 'Kopi instan sachet isi 10'),
('Teh Celup', 1, '../assets/img/barang/teh.jpg', 'Teh celup isi 25 sachet');