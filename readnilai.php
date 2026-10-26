<?php
// Konek ke database dan pilih database nilai
require_once __DIR__ . "/connection.php";
mysqli_select_db($conn, "akademik_db");

// Tangkap parameter pencarian dan filter predikat
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_predikat = isset($_GET['filter_predikat']) ? $_GET['filter_predikat'] : '';

// Pengaturan Pagination (9 data per halaman)
$limit = 9;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Kondisi pencarian & filter
$where = "WHERE 1=1";
if ($search !== '') {
    $search_esc = mysqli_real_escape_string($conn, $search);
    $where .= " AND (nm_lengkap LIKE '%$search_esc%' OR nim LIKE '%$search_esc%')";
}
if ($filter_predikat !== '') {
    $predikat_esc = mysqli_real_escape_string($conn, $filter_predikat);
    $where .= " AND predikat = '$predikat_esc'";
}

// Hitung total data untuk pagination
$sql_count = "SELECT COUNT(*) as total FROM nilai $where";
$result_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_data = $row_count['total'];
$total_pages = $total_data > 0 ? ceil($total_data / $limit) : 1;

if ($page > $total_pages) {
    $page = $total_pages;
}

// Query data utama dengan limit dan offset
$sql = "SELECT * FROM nilai $where ORDER BY created_date DESC LIMIT $offset, $limit";
$result = mysqli_query($conn, $sql);

// Hitung range data yang ditampilkan
$start_num = $total_data > 0 ? $offset + 1 : 0;
$end_num = min($offset + $limit, $total_data);
?>
<!doctype html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Nilai Mahasiswa</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #161922;
            color: #cbd5e1;
            font-family: Arial, sans-serif;
        }
        .card {
            background-color: #1e222b;
            border: 1px solid #2a2f3d;
        }
        .card-header, .card-footer {
            background-color: #1e222b !important;
            border-color: #2a2f3d !important;
        }
        .table {
            color: #cbd5e1;
            background-color: #1e222b;
        }
        .table th {
            color: #94a3b8;
            font-weight: 600;
            background-color: #1e222b;
            border-bottom: 1px solid #2a2f3d;
        }
        .table td {
            border-bottom: 1px solid #2a2f3d;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: #252a37;
            color: #ffffff;
        }
        .form-control, .form-select {
            background-color: #161922;
            border-color: #2a2f3d;
            color: #cbd5e1;
        }
        .form-control:focus, .form-select:focus {
            background-color: #161922;
            border-color: #3b82f6;
            color: #ffffff;
            box-shadow: none;
        }
        .breadcrumb a {
            color: #94a3b8;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            color: #ffffff;
        }
        .btn-action {
            background-color: #2a2f3d;
            border: 1px solid #383f51;
            color: #94a3b8;
            padding: 5px 8px;
            border-radius: 4px;
        }
        .btn-action:hover {
            background-color: #383f51;
            color: #ffffff;
        }
        .btn-action-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="container py-4">
        <!-- Top Header & Breadcrumb -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-white mb-0">Rekap Nilai Mahasiswa</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">Nilai</li>
                </ol>
            </nav>
        </div>

        <!-- Card Table Container -->
        <div class="card shadow-sm">
            <!-- Card Header with Filters and Button -->
            <div class="card-header py-3">
                <div class="row align-items-center g-3">
                    <div class="col-md-3">
                        <h5 class="card-title mb-0 fw-semibold text-white">Direktori Nilai</h5>
                    </div>
                    <div class="col-md-9">
                        <form method="GET" action="" class="d-flex flex-wrap justify-content-md-end gap-2 align-items-center">
                            <!-- Input Pencarian berdasarkan Nama -->
                            <div class="input-group input-group-sm" style="width: 220px;">
                                <span class="input-group-text bg-transparent border-end-0 text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari Berdasarkan Nama" value="<?= htmlspecialchars($search) ?>">
                            </div>

                            <!-- Dropdown Filter Predikat -->
                            <select name="filter_predikat" class="form-select form-select-sm" style="width: 150px;" onchange="this.form.submit()">
                                <option value="">Semua Predikat</option>
                                <option value="Sangat Memuaskan" <?= $filter_predikat === 'Sangat Memuaskan' ? 'selected' : '' ?>>Sangat Memuaskan</option>
                                <option value="Memuaskan" <?= $filter_predikat === 'Memuaskan' ? 'selected' : '' ?>>Memuaskan</option>
                                <option value="Cukup" <?= $filter_predikat === 'Cukup' ? 'selected' : '' ?>>Cukup</option>
                            </select>

                            <?php if ($search !== '' || $filter_predikat !== ''): ?>
                                <a href="?" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            <?php endif; ?>

                            <!-- Tombol Tambah Data Nilai -->
                            <a href="nilaiui.php" class="btn btn-sm btn-primary px-3">
                                <i class="bi bi-person-plus-fill me-1"></i> Tambah Data Nilai
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Card Body Table -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="py-3 ps-4">NIM</th>
                                <th class="py-3">Nama Lengkap</th>
                                <th class="py-3">Nilai Akhir</th>
                                <th class="py-3">Nilai Huruf</th>
                                <th class="py-3">Predikat</th>
                                <th class="py-3">Tgl Dibuat</th>
                                <th class="py-3">Tgl Diubah</th>
                                <th class="py-3 text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                            ?>
                                    <tr>
                                        <td class="ps-4 fw-medium"><?= htmlspecialchars($row["nim"]) ?></td>
                                        <td><?= htmlspecialchars($row["nm_lengkap"]) ?></td>
                                        <td><?= htmlspecialchars($row["nilai_akhir"]) ?></td>
                                        <td><?= htmlspecialchars($row["nilai_huruf"]) ?></td>
                                        <td><?= htmlspecialchars($row["predikat"]) ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($row["created_date"]) ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($row["modified_date"]) ?></td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <a href="#" class="btn btn-action" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="#" class="btn btn-action btn-action-danger" title="Hapus" onclick="return confirm('Yakin hapus data ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="8" class="text-center py-4 text-muted">Data tidak ditemukan.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card Footer: Jumlah Data di Kiri & Pagination di Kanan -->
            <div class="card-footer py-3 d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted small mb-2 mb-md-0">
                    Menampilkan data <?= $start_num ?> sampai <?= $end_num ?> dari total <strong><?= $total_data ?></strong> data
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <!-- Tombol Previous -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link bg-transparent border-secondary text-light" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&filter_predikat=<?= urlencode($filter_predikat) ?>" aria-label="Previous">
                                    &laquo;
                                </a>
                            </li>

                            <!-- Nomor Halaman -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link <?= ($page == $i) ? 'bg-primary border-primary text-white' : 'bg-transparent border-secondary text-light' ?>" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&filter_predikat=<?= urlencode($filter_predikat) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Tombol Next -->
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link bg-transparent border-secondary text-light" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&filter_predikat=<?= urlencode($filter_predikat) ?>" aria-label="Next">
                                    &raquo;
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
mysqli_close($conn); 
?>