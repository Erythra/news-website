<?php
require_once '../../app/db.php';

use MongoDB\BSON\UTCDateTime;

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$db = connectMongoDB();
$newsCollection = $db->NewsOne;
$categoryCollection = $db->Category;

try {
    $categories = $categoryCollection->find(
        [],
        ['sort' => ['name' => 1]]
    );
    $categoriesArray = iterator_to_array($categories);
} catch (Exception $e) {
    $categoriesArray = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validasi input
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $summary = trim($_POST['summary']);
        $author = trim($_POST['author']);
        $categoryId = $_POST['category'];

        if (empty($title) || empty($content) || empty($summary) || empty($author) || empty($categoryId)) {
            throw new Exception("Semua field harus diisi");
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/';
            $fileName = basename($_FILES['image']['name']);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception("Format gambar tidak valid. Hanya jpg, jpeg, png, dan gif yang diizinkan.");
            }

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $image = '/uploads/' . $fileName; // Simpan path relatif
            } else {
                throw new Exception("Gagal mengupload gambar.");
            }
        } else {
            throw new Exception("Tidak ada gambar yang diupload.");
        }

        // Siapkan data untuk disimpan
        $newsData = [
            'title' => $title,
            'content' => $content,
            'summary' => $summary,
            'author' => $author,
            'image' => $image,
            'category' => new MongoDB\BSON\ObjectId($categoryId),
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];

        $result = $newsCollection->insertOne($newsData);

        $_SESSION['message'] = "Berita berhasil dibuat!";
        header("Location: list-news.php");
        exit;
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        echo $errorMessage;
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
                <!-- Form untuk input data berita -->
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-2">Title</div>
                        <div class="col-10">
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-2">Content</div>
                        <div class="col-10">
                            <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-2">Summary</div>
                        <div class="col-10">
                            <textarea class="form-control" id="summary" name="summary" rows="4" required></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-2">Category</div>
                        <div class="col-10">
                            <select class="form-control" id="category" name="category" required>
                                <option value="">Choose Category</option>
                                <?php
                                foreach ($categoriesArray as $category) {
                                    $categoryId = (string) $category['_id'];
                                    $categoryName = htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8');
                                    echo "<option value=\"$categoryId\">$categoryName</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-2">Author</div>
                        <div class="col-10">
                            <input type="text" class="form-control" id="author" name="author" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-2">Upload Image</div>
                        <div class="col-10">
                            <input type="file" class="form-control" name="image" id="image" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-4">
                        <button type="submit" class="btn btn-dark" name="submit">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>