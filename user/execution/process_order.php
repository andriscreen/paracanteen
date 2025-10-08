<?php
session_start();

// Pastikan Anda meng-include file db.php untuk koneksi
require_once '../config/db.php';  // Sesuaikan path jika perlu

// Periksa apakah user_id ada di session
if (!isset($_SESSION['user_id'])) {
    // Redirect ke halaman login jika user_id tidak ada
    header("Location: login.php");
    exit;
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $week_id = $_POST['week'];  // ID Week yang dipilih
    $year_id = $_POST['year'];  // ID Year yang dipilih
    $plant_id = $_POST['plant']; // ID Plant yang dipilih
    $place_id = $_POST['place']; // ID Place yang dipilih
    $shift_id = $_POST['shift']; // ID Shift yang dipilih
    $selected_menus = isset($_POST['menu_selected']) ? $_POST['menu_selected'] : []; // Array ID Menu yang dipilih

    // Mulai transaksi untuk memastikan atomisitas
    $conn->begin_transaction();  // Menggunakan $conn, bukan $mysqli

    try {
        // 1. Insert data ke tabel orders, termasuk user_id
        $stmt = $conn->prepare("INSERT INTO orders (week_id, year_id, plant_id, place_id, shift_id, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiiii", $week_id, $year_id, $plant_id, $place_id, $shift_id, $user_id);
        $stmt->execute();
        
        // Ambil ID order yang baru saja disimpan
        $order_id = $conn->insert_id;
        
        // 2. Insert data ke tabel order_menus (relasi antara order dan menu)
        if (!empty($selected_menus)) {
            $stmt = $conn->prepare("INSERT INTO order_menus (order_id, menu_id) VALUES (?, ?)");
        
            foreach ($selected_menus as $menu_id) {
                $stmt->bind_param("ii", $order_id, $menu_id);
                $stmt->execute();
            }
        }
        
        // Commit transaksi
        $conn->commit();
        
        // Redirect setelah berhasil
        header("Location: ../history.php");
        exit(); // Jangan lupa untuk menghentikan eksekusi script setelah redirect
    } catch (Exception $e) {
        // Rollback jika terjadi kesalahan
        $conn->rollback();
        echo "Terjadi kesalahan: " . $e->getMessage();
    }

    // Menutup statement dan koneksi
    $stmt->close();
    $conn->close();
}
?>
