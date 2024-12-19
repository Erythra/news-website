<?php
require_once '../../app/db.php';
include 'partials/header.php';

$db = connectMongoDB();

$newsCollection = $db->NewsOne;
$categoryCollection = $db->Category;

$categoryList = $categoryCollection->find([]);
$categoryArray = iterator_to_array($categoryList);

$searchQuery = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$filter = [];

if (!empty($searchQuery)) {
    $filter['$or'] = [
        ['title' => ['$regex' => $searchQuery, '$options' => 'i']],
        ['summary' => ['$regex' => $searchQuery, '$options' => 'i']]
    ];
}
if (!empty($categoryFilter)) {
    $filter['category'] = new MongoDB\BSON\ObjectId($categoryFilter);
}

$newsList = $newsCollection->find($filter, ['sort' => ['created_at' => -1]]);
$newsArray = iterator_to_array($newsList);
?>

<div class="container content" style="margin-top: 5rem !important;">
    <!-- FORM PENCARIAN -->
    <div class="search-bar mb-5">
        <form method="GET" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by title or summary" value="<?= htmlspecialchars($searchQuery) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categoryArray as $category): ?>
                            <option value="<?= (string)$category['_id'] ?>" <?= $categoryFilter == (string)$category['_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-custom w-100">Search</button>
                </div>
            </div>
        </form>
    </div>

    <!-- HASIL PENCARIAN -->
    <div class="row g-4">
        <?php if (empty($newsArray)): ?>
            <p>No news found matching your criteria.</p>
        <?php else: ?>
            <?php foreach ($newsArray as $news): ?>
                <div class="col-md-4">
                    <div class="card card-margin">
                        <?php
                        $imagePath = $news['image'] ?? '/api/placeholder/400/250';
                        $imageSrc = strpos($imagePath, '/uploads/') === 0 ? '/news-website' . $imagePath : $imagePath;
                        ?>
                        <img src="<?= htmlspecialchars($imageSrc) ?>" class="card-img-top" alt="<?= htmlspecialchars($news['title']) ?>">
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
                            <a href="detail.php?id=<?= $news['_id'] ?>" class="btn btn-custom btn-sm">Read More</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>