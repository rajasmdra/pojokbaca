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

// 4. Logika Sorting via Header Tabel
$sort_by    = $_GET['by'] ?? 'status_nyata'; 
$sort_order = $_GET['order'] ?? 'ASC';       

$next_order = ($sort_order == 'ASC') ? 'DESC' : 'ASC';
$allowed_columns = ['nama_lengkap', 'judul', 'total_tagihan', 'status_nyata'];
$allowed_orders  = ['ASC', 'DESC'];

if (!in_array($sort_by, $allowed_columns)) { $sort_by = 'status_nyata'; }
if (!in_array($sort_order, $allowed_orders)) { $sort_order = 'ASC'; }

$orderby_sql = "ORDER BY $sort_by $sort_order";

// Hitung Anggota Baru yang berstatus 'pending' (Butuh Persetujuan)
$query_pending = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM anggota WHERE status_akun = 'pending'");
$data_pending  = mysqli_fetch_assoc($query_pending);
$anggota_pending = $data_pending['total'] ?? 0;

// ==========================================================
// 5. QUERY UNION: TUNGGAKAN AKTIF + HISTORY DENDA LUNAS
// ==========================================================
$sql_union = "
    (
        -- KONDISI 1: Buku Sudah Kembali tapi ada denda lama yang tertinggal (belum bayar)
        SELECT 
            d.id_denda,
            p.id_peminjaman,
            a.nama_lengkap,
            b.judul,
            IF(d.jumlah_denda IS NULL OR d.jumlah_denda = 0, (DATEDIFF(p.tgl_kembali, p.tgl_jatuh_tempo) * 5000), d.jumlah_denda) AS total_tagihan,
            'Belum dibayar' AS tipe_denda,
            'belum_bayar' AS status_nyata
        FROM denda d
        INNER JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman
        INNER JOIN anggota a ON p.id_anggota = a.id_anggota
        INNER JOIN buku b ON p.id_buku = b.id_buku
        WHERE d.status_denda = 'belum_bayar' 
          AND p.status = 'kembali'
    )
    UNION ALL
    (
        -- KONDISI 2: Denda Berjalan secara Real-time (Buku belum kembali / masih dipinjam)
        SELECT 
            d.id_denda AS id_denda,
            p.id_peminjaman,
            a.nama_lengkap,
            b.judul,
            (DATEDIFF(CURDATE(), p.tgl_jatuh_tempo) * 5000) AS total_tagihan,
            'Belum dibayar' AS tipe_denda,
            'belum_bayar' AS status_nyata
        FROM peminjaman p
        INNER JOIN anggota a ON p.id_anggota = a.id_anggota
        INNER JOIN buku b ON p.id_buku = b.id_buku
        LEFT JOIN denda d ON p.id_peminjaman = d.id_peminjaman
        WHERE p.status = 'dipinjam' 
          AND CURDATE() > p.tgl_jatuh_tempo
          AND (d.status_denda IS NULL OR d.status_denda = 'belum_bayar')
    )
    UNION ALL
    (
        -- KONDISI 3: HISTORY / RIWAYAT DENDA YANG SUDAH LUNAS
        SELECT 
            d.id_denda,
            p.id_peminjaman,
            a.nama_lengkap,
            b.judul,
            d.jumlah_denda AS total_tagihan,
            'Lunas' AS tipe_denda,
            'lunas' AS status_nyata
        FROM denda d
        INNER JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman
        INNER JOIN anggota a ON p.id_anggota = a.id_anggota
        INNER JOIN buku b ON p.id_buku = b.id_buku
        WHERE d.status_denda = 'lunas'
    )
    $orderby_sql";

