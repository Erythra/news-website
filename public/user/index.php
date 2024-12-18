    <?php
    require_once '../../app/db.php';
    include 'partials/header.php';

    $db = connectMongoDB();

    $newsCollection = $db->NewsOne;
    $categoryCollection = $db->Category;

    $newsList = $newsCollection->find([], ['sort' => ['created_at' => -1]]);
    $newsArray = iterator_to_array($newsList);
    $latestNews = array_slice($newsArray, 0, 3);

    $categoryList = $categoryCollection->find([]);
    $categoryArray = iterator_to_array($categoryList);
    $categoryMap = [];
    foreach ($categoryArray as $category) {
        $categoryMap[(string)$category['_id']] = $category['name'];
    }
    ?>

    <!-- CAROUSEL -->
    <div class="inti_carousel">
        <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach ($latestNews as $index => $news): ?>
                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="true" aria-label="Slide <?= $index + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($latestNews as $index => $news): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <div class="carousel-image-container position-relative">
                            <?php
                            $imagePath = $news['image'] ?? '/api/placeholder/800/400';

                            $imageSrc = strpos($imagePath, '/uploads/') === 0 ? '/news-website' . $imagePath : $imagePath;
                            ?>
                            <img src="<?= htmlspecialchars($imageSrc) ?>" class="d-block w-100" alt="<?= htmlspecialchars($news['title']) ?>" style="height: auto; object-fit: cover;">

                            <div class="overlay"></div>
                        </div>
                        <div class="carousel-caption d-none d-md-block text-left">
                            <a href="detail.php?id=<?= $news['_id'] ?>" class="news-title-link">
                                <h3><?= htmlspecialchars($news['title']) ?></h3>
                            </a>
                            <p><?= htmlspecialchars($news['summary']) ?></p>
                            <small class="text-light">
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
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>

    <div class="container content">
        <!-- LATEST NEWS -->
        <div class="d-flex align-items-center mb-5">
            <div class="flex-grow-1 border-top border-2"></div>
            <h1 class="px-3">Latest News</h1>
            <div class="flex-grow-1 border-top border-2"></div>
        </div>

        <div class="row g-4">
            <?php if (empty($newsArray)): ?>
                <p>No news found.</p>
            <?php else: ?>
                <?php
                $newsArrayLimited = array_slice($newsArray, 0, 6);
                ?>
                <?php foreach ($newsArrayLimited as $news): ?>
                    <div class="col-md-4">
                        <div class="card card-margin">
                            <?php
                            $imagePath = $news['image'];

                            // Cek apakah image adalah URL atau path relatif
                            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                                // Jika sudah berupa URL penuh, gunakan langsung
                                $imageSrc = $imagePath;
                            } else {
                                // Jika hanya path relatif, tambahkan base URL yang mengarah ke folder public
                                $imageSrc = '/news-website/' . ltrim($imagePath, '/');
                            }
                            ?>
                            <?php if (!empty($imageSrc)): ?>
                                <img src="<?= htmlspecialchars($imageSrc) ?>" class="card-img-top" alt="News Image">
                            <?php else: ?>
                                <img src="/api/placeholder/400/250" class="card-img-top" alt="Placeholder Image">
                            <?php endif; ?>

                            <div class="card">
                                <h5 class="card-title"><?= htmlspecialchars($news['title']) ?></h5>

                                <?php
                                $summary = htmlspecialchars($news['summary']);
                                $maxLength = 100;

                                if (strlen($summary) > $maxLength) {
                                    $summary = substr($summary, 0, $maxLength) . '...'; // Truncate dengan elipsis
                                }
                                ?>
                                <p class="card-text"><?= $summary ?></p>

                                <a href="news.php?category=<?= htmlspecialchars($news['category']) ?>">
                                    <div class="w-25"><span class="badge text-bg-secondary"><?= htmlspecialchars($categoryMap[(string)$news['category']] ?? 'Uncategorized') ?></span></div>
                                </a>

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

        <!-- CATEGORY -->
        <div class="row category-main">
            <div class="d-flex align-items-center mb-5">
                <div class="flex-grow-1 border-top border-2"></div>
                <h1 class="px-3">Category</h1>
                <div class="flex-grow-1 border-top border-2"></div>
            </div>
            <div class="row">
                <?php foreach ($categoryArray as $category): ?>
                    <div class="col-md-3">
                        <div class="category-card">
                            <?php if (!empty($category['image_url'])): ?>
                                <img src="<?= htmlspecialchars($category['image_url']) ?>" alt="Category Image" class="category-image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x300" alt="Placeholder Image" class="category-image">
                            <?php endif; ?>
                            <div class="category-overlay">
                                <h5 class="category-title"><?= htmlspecialchars($category['name']) ?></h5>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php include 'partials/footer.php'; ?>