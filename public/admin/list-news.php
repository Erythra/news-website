<?php
require_once '../../app/db.php';

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Inisialisasi koneksi database
$db = connectMongoDB();
$newsCollection = $db->NewsOne;
$categoryCollection = $db->Category;

// Fungsi untuk membersihkan input
function sanitizeInput($input) {
    return trim(htmlspecialchars(strip_tags($input)));
}

// Inisialisasi parameter filter
$filter = [];
$searchQuery = '';
$selectedCategory = '';
$sortBy = 'created_at';
$sortOrder = -1;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$itemsPerPage = 10;

// Ambil daftar kategori
try {
    $categories = iterator_to_array(
        $categoryCollection->find([], ['sort' => ['name' => 1]])
    );
} catch (Exception $e) {
    $categories = [];
    // Log error
}

// Proses filter
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Filter kategori
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        try {
            $selectedCategory = sanitizeInput($_GET['category']);
            $filter['category'] = new MongoDB\BSON\ObjectId($selectedCategory);
        } catch (Exception $e) {
            // Handle invalid ObjectId
            $selectedCategory = '';
        }
    }

    // Pencarian
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $searchQuery = sanitizeInput($_GET['search']);
        $filter['$or'] = [
            ['title' => new MongoDB\BSON\Regex($searchQuery, 'i')],
            ['content' => new MongoDB\BSON\Regex($searchQuery, 'i')],
            ['author' => new MongoDB\BSON\Regex($searchQuery, 'i')]
        ];
    }

    // Sorting
    if (isset($_GET['sort']) && !empty($_GET['sort'])) {
        switch(sanitizeInput($_GET['sort'])) {
            case 'newest':
                $sortBy = 'created_at';
                $sortOrder = -1;
                break;
            case 'oldest':
                $sortBy = 'created_at';
                $sortOrder = 1;
                break;
            case 'title_asc':
                $sortBy = 'title';
                $sortOrder = 1;
                break;
            case 'title_desc':
                $sortBy = 'title';
                $sortOrder = -1;
                break;
        }
    }
}

// Proses penghapusan berita
if (isset($_GET['delete'])) {
    try {
        $newsId = sanitizeInput($_GET['delete']);
        $objectId = new MongoDB\BSON\ObjectId($newsId);
        
        $result = $newsCollection->deleteOne(['_id' => $objectId]);
        
        if ($result->getDeletedCount() > 0) {
            $_SESSION['message'] = "Berita berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus berita.";
        }
        
        header("Location: list-news.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Pipeline aggregation
$pipeline = [
    ['$match' => $filter],
    [
        '$lookup' => [
            'from' => 'Category',
            'localField' => 'category',
            'foreignField' => '_id',
            'as' => 'categoryDetails'
        ]
    ],
    [
        '$unwind' => [
            'path' => '$categoryDetails',
            'preserveNullAndEmptyArrays' => true
        ]
    ],
    [
        '$sort' => [$sortBy => $sortOrder]
    ],
    [
        '$project' => [
            'title' => 1,
            'content' => 1,
            'author' => 1,
            'category' => 1,
            'created_at' => 1,
            'categoryName' => '$categoryDetails.name'
        ]
    ]
];

// Eksekusi aggregation
try {
    $newsList = $newsCollection->aggregate($pipeline);
    $newsArray = iterator_to_array($newsList);
    
    // Pagination
    $totalItems = count($newsArray);
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($page - 1) * $itemsPerPage;
    $paginatedNews = array_slice($newsArray, $offset, $itemsPerPage);
} catch (Exception $e) {
    $newsArray = [];
    $paginatedNews = [];
    $totalPages = 0;
    // Log error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../partials/cdn.php'; ?>
    <title>Daftar Berita</title>
    <style>
        .filter-container {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include '../partials/sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Pesan Notifikasi -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Filter Container -->
                <div class="filter-container">
                    <form method="GET" class="row g-3">
                        <!-- Kategori Dropdown -->
                        <div class="col-md-3">
                            <select name="category" class="form-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option 
                                        value="<?= $category['_id'] ?>"
                                        <?= ($selectedCategory == $category['_id']) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Pencarian -->
                        <div class="col-md-3">
                            <input 
                                type="text" 
                                name="search" 
                                class="form-control" 
                                placeholder="Cari berita..." 
                                value="<?= htmlspecialchars($searchQuery) ?>"
                            >
                        </div>

                        <!-- Sorting -->
                        <div class="col-md-3">
                            <select name="sort" class="form-select">
                                <option value="newest" <?= ($sortBy == 'created_at' && $sortOrder == -1) ? 'selected' : '' ?>>
                                    Terbaru
                                </option>
                                <option value="oldest" <?= ($sortBy == 'created_at' && $sortOrder == 1) ? 'selecte d' : '' ?>>
                                    Terlama
                                </option>
                                <option value="title_asc" <?= ($sortBy == 'title' && $sortOrder == 1) ? 'selected' : '' ?>>
                                    Judul A-Z
                                </option>
                                <option value="title_desc" <?= ($sortBy == 'title' && $sortOrder == -1) ? 'selected' : '' ?>>
                                    Judul Z-A
                                </option>
                            </select>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                            <a href="list-news.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Tabel Berita -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Kategori</th>
                                <th>Dibuat Pada</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paginatedNews)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        Tidak ada berita ditemukan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($paginatedNews as $index => $news): ?>
                                    <tr>
                                        <td><?= $offset + $index + 1 ?></td>
                                        <td><?= htmlspecialchars($news['title']) ?></td>
                                        <td><?= htmlspecialchars($news['author']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($news['categoryDetails']['name'] ?? 'Tanpa Kategori') ?>
                                        </td>
                                        <td>
                                            <?= date('F d, Y', 
                                                $news['created_at'] instanceof MongoDB\BSON\UTCDateTime 
                                                ? $news['created_at']->toDateTime()->getTimestamp() 
                                                : time()
                                            ) ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit.php?id=<?= $news['_id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a 
                                                    href="?delete=<?= $news['_id'] ?>" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Apakah Anda yakin?');"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Navigasi Halaman -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Navigasi Halaman">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a href="list-news.php?page=<?= $i ?>" class="page-link">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <?php include '../partials/footer.php'; ?>
</body>
</html>