$query_denda = mysqli_query($mysqli, $sql_union);

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
  <title>PojokBaca | Data Denda</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    th.sortable-header { cursor: pointer; white-space: nowrap; transition: background-color 0.2s; }
    th.sortable-header:hover { background-color: rgba(0, 0, 0, 0.04) !important; }
    th.sortable-header a { text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
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
        <a class="nav-link" href="anggota.php">
          <span class="nav-icon"><i class="bi bi-people"></i></span><span class="nav-text">Data Anggota</span>
          <?php if($anggota_pending > 0): ?>
            <span class="badge bg-warning text-dark ms-auto px-2 rounded-pill"><?= $anggota_pending; ?></span>
          <?php endif; ?>
        </a>
        <a class="nav-link" href="peminjaman.php"><span class="nav-icon"><i class="bi bi-arrow-left-right"></i></span><span class="nav-text">Peminjaman</span></a>
        <a class="nav-link active" href="denda.php"><span class="nav-icon"><i class="bi bi-cash-coin"></i></span><span class="nav-text">Data Denda</span></a>
        
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
        <a class="nav-link text-danger" href="../logout.php"><span class="nav-icon"><i class="bi bi-box-arrow-left text-danger"></i></span><span class="nav-text fw-bold">Logout</span></a>
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
                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
              </ul>
            </div>
          </div>
        </div>
      </nav>

      <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
          <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="bi bi-cash-coin"></i></span>
              <div>
                <p class="eyebrow mb-1">Keuangan & Sanksi</p>
                <h1 class="h3 mb-1">Administrasi Tunggakan Denda</h1>
                <p class="text-muted mb-0">Rekapitulasi denda berjalan anggota sebelum pemulangan buku dilakukan.</p>
              </div>
            </div>
          </div>

          <section class="panel mt-3">
            <div class="panel-header">
              <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-table"></i><span>Daftar Seluruh Tunggakan Aktif</span></h2>
              </div>
              <input class="form-control form-control-sm table-search" type="search" placeholder="Cari denda..." data-table-search="dendaTable" style="max-width: 250px;">
            </div>
            
            <div class="table-responsive">
              <table class="table align-middle mb-0 table-hover" id="dendaTable">
                <thead>
                  <tr>
                    <th class="sortable-header">
                      <a href="denda.php?by=nama_lengkap&order=<?= ($sort_by == 'nama_lengkap') ? $next_order : 'ASC'; ?>">
                        Nama Anggota <?= getSortIcon('nama_lengkap', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="denda.php?by=judul&order=<?= ($sort_by == 'judul') ? $next_order : 'ASC'; ?>">
                        Buku Bermasalah <?= getSortIcon('judul', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="denda.php?by=total_tagihan&order=<?= ($sort_by == 'total_tagihan') ? $next_order : 'ASC'; ?>">
                        Jumlah Denda (Estimasi) <?= getSortIcon('total_tagihan', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="denda.php?by=status_nyata&order=<?= ($sort_by == 'status_nyata') ? $next_order : 'ASC'; ?>">
                        Sifat / Tipe <?= getSortIcon('status_nyata', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="text-end" style="min-width: 150px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  if (mysqli_num_rows($query_denda) > 0): 
                    while($row = mysqli_fetch_assoc($query_denda)):
                  ?>
                    <tr>
                      <td class="fw-medium text-primary"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                      <td>
                        <div class="table-media">
                          <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
                          <span class="text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($row['judul']); ?>"><?= htmlspecialchars($row['judul']); ?></span>
                        </div>
                      </td>
                      <td class="fw-bold <?= ($row['status_nyata'] == 'lunas') ? 'text-success' : 'text-danger'; ?>">
                        Rp <?= number_format($row['total_tagihan'], 0, ',', '.'); ?>
                      </td>
                      <td>
                        <span class="badge <?= ($row['status_nyata'] == 'lunas') ? 'bg-success' : 'bg-danger'; ?> text-white border <?= ($row['status_nyata'] == 'lunas') ? 'border-success' : 'border-danger'; ?> px-2 py-1 rounded-pill small">
                          <i class="bi bi-hourglass-split me-1"></i><?= $row['tipe_denda']; ?>
                        </span>
                      </td>
                      <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                          <?php if ($row['status_nyata'] == 'lunas'): ?>
                            <button class="btn btn-secondary btn-sm px-2 py-1" style="font-size: 0.8rem;" disabled>
                              <i class="bi bi-check2-all me-1"></i>Selesai
                            </button>
                          <?php else: ?>
                            <a href="peminjaman.php" class="btn btn-primary btn-sm px-2 py-1" style="font-size: 0.8rem;">
                              <i class="bi bi-arrow-right me-1"></i>Kembalikan & Lunasi
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
                      <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle display-6 d-block mb-2 text-success"></i> Semua sirkulasi aman! Tidak ada denda berjalan saat ini.
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
        <div class="container-fluid px-3 px-lg-4"><span>Copyright 2026 PojokBaca. All rights reserved.</span></div>
      </footer>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>