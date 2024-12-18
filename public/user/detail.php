<?php
require_once '../../app/db.php';
include 'partials/header.php';
include '../partials/cdn.php';
$db = connectMongoDB();

$newsId = $_GET['id'];

$news = $db->NewsOne->findOne(['_id' => new MongoDB\BSON\ObjectId($newsId)]);

?>

<div class="detail-news d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="col">
            <h1><?= htmlspecialchars($news['title']) ?></h1>
        </div>
        <div class="col">
            <p><?= htmlspecialchars($news['content']) ?></p>
        </div>

    </div>
</div>

<?php include 'partials/footer.php'; ?>