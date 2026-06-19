<?php
session_start();
// 1. Proteksi Halaman: Pastikan yang masuk adalah admin
if (!isset($_SESSION['login_admin'])) {
    header("Location: ../index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// 3. Ambil total pendaftaran anggota baru yang berstatus 'pending' untuk badge sidebar
$query_pending = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM anggota WHERE status_akun = 'pending'");
$data_pending  = mysqli_fetch_assoc($query_pending);
$anggota_pending = $data_pending['total'] ?? 0;

// 4. Logika Sorting via Header Tabel (Nama Anggota & Status Akun)
$sort_by    = $_GET['by'] ?? 'id_anggota'; // Kolom target default
$sort_order = $_GET['order'] ?? 'DESC';     // Arah default

// Ambil arah sebaliknya untuk link klik berikutnya (Toggle ASC <=> DESC)
$next_order = ($sort_order == 'ASC') ? 'DESC' : 'ASC';

// Validasi komponen kolom whitelist untuk mencegah SQL Injection
$allowed_columns = ['nama_lengkap', 'status_akun', 'id_anggota'];
$allowed_orders  = ['ASC', 'DESC'];

if (!in_array($sort_by, $allowed_columns)) { $sort_by = 'id_anggota'; }
if (!in_array($sort_order, $allowed_orders)) { $sort_order = 'DESC'; }

// 5. Ambil data seluruh anggota dengan query sorting dinamis
$sql_anggota = "SELECT * FROM anggota ORDER BY $sort_by $sort_order";
$query_anggota = mysqli_query($mysqli, $sql_anggota);

// Fungsi bantu untuk menampilkan icon panah sort yang aktif di header tabel
function getSortIcon($column, $current_by, $current_order) {
    if ($column === $current_by) {
        return ($current_order === 'ASC') ? ' <i class="bi bi-caret-up-fill small"></i>' : ' <i class="bi bi-caret-down-fill small"></i>';
    }
    return ' <i class="bi bi-arrow-down-up text-muted opacity-50 small"></i>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PojokBaca | Data Anggota</title>
  
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
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
    /* Mengatur tata letak header panel agar input search rapi di kanan */
    .panel-header-custom {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 20px;
    }
    .search-box-custom {
      max-width: 300px;
      width: 100%;
    }
  </style>
</head>
<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar">
      <div class="sidebar-header">
        <a class="brand-mark" href="dashboard.php">
          <span class="brand-icon"><i class="bi bi-book-half"></i></span>
          <span class="brand-copy">
            <span class="brand-title">PojokBaca</span>
            <span class="brand-subtitle">Administrator</span>
          </span>
        </a>
      </div>
      <nav class="sidebar-nav">
        <a class="nav-link" href="dashboard.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span><span class="nav-text">Dashboard</span></a>
        <a class="nav-link" href="katalog.php"><span class="nav-icon"><i class="bi bi-journal-text"></i></span><span class="nav-text">Data Buku</span></a>
        <a class="nav-link active" href="anggota.php">
          <span class="nav-icon"><i class="bi bi-people"></i></span><span class="nav-text">Data Anggota</span>
          <?php if($anggota_pending > 0): ?>
            <span class="badge bg-warning text-dark ms-auto px-2 rounded-pill"><?= $anggota_pending; ?></span>
          <?php endif; ?>
        </a>
        <a class="nav-link" href="peminjaman.php"><span class="nav-icon"><i class="bi bi-arrow-left-right"></i></span><span class="nav-text">Peminjaman</span></a>
        <a class="nav-link" href="denda.php"><span class="nav-icon"><i class="bi bi-cash-coin"></i></span><span class="nav-text">Data Denda</span></a>
        
        <div class="nav-item-dropdown">
          <a class="nav-link dropdown-toggle" href="#menuKelola" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="menuKelola">
            <span class="nav-icon"><i class="bi bi-gear"></i></span><span class="nav-text">Kelola</span>
          </a>
          <div class="collapse ms-3" id="menuKelola">
            <a class="nav-link py-1 small" href="kategori.php"><i class="bi bi-tags me-2"></i>Kategori</a>
            <a class="nav-link py-1 small" href="penerbit.php"><i class="bi bi-building me-2"></i>Penerbit</a>
            <a class="nav-link py-1 small" href="rak.php"><i class="bi bi-bookshelf me-2"></i>Data Rak</a>
          </div>
        </div>

        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')"><span class="nav-icon"><i class="bi bi-box-arrow-left text-danger"></i></span><span class="nav-text fw-bold">Logout</span></a>
      </nav>
      
      <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="User">
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
            <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
              <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>

            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="avatar-img avatar-sm" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
                <span class="d-none d-sm-inline"><?= htmlspecialchars($nama_admin); ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
              </ul>
            </div>
          </div>
        </div>
      </nav>

      <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
          
          <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="bi bi-people"></i></span>
              <div>
                <p class="eyebrow mb-1">Manajemen Pengguna</p>
                <h1 class="h3 mb-1">Data Anggota Perpustakaan</h1>
                <p class="text-muted mb-0 font-sans-serif">Kelola hak akses, validasi pendaftaran, dan data profil anggota aktif.</p>
              </div>
            </div>
          </div>

          <div class="panel border-0 shadow-sm overflow-hidden p-4">
            
            <div class="panel-header-custom">
              <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-people" aria-hidden="true"></i><span>Daftar Seluruh Anggota</span></h2>
              </div>
              <div class="search-box-custom">
                <input class="form-control form-control-sm table-search" type="search" placeholder="Cari nama atau email..." data-table-search="anggotaTable" aria-label="Search members">
              </div>
            </div>

            <div class="table-responsive">
              <table class="table align-middle text-dark mb-0 table-hover" id="anggotaTable" data-searchable-table>
                <thead>
                  <tr>
                    <th style="width: 60px;">No</th>
                    <th class="sortable-header">
                      <a href="anggota.php?by=nama_lengkap&order=<?= ($sort_by == 'nama_lengkap') ? $next_order : 'ASC'; ?>">
                        Nama Anggota <?= getSortIcon('nama_lengkap', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th>Email</th>
                    <th>Nomor Telepon</th>
                    <th class="sortable-header">
                      <a href="anggota.php?by=status_akun&order=<?= ($sort_by == 'status_akun') ? $next_order : 'ASC'; ?>">
                        Status Akun <?= getSortIcon('status_akun', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="text-center" style="width: 220px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $no = 1;
                  if (mysqli_num_rows($query_anggota) > 0):
                    while ($row = mysqli_fetch_assoc($query_anggota)): 
                      $status = $row['status_akun'] ?? 'pending';
                  ?>
                    <tr>
                      <td><?= $no++; ?></td>
                      <td class="fw-semibold text-primary">
                        <div class="d-flex align-items-center gap-2">
                          <span class="brand-icon"><i class="bi bi-person-fill fs-4"></i></span>
                          <?= htmlspecialchars($row['nama_lengkap'] ?? ($row['nama'] ?? '')); ?>
                        </div>
                      </td>
                      <td><?= htmlspecialchars($row['email']); ?></td>
                      <td><?= htmlspecialchars($row['no_telepon'] ?? '-'); ?></td>
                      <td>
                        <?php if ($status === 'approved'): ?>
                          <span class="badge bg-success text-white px-2 py-1.5">Aktif</span>
                        <?php elseif ($status === 'pending'): ?>
                          <span class="badge bg-warning text-dark px-2 py-1.5">Ditunda</span>
                        <?php else: ?>
                          <span class="badge bg-danger text-white px-2 py-1.5">Ditolak</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                          <?php if ($status === 'pending'): ?>
                            <a href="anggota-status.php?id=<?= $row['id_anggota']; ?>&action=approve" class="btn btn-outline-success btn-sm px-2 py-1 fw-medium" title="Setujui Pendaftaran" onclick="return confirm('Setujui pendaftaran anggota ini?')">
                              <i class="bi bi-check-circle me-1"></i> Setujui
                            </a>
                            <a href="anggota-status.php?id=<?= $row['id_anggota']; ?>&action=reject" class="btn btn-outline-warning btn-sm px-2 py-1 fw-medium" title="Tolak Pendaftaran" onclick="return confirm('Tolak pendaftaran anggota ini?')">
                              <i class="bi bi-x-circle me-1"></i> Tolak
                            </a>
                          <?php else: ?>
                            <a href="anggota-status.php?id=<?= $row['id_anggota']; ?>&action=delete" class="btn btn-outline-danger btn-sm px-2 py-1 fw-medium" title="Hapus Permanen" onclick="return confirm('Apakah Anda yakin ingin menghapus data data anggota ini secara permanen?')">
                              <i class="bi bi-trash"></i> Hapus
                            </a>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php 
                    endwhile; 
                  else:
                  ?>
                    <tr>
                      <td colspan="6" class="text-center py-4 text-muted">Belum ada data anggota yang terdaftar.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

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