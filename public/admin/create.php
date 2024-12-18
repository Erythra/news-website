<?php
require_once '../../app/db.php';

use MongoDB\BSON\UTCDateTime;

session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Handle form submission
if (isset($_POST['submit'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $db = connectMongoDB();
        $collection = $db->NewsOne;

        // Get form data
        $title = $_POST['title'];
        $content = $_POST['content'];
        $summary = $_POST['summary'];
        $category = $_POST['category'];
        $author = $_POST['author'];

        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/';
            $fileName = basename($_FILES['image']['name']);
            $uploadFile = $uploadDir . $fileName;

            // Ensure the upload directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $image = $uploadFile; // Save the file path to the database
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "No image file uploaded.";
        }

        
        if (!isset($error)) {
            $document = [
                'title' => $title,
                'content' => $content,
                'summary' => $summary,
                'category' => $category,
                'author' => $author,
                'image' => $image,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ];

            // Insert the document into the collection
            $result = $collection->insertOne($document);

            // Check if the insert was successful
            if ($result->getInsertedCount() > 0) {
                header("Location: list-news.php"); // Redirect to the news list page
                exit;
            } else {
                $error = "Failed to add news.";
            }
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
                <form action="" method="post" enctype="multipart/form-data">

                    <?php
                    include '../partials/form.php';
                    ?>
                    <div class="mb-3">
                        <label for="image" class="form-label">Upload Image</label>
                        <input type="file" class="form-control" name="image" id="image" required>
                    </div>
                    <div class="d-flex justify-content-end mb-4">
                        <button type="submit" class="btn btn-dark" name="submit">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
