<?php
require_once '../../app/db.php';

use MongoDB\BSON\UTCDateTime;

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$db = connectMongoDB();
$collection = $db->NewsOne;
$categoryCollection = $db->Category;

try {
    $categories = $categoryCollection->find(
        [], 
        ['sort' => ['name' => 1]] // Urutkan berdasarkan nama kategori
    );
    $categoriesArray = iterator_to_array($categories);
} catch (Exception $e) {
    $categoriesArray = [];
    // Log error atau tampilkan pesan error
}
// Handle form submission
if (isset($_POST['submit'])) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Validasi input
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $author = trim($_POST['author']);
            $categoryId = $_POST['category']; // ID kategori yang dipilih
    
            // Validasi input
            if (empty($title) || empty($content) || empty($author) || empty($categoryId)) {
                throw new Exception("Semua field harus diisi");
            }
    
            // Siapkan data untuk disimpan
            $newsData = [
                'title' => $title,
                'content' => $content,
                'author' => $author,
                'category' => new MongoDB\BSON\ObjectId($categoryId), // Konversi ke ObjectId
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ];
    
            // Simpan berita
            $result = $newsCollection->insertOne($newsData);
    
            // Redirect dengan pesan sukses
            $_SESSION['message'] = "Berita berhasil dibuat!";
            header("Location: table.php");
            exit;
    
        } catch (Exception $e) {
            // Tangani error
            $errorMessage = $e->getMessage();
        }
    }
}
?>

<?php
include '../partials/cdn.php';
?>
<title>Dashboard</title>

<body>
    <?php
    include '../partials/navbar.php';
    ?>
    <div class="d-flex">

        <?php
        include '../partials/sidebar.php';
        ?>

        <div class="flex-grow-1 p-4">
            <p class="text-muted">Let's share the latest update, Admin!</p>

            <div class="container">
                <form  action="" method="post">

                    <?php
                    include '../partials/form.php';
                    ?>
                    <div class="d-flex justify-content-end mb-4">
                        <button type="submit" class="btn btn-dark" name="submit">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
</body>