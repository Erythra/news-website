<?php
require_once '../../app/db.php';

// Connect to MongoDB
$db = connectMongoDB();
$newsCollection = $db->NewsOne;
$categoryCollection = $db->Category;

// Fetch news articles based on selected category
$categoryFilter = isset($_GET['category']) ? new MongoDB\BSON\ObjectId($_GET['category']) : null;
$newsList = $newsCollection->find($categoryFilter ? ['category' => $categoryFilter] : [], ['sort' => ['created_at' => -1]]);
$newsArray = iterator_to_array($newsList);

// Fetch the category name based on the selected category
$categoryName = null;
if ($categoryFilter) {
    $category = $categoryCollection->findOne(['_id' => $categoryFilter]);
    if ($category) {
        $categoryName = $category['name'];
    }
}
$categories = $categoryCollection->find();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News List by Category</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="../assets/images/LogoNews.png" alt="Logo" style="max-height: 40px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav m-auto my-3">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> Category </a>
                        <ul class="dropdown-menu">
                            <?php if ($categories): ?>
                                <?php foreach ($categories as $category): ?>
                                    <li><a class="dropdown-item" href="news.php?category=<?= htmlspecialchars($category['_id']) ?>"><?= htmlspecialchars($category['name']) ?></a></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>
                                    <p class="dropdown-item">No categories found.</p>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
                <form class="d-flex ms-3" action="/search" method="GET">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>
    
    <div class="container content">
        <div class="d-flex align-items-center my-5">
            <div class="flex-grow-1 border-top border-2"></div>
            <h1 class="px-3"><?= htmlspecialchars($categoryName ? $categoryName : 'No Category Selected') ?></h1>
            <div class="flex-grow-1 border-top border-2"></div>
        </div>

        <div class="row g-4">
            <?php if (empty($newsArray)): ?>
                <p>No news found.</p>
            <?php else: ?>
                <?php foreach ($newsArray as $news): ?>
                    <div class="col-md-6">
                        <div class="card card-margin">
                            <img src="<?= !empty($news['image']) ? htmlspecialchars($news['image']) : '/api/placeholder/400/250' ?>" class="card-img-top" alt="News Image">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($news['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($news['summary']) ?></p>
                                <p class="text-muted">
                                    <small>
                                        Published:
                                        <?php
                                        if (isset($news['created_at'])) {
                                            if ($news['created_at'] instanceof MongoDB\BSON\UTCDateTime) {
                                                $date = $news['created_at']->toDateTime();
                                                echo $date->format('Y-m-d H:i:s');
                                            } elseif (is_numeric($news['created_at'])) {
                                                $date = new DateTime();
                                                $date->setTimestamp($news['created_at'] / 1000);
                                                echo $date->format('Y-m-d H:i:s');
                                            } else {
                                                echo "Invalid Date Format";
                                            }
                                        } else {
                                            echo "Date Not Set";
                                        }
                                        ?>
                                    </small>
                                </p>
                                <a href="detail.php?id=<?= $news['_id'] ?>" class="btn btn-custom btn-sm">Continue Reading</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/js/bootstrap.bundle.min.js"></script>

</body>

</html>