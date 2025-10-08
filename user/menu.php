<?php include "../auth.php"; ?>
<?php 
if ($_SESSION['role'] !== 'user') { 
    header("Location: ../form_login.php"); 
    exit; 
} 

// Koneksi ke database
include 'config/db.php';

// Ambil week_id dari filter
$selected_week = isset($_GET['week_id']) ? (int)$_GET['week_id'] : 0;

// Ambil daftar week untuk dropdown filter
$weeks_result = $conn->query("SELECT DISTINCT week_id FROM menu ORDER BY week_id ASC");

// Query daftar menu + gambar
if ($selected_week > 0) {
    $sql = "SELECT m.*, i.image_url 
            FROM menu m 
            LEFT JOIN menu_images i 
                ON m.week_id = i.week_id AND m.day = i.day
            WHERE m.week_id = $selected_week
            ORDER BY m.day ASC";
} else {
    $sql = "SELECT m.*, i.image_url 
            FROM menu m 
            LEFT JOIN menu_images i 
                ON m.week_id = i.week_id AND m.day = i.day
            ORDER BY m.week_id ASC, m.day ASC";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <title>Daftar Menu | ParaCanteen</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />

    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>

    <style>
      .menu-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
      }
      .menu-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        transition: 0.3s;
      }
      .menu-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
      }
      .menu-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
      }
      .menu-card-body {
        padding: 10px;
      }
      .menu-card-body h5 {
        font-size: 16px;
        margin-bottom: 5px;
      }
      .menu-card-body p {
        font-size: 14px;
        margin: 0;
        color: #666;
      }
      .filter-form {
        max-width: 250px;
        margin-bottom: 20px;
      }
    </style>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->
        <?php include 'layout/sidebar.php'; ?>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          <?php include 'layout/navbar.php'; ?>
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container mt-4">
              <div class="card shadow-sm p-4">
                <h4 class="mb-4"><i class="bx bx-food-menu"></i> Daftar Menu</h4>

                <!-- Filter Week -->
                <form class="filter-form" method="GET" action="">
                  <label for="week_id" class="form-label">Filter by Week:</label>
                  <select class="form-select" name="week_id" id="week_id" onchange="this.form.submit()">
                    <option value="0">All Weeks</option>
                    <?php while($week = $weeks_result->fetch_assoc()): ?>
                      <option value="<?= $week['week_id']; ?>" <?= ($week['week_id']==$selected_week) ? 'selected' : ''; ?>>
                        Week <?= $week['week_id']; ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </form>

                <!-- Menu List -->
                <div class="menu-container">
                  <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                      <div class="menu-card">
                        <img src="<?= $row['image_url'] ? '../' . $row['image_url'] : '../assets/img/menu/no-image.jpg'; ?>" 
                          alt="<?= htmlspecialchars($row['menu_name']); ?>">
                        <div class="menu-card-body">
                          <h5><?= htmlspecialchars($row['menu_name']); ?></h5>
                          <p>Day: <?= $row['day']; ?></p>
                          <p>Week: <?= $row['week_id']; ?></p>
                        </div>
                      </div>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <p class="text-muted">Tidak ada menu untuk week yang dipilih.</p>
                  <?php endif; ?>
                </div>

              </div>
            </div>
            <!-- / Content -->

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                <div class="mb-2 mb-md-0">
                  © <script>document.write(new Date().getFullYear());</script>,
                  Part of <a href="#" class="footer-link fw-bolder">ParagonCorp</a>
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
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>

    <!-- Vendors JS -->
    <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>
  </body>
</html>

<?php $conn->close(); ?>
