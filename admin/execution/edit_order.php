<?php
include "../config/db.php";
include "../../auth.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../form_login.php");
    exit;
}

$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    $_SESSION['error'] = "Order ID tidak valid!";
    header('Location: ../manage-user-order.php');
    exit;
}

// Get order data
$orderQuery = $conn->prepare("SELECT o.*, u.nama as user_nama, u.nip FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$orderQuery->bind_param("i", $order_id);
$orderQuery->execute();
$order = $orderQuery->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['error'] = "Order tidak ditemukan!";
    header('Location: ../manage-user-order.php');
    exit;
}

// Get order menus
$menusQuery = $conn->prepare("
    SELECT om.*, m.menu_name, m.day 
    FROM order_menus om 
    LEFT JOIN menu m ON om.menu_id = m.id 
    WHERE om.order_id = ?
");
$menusQuery->bind_param("i", $order_id);
$menusQuery->execute();
$orderMenus = $menusQuery->get_result();

// Get available weeks, plants, places, shifts
$weeks = $conn->query("SELECT * FROM week ORDER BY week_number");
$plants = $conn->query("SELECT * FROM plant");
$places = $conn->query("SELECT * FROM place");
$shifts = $conn->query("SELECT * FROM shift");

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $week_id = $_POST['week_id'];
    $year_id = $_POST['year_id'];
    $plant_id = $_POST['plant_id'];
    $place_id = $_POST['place_id'];
    $shift_id = $_POST['shift_id'];
    
    // Update order
    $updateOrder = $conn->prepare("UPDATE orders SET week_id = ?, year_id = ?, plant_id = ?, place_id = ?, shift_id = ? WHERE id = ?");
    $updateOrder->bind_param("iiiiii", $week_id, $year_id, $plant_id, $place_id, $shift_id, $order_id);
    
    if ($updateOrder->execute()) {
        $_SESSION['success'] = "Order berhasil diupdate!";
        header("Location: ../manage-user-order.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal mengupdate order!";
    }
}

// Handle menu update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_menus'])) {
    $conn->begin_transaction();
    
    try {
        // Delete existing menu selections
        $deleteMenus = $conn->prepare("DELETE FROM order_menus WHERE order_id = ?");
        $deleteMenus->bind_param("i", $order_id);
        $deleteMenus->execute();
        
        // Insert new menu selections
        if (isset($_POST['menus'])) {
            $insertMenu = $conn->prepare("INSERT INTO order_menus (order_id, menu_id, makan, kupon, libur) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($_POST['menus'] as $menu_id => $option) {
                $selectedOption = $option['option'] ?? '';
                
                $makan = ($selectedOption === 'makan') ? 1 : 0;
                $kupon = ($selectedOption === 'kupon') ? 1 : 0;
                $libur = ($selectedOption === 'libur') ? 1 : 0;
                
                $insertMenu->bind_param("iiiii", $order_id, $menu_id, $makan, $kupon, $libur);
                $insertMenu->execute();
            }
        }
        
        $conn->commit();
        $_SESSION['success'] = "Menu berhasil diupdate!";
        header("Location: edit_order.php?id=$order_id");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Gagal mengupdate menu: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html
  lang="id"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../../assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Edit Order - ParaCanteen</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="../../assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../../assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../../assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Page CSS -->
    <style>
      .table-responsive { 
        border-radius: 8px;
      }
      .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
      }
      .form-check-input {
        transform: scale(1.2);
      }
      .badge-order {
        background: rgba(255,255,255,0.2);
        font-size: 0.9em;
      }
    </style>

    <!-- Helpers -->
    <script src="../../assets/vendor/js/helpers.js"></script>
    <script src="../../assets/js/config.js"></script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Layout container -->
        <!-- Menu -->
         <?php include '../layout/sidebar.php'; ?>
        <!-- / Menu -->
        <div class="layout-page">
          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
                <div class="mb-4 order-0">
                  <div class="card">
                    <div class="d-flex align-items-end row">
                      <div class="col-sm-12">
                        <div class="card-body">
                          <!-- Header -->
                          <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title text-primary">
                              <i class="bx bx-edit"></i> Edit Order 
                              <span class="badge badge-order">#<?= $order_id ?></span>
                            </h4>
                            <a href="../manage-user-order.php" class="btn btn-secondary">
                              <i class="bx bx-arrow-back"></i> Kembali
                            </a>
                          </div>

                          <!-- Alert messages -->
                          <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                              <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                          <?php endif; ?>

                          <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                              <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                          <?php endif; ?>

                          <!-- Order Information -->
                          <div class="card mb-4">
                            <div class="card-header">
                              <h5 class="card-title mb-0 text-white">
                                <i class="bx bx-info-circle"></i> Informasi Order
                              </h5>
                            </div>
                            <div class="card-body">
                              <div class="row">
                                <div class="col-md-4">
                                  <p><strong>User:</strong> <?= htmlspecialchars($order['user_nama']) ?></p>
                                  <p><strong>NIP:</strong> <?= htmlspecialchars($order['nip']) ?></p>
                                </div>
                                <div class="col-md-4">
                                  <p><strong>Tanggal Order:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                                  <p><strong>Status:</strong> 
                                    <span class="badge bg-label-<?= $order['status'] == 'completed' ? 'success' : 'primary' ?>">
                                      <?= ucfirst($order['status']) ?>
                                    </span>
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>

                          <!-- Edit Order Form -->
                          <div class="card mb-4">
                            <div class="card-header">
                              <h5 class="card-title mb-0 text-white">
                                <i class="bx bx-cog"></i> Edit Detail Order
                              </h5>
                            </div>
                            <div class="card-body">
                              <form method="POST">
                                <div class="row g-3">
                                  <div class="col-md-6">
                                    <label for="week_id" class="form-label">Minggu</label>
                                    <select class="form-select" id="week_id" name="week_id" required>
                                      <option value="">Pilih Minggu</option>
                                      <?php while ($week = $weeks->fetch_assoc()): ?>
                                        <option value="<?= $week['id'] ?>" <?= $week['id'] == $order['week_id'] ? 'selected' : '' ?>>
                                          Minggu <?= $week['week_number'] ?>
                                        </option>
                                      <?php endwhile; ?>
                                    </select>
                                  </div>
                                  
                                  <div class="col-md-6">
                                    <label for="year_id" class="form-label">Tahun</label>
                                    <select class="form-select" id="year_id" name="year_id" required>
                                      <option value="1" <?= $order['year_id'] == 1 ? 'selected' : '' ?>>2025</option>
                                      <option value="2" <?= $order['year_id'] == 2 ? 'selected' : '' ?>>2026</option>
                                      <option value="3" <?= $order['year_id'] == 3 ? 'selected' : '' ?>>2027</option>
                                    </select>
                                  </div>
                                  
                                  <div class="col-md-6">
                                    <label for="plant_id" class="form-label">Plant</label>
                                    <select class="form-select" id="plant_id" name="plant_id" required>
                                      <option value="">Pilih Plant</option>
                                      <?php while ($plant = $plants->fetch_assoc()): ?>
                                        <option value="<?= $plant['id'] ?>" <?= $plant['id'] == $order['plant_id'] ? 'selected' : '' ?>>
                                          <?= htmlspecialchars($plant['name']) ?>
                                        </option>
                                      <?php endwhile; ?>
                                    </select>
                                  </div>
                                  
                                  <div class="col-md-6">
                                    <label for="place_id" class="form-label">Place</label>
                                    <select class="form-select" id="place_id" name="place_id" required>
                                      <option value="">Pilih Place</option>
                                      <?php while ($place = $places->fetch_assoc()): ?>
                                        <option value="<?= $place['id'] ?>" <?= $place['id'] == $order['place_id'] ? 'selected' : '' ?>>
                                          <?= htmlspecialchars($place['name']) ?>
                                        </option>
                                      <?php endwhile; ?>
                                    </select>
                                  </div>
                                  
                                  <div class="col-md-6">
                                    <label for="shift_id" class="form-label">Shift</label>
                                    <select class="form-select" id="shift_id" name="shift_id">
                                      <option value="">Pilih Shift</option>
                                      <?php while ($shift = $shifts->fetch_assoc()): ?>
                                        <option value="<?= $shift['id'] ?>" <?= $shift['id'] == $order['shift_id'] ? 'selected' : '' ?>>
                                          <?= htmlspecialchars($shift['nama_shift']) ?>
                                        </option>
                                      <?php endwhile; ?>
                                    </select>
                                  </div>
                                  
                                  <div class="col-12">
                                    <button type="submit" name="update_order" class="btn btn-primary">
                                      <i class="bx bx-save"></i> Update Order
                                    </button>
                                  </div>
                                </div>
                              </form>
                            </div>
                          </div>

                          <!-- Edit Menus Form -->
                          <div class="card">
                            <div class="card-header">
                              <h5 class="card-title mb-0 text-white">
                                <i class="bx bx-food-menu"></i> Edit Menu Selection
                              </h5>
                            </div>
                            <div class="card-body">
                              <form method="POST">
                                <?php
                                // Get menus for the selected week
                                $weekMenus = $conn->prepare("SELECT * FROM menu WHERE week_id = ? ORDER BY FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");
                                $weekMenus->bind_param("i", $order['week_id']);
                                $weekMenus->execute();
                                $menus = $weekMenus->get_result();
                                
                                // Create array of current menu selections
                                $currentSelections = [];
                                while ($menu = $orderMenus->fetch_assoc()) {
                                  $currentSelections[$menu['menu_id']] = [
                                    'makan' => $menu['makan'],
                                    'kupon' => $menu['kupon'],
                                    'libur' => $menu['libur']
                                  ];
                                }
                                ?>
                                
                                <div class="table-responsive">
                                  <table class="table table-striped">
                                    <thead class="table-light">
                                      <tr>
                                        <th>Hari</th>
                                        <th>Menu</th>
                                        <th class="text-center">Makan</th>
                                        <th class="text-center">Kupon</th>
                                        <th class="text-center">Libur</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($menu = $menus->fetch_assoc()): 
                                            $current = $currentSelections[$menu['id']] ?? ['makan' => 0, 'kupon' => 0, 'libur' => 0];
                                            
                                            // Tentukan nilai yang terpilih
                                            $selectedOption = '';
                                            if ($current['makan']) $selectedOption = 'makan';
                                            if ($current['kupon']) $selectedOption = 'kupon';
                                            if ($current['libur']) $selectedOption = 'libur';
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-label-primary"><?= $menu['day'] ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($menu['menu_name']) ?></td>
                                            <td class="text-center">
                                                <input type="radio" name="menus[<?= $menu['id'] ?>][option]" value="makan" 
                                                    <?= $selectedOption == 'makan' ? 'checked' : '' ?> class="form-check-input">
                                            </td>
                                            <td class="text-center">
                                                <input type="radio" name="menus[<?= $menu['id'] ?>][option]" value="kupon" 
                                                    <?= $selectedOption == 'kupon' ? 'checked' : '' ?> class="form-check-input">
                                            </td>
                                            <td class="text-center">
                                                <input type="radio" name="menus[<?= $menu['id'] ?>][option]" value="libur" 
                                                    <?= $selectedOption == 'libur' ? 'checked' : '' ?> class="form-check-input">
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                  </table>
                                </div>
                                
                                <div class="mt-3">
                                  <button type="submit" name="update_menus" class="btn btn-success">
                                    <i class="bx bx-save"></i> Update Menu Selection
                                  </button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- / Content -->

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                <div class="mb-2 mb-md-0">
                  ©
                  <script>
                    document.write(new Date().getFullYear());
                  </script>
                  , Part of
                  <a href="#" target="_blank" class="footer-link fw-bolder">ParagonCorp</a>
                </div>
              </div>
            </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="../../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../assets/vendor/libs/popper/popper.js"></script>
    <script src="../../assets/vendor/js/bootstrap.js"></script>
    <script src="../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="../../assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="../../assets/js/main.js"></script>

    <!-- Page JS -->
    <script>
      // Auto refresh menus when week changes
      document.getElementById('week_id').addEventListener('change', function() {
        // You can add AJAX functionality here to load menus based on selected week
        console.log('Week changed to: ' + this.value);
      });
    </script>
  </body>
</html>