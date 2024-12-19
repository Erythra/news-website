<?php
require_once '../../app/db.php';
include 'partials/header.php';

$db = connectMongoDB();

// Fetch all categories
$categoryCollection = $db->Category;
$categoryList = $categoryCollection->find([]);
$categoryArray = iterator_to_array($categoryList);
$categories = $categoryCollection->find();

// Fetch news details
$newsCollection = $db->NewsOne;
$idParam = $_GET['id'] ?? null;
try {
    $id = new MongoDB\BSON\ObjectId($idParam);
} catch (Exception $e) {
    die('ID berita tidak valid.');
}

$NewsOne = $newsCollection->findOne(['_id' => $id]);
if (!$NewsOne) {
    die('Berita tidak ditemukan.');
}

// Fetch related news (excluding the current one)
$relatedNews = $newsCollection->find([
    '_id' => ['$ne' => $id]
], ['sort' => ['created_at' => -1], 'limit' => 5]);
$relatedNewsArray = iterator_to_array($relatedNews);

// Fetch the category of the current news
$categoryId = $NewsOne['category'] ?? null;
$category = $categoryId ? $categoryCollection->findOne(['_id' => $categoryId]) : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment']) && isset($_POST['rating'])) {
    $pengguna = htmlspecialchars($_POST['pengguna'], ENT_QUOTES, 'UTF-8');
    $comment = htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');
    $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);

    if (!$rating || $rating < 1 || $rating > 5) {
        die('Rating tidak valid.');
    }

    $commentData = [
        'username' => $pengguna,
        'comment' => $comment,
        'rating' => $rating,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];

    $newsCollection->updateOne(
        ['_id' => $id],
        ['$push' => ['comments' => $commentData]]
    );

    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $idParam);
    exit;
}
$comments = $NewsOne['comments'] ?? [];
?>
<style>
    a {
        text-decoration: none;
    }
</style>
<div class="container" style="margin-top: 8rem !important; width: fit-content;">
    <div class="row" style="column-gap: 4rem">
        <!-- Kolom Kiri: List Kategori -->
        <div class="col-md-3 align-self-start" style="background-color: #f5f5f5; border-radius: 1rem; width: 15%; padding-bottom: 2rem;">
            <h4 style="padding: 1rem; font-weight: bolder;">Category</h4>
            <ul class="list-group">
                <?php if ($categories): ?>
                    <?php foreach ($categories as $cat): ?>
                        <li class="list-group-item" style="border: 0 !important; background-color: #f5f5f5 !important;">
                            <a class="dropdown-item" href="news.php?category=<?= htmlspecialchars($cat['_id']) ?>"><?= htmlspecialchars($cat['name']) ?></a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>
                        <p class="dropdown-item">No categories found.</p>
                    </li>
                <?php endif; ?>

            </ul>
        </div>

        <!-- Kolom Tengah: Detail Berita -->
        <div class="col-md-6">
            <?php if ($category): ?>
                <h5 class="text-muted">
                    <img src="<?php echo htmlspecialchars($category['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($category['name']); ?>"
                        style="width: 30px; height: 30px; margin-right: 10px; border-radius: 50%;">
                    <?php echo htmlspecialchars($category['name']); ?>
                </h5>
            <?php endif; ?>

            <h1><?php echo htmlspecialchars($NewsOne['title']); ?></h1>
            <p><em>Ditulis oleh <?php echo htmlspecialchars($NewsOne['author']); ?> | <?php echo $NewsOne['created_at']->toDateTime()->format('Y-m-d'); ?></em></p>

            <?php
            $imagePath = !empty($NewsOne['image']) ? $NewsOne['image'] : null;

            if ($imagePath && strpos($imagePath, '/uploads/') === 0) {
                $imageSrc = '/news-website' . $imagePath;
            } else {
                $imageSrc = $imagePath;
            }
            ?>
            <?php if (!empty($imageSrc)): ?>
                <img src="<?= htmlspecialchars($imageSrc) ?>" class="img-fluid" alt="<?= htmlspecialchars($NewsOne['title']) ?>">
            <?php endif; ?>

            <div class="mt-4">
                <p><?php echo nl2br(htmlspecialchars($NewsOne['content'])); ?></p>
            </div>

            <!-- Form Komentar -->
            <section class="mt-4" style="border-top: solid 4px rgb(33 37 41);">
                <h3 class="mt-4">Tambahkan Komentar dan Rating</h3>
                <form method="POST" action="">
                    <div class="form-group" style="padding-bottom: 1rem;">
                        <input class="form-control mb-2" type="text" name="pengguna" id="pengguna" placeholder="Username">
                        <textarea name="comment" id="comment" class="form-control" rows="4" placeholder="Tambahkan Komentar..." required></textarea>
                    </div>
                    <div class="form-group" style="padding-bottom: 1rem;">
                        <label for="rating">Rating:</label>
                        <select name="rating" class="form-control" required>
                            <option value="">Pilih Rating</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="background-color: #050505; border: 0px;">Kirim Komentar</button>
                </form>
            </section>

            <!-- Komentar yang Sudah Ada -->
            <section class="mt-4" style="border-top: solid 4px rgb(33 37 41);">
                <h3>Komentar Pengunjung</h3>
                <ul class="list-group mt-4">
                    <?php foreach ($comments as $comment): ?>
                        <li class="list-group-item">
                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong> (Rating: <?php echo $comment['rating']; ?>)
                            <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                            <small class="text-muted"><?php echo $comment['created_at']->toDateTime()->format('Y-m-d H:i'); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </div>

        <!-- Kolom Kanan: Berita Lain -->
        <div class="col-md-3" style="min-width: 20rem;">
            <h4><b>Related News</b></h4>
            <?php foreach ($relatedNewsArray as $news): ?>
                <div class="card mb-3" style="background-color: #f5f5f5; padding: 1rem; border-radius: 1rem;">
                    <?php
                    $imagePath = !empty($news['image']) ? $news['image'] : null;

                    if ($imagePath && strpos($imagePath, '/uploads/') === 0) {
                        $imageSrc = '/news-website' . $imagePath;
                    } else {
                        $imageSrc = $imagePath;
                    }
                    ?>
                    <?php if (!empty($imageSrc)): ?>
                        <img src="<?= htmlspecialchars($imageSrc) ?>" class="card-img-top" alt="<?= htmlspecialchars($news['title']) ?>">
                    <?php else: ?>
                        <img src="/api/placeholder/800/400" class="card-img-top" alt="Placeholder Image">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="kategori" style="background-color: #050505; color: #f5f5f5; width: fit-content; border-radius: 1rem; font-size: small; padding: 0.5rem; font-weight: bold;">
                            <?php if ($category): ?>
                                <?php echo htmlspecialchars($category['name']); ?>
                            <?php endif; ?>
                        </h5>
                        <a href="?id=<?php echo $news['_id']; ?>" style="color: #050505; font-weight: bold;">
                            <?php echo htmlspecialchars($news['title']); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php include 'partials/footer.php'; ?>