<?php
require_once '../../app/db.php'; // Include database connection

// Start session to check if the admin is logged in
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Connect to MongoDB
$db = connectMongoDB();
$collection = $db->NewsOne; // Name of the collection

// Handle Edit Request
if (isset($_GET['id'])) {
    $newsId = $_GET['id'];

    // Validate if the ObjectId is a valid 24-character hex string
    if (preg_match('/^[a-f0-9]{24}$/', $newsId)) {
        // Fetch the news item from the MongoDB collection
        $news = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($newsId)]);
    } else {
        echo "Invalid ObjectId format.";
        exit;
    }
}

// Handle Update Request
if (isset($_POST['update'])) {
    // Collect new data from the form
    $updatedNews = [
        'title' => $_POST['title'],
        'content' => $_POST['content'],
        'author' => $_POST['author'],
        'summary' => $_POST['summary'], // New summary field
    ];

    // Handle image link input
    if (!empty($_POST['image_url'])) {
        $updatedNews['image'] = $_POST['image_url']; // Save the image URL
    }

    // Update the news item in the MongoDB collection
    $collection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($newsId)],
        ['$set' => $updatedNews]
    );

    // Redirect back to the list-news page after update
    header("Location: list-news.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit News</title>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #333;
            line-height: 1.6;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .nav {
            margin-bottom: 20px;
            text-align: right;
        }

        .nav a {
            text-decoration: none;
            color: #555;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .nav a:hover {
            color: #222;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #333;
        }

        .image-preview {
            max-width: 200px;
            margin: 10px 0;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .submit-btn {
            background-color: #444;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #222;
        }

        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Edit News Article</h1>

        <div class="nav">
            <a href="list-news.php">← Back to News List</a>
        </div>

        <?php if (isset($news)): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title"
                        value="<?= htmlspecialchars($news['title']) ?>"
                        placeholder="Enter news title" required>
                </div>

                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" rows="6"
                        placeholder="Write your news article content"
                        required><?= htmlspecialchars($news['content']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author"
                        value="<?= htmlspecialchars($news['author']) ?>"
                        placeholder="Author name" required>
                </div>

                <div class="form-group">
                    <label for="summary">Summary</label>
                    <textarea id="summary" name="summary" rows="3"
                        placeholder="Short summary of the article"
                        required><?= htmlspecialchars($news['summary']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image_url">Image URL</label>
                    <input type="text" id="image_url" name="image_url"
                        value="<?= htmlspecialchars($news['image'] ?? '') ?>"
                        placeholder="Optional image URL">
                </div>

                <?php if (!empty($news['image'])): ?>
                    <div class="form-group">
                        <label>Current Image</label>
                        <img src="<?= htmlspecialchars($news['image']) ?>"
                            alt="Current Image" class="image-preview">
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <button type="submit" name="update" class="submit-btn">
                        Update News Article
                    </button>
                </div>
            </form>
        <?php else: ?>
            <p>News article not found.</p>
        <?php endif; ?>
    </div>
</body>

</html>