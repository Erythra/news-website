<?php
require_once '../../app/db.php';

$db = connectMongoDB();
$newsCollection = $db->NewsOne;
$categoryCollection = $db->Category;

$categoryFilter = isset($_GET['category']) ? new MongoDB\BSON\ObjectId($_GET['category']) : null;
$newsList = $newsCollection->find($categoryFilter ? ['category' => $categoryFilter] : [], ['sort' => ['created_at' => -1]]);
$newsArray = iterator_to_array($newsList);

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

    <?php include 'partials/header.php' ?>

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
                            <?php
                            $imagePath = !empty($news['image']) ? $news['image'] : null;

                            if ($imagePath && strpos($imagePath, '/uploads/') === 0) {
                                $imageSrc = '/news-website' . $imagePath;
                            } else {
                                $imageSrc = $imagePath;
                            }
                            ?>
                            <img src="<?= htmlspecialchars($imageSrc ?? '/api/placeholder/400/250') ?>" class="card-img-top" alt="News Image">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($news['title']) ?></h5>
                                <?php
                                $summary = htmlspecialchars($news['summary']);
                                $maxLength = 100;

                                if (strlen($summary) > $maxLength) {
                                    $summary = substr($summary, 0, $maxLength) . '...';
                                }
                                ?>
                                <p class="card-text"><?= $summary ?></p>
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