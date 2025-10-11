29 Sept 2025
Progress
- membuat database untuk week, year, plant, place, menu.
- membuat Koneksi database ada di config
- membuat ajax untuk get data form plant, place, menu ada di config.

30 Sept 2025
Tambahkan 

-   <?php include "../auth.php"; ?>
    <?php if ($_SESSION['role'] !== 'vendorkantin') { header("Location: ../form_login.php"); exit; } ?>

    Note : letakan code auth.php pada setiap file untuk menyimpan season start. untuk if diatas, menunjukan jika role nya tidak sesuai. contoh diatas untuk folder vendor ngambil data dari tabel vendorkantin. tinggal disesuaikan saja jika untuk tabel admin, maupun user. atau mau buat tabel baru untuk skema loginnya, misal PIC KANTIN, Dll.

Progress
    - Membuat skema login tiga pihak (User, Admin, Vendor)
    - Update halaman form_login.php

1 Oct 2025
 - hapus yarn. soalnya bentrok
 - push ke github untuk velce

 6 Oct 2025
 - memfungsikan tombol save order di form food order
 - menambahkan databasenya juga 
    (untuk db order menyimpan yang di sumbit form food order)
    (untuk db order_menus menyimpan data menu yang di sumbit form food order)
    yang masih bingung menampilkan week (dropdown) lalu isinya tabel hari apa saja yang dia select (pertanyaan dalam hati "apakah ketika save order maka yang tidak terselect juga sebaiknya disimpan???")
 - Menambahkan frontend history.php (belum include db)

8 Oct 2025
- Menambahkan halaman history + fungsi tabel
- menambahkan halaman menu + fungsi tabel (cuma untuk halaman upload gambar dan menu kantin belum di buat)
    Bungung juga weee ... nama menu ada di tabel menu, gambar menu ada di tabel menu_image
    Note : cari tau gimana caranya menambahkan nama + gambar di tabel yang berbeda dengan satu form sumbit (akan di taro di halaman adminmaster)
- memperbaiki navbar untuk nama user yang sedang login

11 Oct 2025
- Menambahkan fitur edit profil
- Memperbaharui redirect
- Menambahkan halaman index awal user

