<?php
session_start();
// 1. Proteksi Halaman: Jika tidak ada session login_admin, tendang kembali ke login (index.php)
if (!isset($_SESSION['login_admin'])) {
    header("Location: ../index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// 3. Ambil total pendaftaran anggota baru yang berstatus 'pending' untuk indikator badge di sidebar
$query_pending = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM anggota WHERE status_akun = 'pending'");
$data_pending  = mysqli_fetch_assoc($query_pending);
$anggota_pending = $data_pending['total'] ?? 0;

// 4. Logika Sorting via Header Tabel
$sort_by    = $_GET['by'] ?? 'judul'; // Kolom target default
$sort_order = $_GET['order'] ?? 'ASC'; // Arah default

// Ambil arah sebaliknya untuk link klik berikutnya (Toggle ASC <=> DESC)
$next_order = ($sort_order == 'ASC') ? 'DESC' : 'ASC';

// Validasi kolom whitelist agar aman dari SQL Injection
$allowed_columns = ['judul', 'nama_kategori', 'penulis', 'nama_rak', 'stok_tersedia'];
$allowed_orders  = ['ASC', 'DESC'];

if (!in_array($sort_by, $allowed_columns)) { $sort_by = 'judul'; }
if (!in_array($sort_order, $allowed_orders)) { $sort_order = 'ASC'; }

// Pemetaan query pengurutan berdasarkan kolom yang dipilih
switch ($sort_by) {
    case 'nama_kategori':
        $orderby_sql = "ORDER BY k.nama_kategori $sort_order";
        break;
    case 'nama_rak':
        $orderby_sql = "ORDER BY r.nama_rak $sort_order";
        break;
    case 'penulis':
        $orderby_sql = "ORDER BY b.penulis $sort_order, b.tahun_terbit $sort_order";
        break;
    case 'stok_tersedia':
        $orderby_sql = "ORDER BY b.stok_tersedia $sort_order";
        break;
    default:
        $orderby_sql = "ORDER BY b.judul $sort_order";
        break;
}

// 5. Ambil Data Buku (Menggunakan urutan dinamis)
$query_buku = mysqli_query($mysqli, "SELECT b.*, k.nama_kategori, r.nama_rak 
                                     FROM buku b
                                     LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                                     LEFT JOIN rak r ON b.id_rak = r.id_rak
                                     $orderby_sql");

// Fungsi bantu untuk menampilkan icon panah sort yang aktif di header
function getSortIcon($column, $current_by, $current_order) {
    if ($column === $current_by) {
        return ($current_order === 'ASC') ? ' <i class="bi bi-caret-up-fill text-dark small"></i>' : ' <i class="bi bi-caret-down-fill text-dark small"></i>';
    }
    return ' <i class="bi bi-arrow-down-up text-muted opacity-50 small"></i>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PojokBaca Admin Workspace">
  <title>PojokBaca | Manajemen Katalog Buku</title>

  <link class="main-stylesheet" rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    th.sortable-header {
      cursor: pointer;
      white-space: nowrap;
      transition: background-color 0.2s;
    }
    th.sortable-header:hover {
      background-color: rgba(0, 0, 0, 0.04) !important;
    }
    th.sortable-header a {
      text-decoration: none;
      color: inherit;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }
  </style>
</head>

<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
      <div class="sidebar-header">
        <a class="brand-mark" href="dashboard.php" aria-label="PojokBaca dashboard">
          <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
          <span class="brand-copy">
            <span class="brand-title">PojokBaca</span>
            <span class="brand-subtitle">Administrator</span>
          </span>
        </a>
      </div>

      <nav class="sidebar-nav">
        <a class="nav-link" href="dashboard.php">
          <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
          <span class="nav-text">Dashboard</span>
        </a>
        
        <a class="nav-link active" href="katalog.php" aria-current="page">
          <span class="nav-icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
          <span class="nav-text">Data Buku</span>
        </a>
        
        <a class="nav-link" href="anggota.php">
          <span class="nav-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
          <span class="nav-text">Data Anggota</span>
          <?php if($anggota_pending > 0): ?>
            <span class="badge bg-warning text-dark ms-auto px-2 rounded-pill"><?= $anggota_pending; ?></span>
          <?php endif; ?>
        </a>
        
        <a class="nav-link" href="peminjaman.php">
          <span class="nav-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
          <span class="nav-text">Peminjaman</span>
        </a>
        
        <a class="nav-link" href="pengembalian.php">
          <span class="nav-icon"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></span>
          <span class="nav-text">Pengembalian</span>
        </a>
        
        <a class="nav-link" href="denda.php">
          <span class="nav-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
          <span class="nav-text">Data Denda</span>
        </a>

        <div class="nav-item-dropdown">
          <a class="nav-link dropdown-toggle" href="#menuKelola" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="menuKelola">
            <span class="nav-icon"><i class="bi bi-gear" aria-hidden="true"></i></span>
            <span class="nav-text">Kelola</span>
          </a>
          <div class="collapse ms-3" id="menuKelola">
            <a class="nav-link py-1 small" href="kelola/kategori.php"><i class="bi bi-tags me-2"></i>Kategori</a>
            <a class="nav-link py-1 small" href="kelola/penerbit.php"><i class="bi bi-building me-2"></i>Penerbit</a>
            <a class="nav-link py-1 small" href="kelola/rak.php"><i class="bi bi-bookshelf me-2"></i>Data Rak</a>
          </div>
        </div>
        
        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php">
          <span class="nav-icon"><i class="bi bi-box-arrow-left text-danger" aria-hidden="true"></i></span>
          <span class="nav-text fw-bold">Logout</span>
        </a>
      </nav>

      <div class="sidebar-user d-none">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
        <strong><?= htmlspecialchars($nama_admin); ?></strong>
        <small>Admin</small>
      </div>
      <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
        <strong><?= htmlspecialchars($nama_admin); ?></strong>
        <small>Admin</small>
      </div>
    </aside>

    <div class="admin-main">
      <nav class="navbar admin-navbar navbar-expand bg-white">
        <div class="container-fluid px-3 px-lg-4">
          <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
            <span></span><span></span><span></span>
          </button>

          <div class="navbar-actions ms-auto">
            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="avatar-img avatar-sm" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
                <span class="d-none d-sm-inline"><?= htmlspecialchars($nama_admin); ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
              </ul>
            </div>
          </div>
        </div>
      </nav>

      <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
          <div class="page-heading">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
              <div>
                <p class="eyebrow mb-1">Manajemen Koleksi</p>
                <h1 class="h3 mb-1">Katalog Buku</h1>
                <p class="text-muted mb-0">Kelola seluruh daftar pustaka, katalog, serta ketersediaan stok fisik buku.</p>
              </div>
            </div>
          </div>

          <section class="panel">
            <div class="panel-header d-flex flex-wrap justify-content-between align-items-center gap-3">
              <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-table" aria-hidden="true"></i><span>Koleksi Buku Perpustakaan</span></h2>
                <p class="text-muted mb-0">Klik nama kolom untuk mengurutkan atau gunakan pencarian instan.</p>
              </div>
              <div class="d-flex gap-2 build-actions">
                <input class="form-control form-control-sm table-search" type="search" placeholder="Cari judul, penulis, rak..." data-table-search="katalogBukuTable" aria-label="Search books" style="max-width: 220px;">
                <a href="buku-tambah.php" class="btn btn-sm btn-primary px-3 shadow-sm text-nowrap">
                  <i class="bi bi-plus-lg me-1"></i> Tambah Buku
                </a>
              </div>
            </div>
            
            <div class="table-responsive">
              <table class="table align-middle mb-0 table-hover" id="katalogBukuTable" data-searchable-table>
                <thead>
                  <tr>
                    <th class="sortable-header">
                      <a href="katalog.php?by=judul&order=<?= ($sort_by == 'judul') ? $next_order : 'ASC'; ?>">
                        Judul Buku <?= getSortIcon('judul', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="katalog.php?by=nama_kategori&order=<?= ($sort_by == 'nama_kategori') ? $next_order : 'ASC'; ?>">
                        Kategori <?= getSortIcon('nama_kategori', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="katalog.php?by=penulis&order=<?= ($sort_by == 'penulis') ? $next_order : 'ASC'; ?>">
                        Penulis / Tahun <?= getSortIcon('penulis', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="katalog.php?by=nama_rak&order=<?= ($sort_by == 'nama_rak') ? $next_order : 'ASC'; ?>">
                        Lokasi Rak <?= getSortIcon('nama_rak', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="katalog.php?by=stok_tersedia&order=<?= ($sort_by == 'stok_tersedia') ? $next_order : 'DESC'; ?>">
                        Status Stok <?= getSortIcon('stok_tersedia', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="text-end" style="min-width: 100px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  if (mysqli_num_rows($query_buku) > 0): 
                    while($buku = mysqli_fetch_assoc($query_buku)):
                      
                      $stok_total     = $buku['stok_total'] ?? 0;
                      $stok_tersedia  = $buku['stok_tersedia'] ?? 0; 

                      // Format stok murni angka ringkas Total/Tersedia (contoh: 5/4 atau 3/3)
                      if($stok_tersedia > 0) {
                          $status_badge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fs-7 fw-semibold">' . $stok_total . '/' . $stok_tersedia . '</span>';
                      } else {
                          $status_badge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 fs-7 fw-semibold">' . $stok_total . '/' . $stok_tersedia . '</span>';
                      }
                  ?>
                    <tr>
                      <td>
                        <div class="table-media">
                          <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
                          <span class="fw-semibold text-truncate" style="max-width: 230px;"><?= htmlspecialchars($buku['judul']); ?></span>
                        </div>
                      </td>
                      <td>
                        <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-medium">
                          <?= htmlspecialchars($buku['nama_kategori'] ?? 'Umum'); ?>
                        </span>
                      </td>
                      <td>
                        <div class="d-flex flex-column">
                          <span><?= htmlspecialchars($buku['penulis']); ?></span>
                          <small class="text-muted"><?= htmlspecialchars($buku['tahun_terbit']); ?></small>
                        </div>
                      </td>
                      <td>
                        <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                          <i class="bi bi-bookshelf text-secondary me-1"></i><?= htmlspecialchars($buku['nama_rak'] ?? 'Tanpa Rak'); ?>
                        </span>
                      </td>
                      <td><?= $status_badge; ?></td>
                      <td class="text-end">
                        <a href="buku.php?id=<?= $buku['id_buku']; ?>" class="btn btn-light btn-sm fw-medium border shadow-sm px-3">
                          <i class="bi bi-eye me-1"></i> Detail
                        </a>
                      </td>
                    </tr>
                  <?php 
                    endwhile; 
                  else: 
                  ?>
                    <tr>
                      <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-inboxes display-6 d-block mb-2"></i> Belum ada data koleksi buku di sistem.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </main>

      <footer class="admin-footer">
        <div class="container-fluid px-3 px-lg-4">
          <span>Copyright 2026 PojokBaca. All rights reserved.</span>
        </div>
      </footer>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>