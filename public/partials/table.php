<style>
    :root {
        --primary-bg: #121212;
        --secondary-bg: #1e1e1e;
        --text-primary: #ffffff;
        --text-secondary: #b0b0b0;
        --border-color: #333333;
        --hover-color: #444444;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        background-color: var(--text-primary);
        color: var(--text-primary);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        line-height: 1.6;
    }

    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 5px;
    }

    .news-container {
        background-color: var(--text-primary);
        color: #121212;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        padding: 20px;
    }

    .news-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }

    .news-header h2 {
        font-size: 1.2rem;
        margin: 0;
    }

    .search-bar {
        width: 100%;
        max-width: 200px;
        padding: 8px;
        background-color: var(--text-primary);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        border-radius: 4px;
    }

    .news-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .news-item-content {
        flex-grow: 1;
        margin-right: 15px;
        overflow: hidden;
    }

    .news-title {
        font-size: 1rem;
        margin-bottom: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .news-meta {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-bottom: 5px;
    }

    .news-excerpt {
        font-size: 0.85rem;
        color: var(--text-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .news-actions {
        display: flex;
        flex-shrink: 0;
    }

    .btn {
        background-color: transparent;
        border: 1px solid var(--border-color);
        color: var(--primary-bg);
        padding: 5px 10px;
        margin-left: 5px;
        border-radius: 4px;
        font-size: 0.75rem;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color: var(--text-secondary);
    }

    .btn-delete {
        color: #ff4d4d;
        border-color: #ff4d4d;
    }

    .btn-delete:hover {
        background-color: #ff4d4d;
        color: var(--text-primary);
    }

    .search-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .search-form {
        display: flex;
        gap: 10px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .pagination a {
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        transition: background-color .3s;
        border: 1px solid #ddd;
        margin: 0 4px;
    }

    .pagination a.active {
        background-color: #4CAF50;
        color: white;
        border: 1px solid #4CAF50;
    }

    @media (max-width: 1200px) {
        .container {
            width: 75%;
            padding: 10px;
        }

        .news-header {
            flex-direction: column;
            align-items: stretch;
            width: 100%;
        }

        .search-bar {
            max-width: 100%;
            margin-top: 10px;
        }

        .news-item {
            flex-direction: column;
            align-items: stretch;
        }

        .news-actions {
            margin-top: 10px;
            justify-content: space-between;
        }
    }

    @media (max-width: 992px) {
        .news-item-content {
            margin-right: 0;
        }
    }

    @media (max-width: 768px) {
        .news-item {
            padding: 10px 0;
        }

        .news-actions {
            margin-top: 5px;
        }
    }

    @media (max-width: 576px) {
        .news-item {
            padding: 5px 0;
        }

        .news-actions {
            margin-top: 0;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 5px;
        }

        .news-header {
            padding-bottom: 5px;
        }

        .search-bar {
            padding: 5px;
        }

        .news-item {
            padding: 5px 0;
        }

        .news-actions {
            margin-top: 0;
        }
    }
</style>
<?php
require_once '../../app/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}



$db = connectMongoDB();
$newsCollection = $db->NewsOne;
$newsCategory = $db->Category;

if (isset($_GET['delete'])) {
    $newsId = $_GET['delete'];

    if (preg_match('/^[a-f0-9]{24}$/', $newsId)) {
        $collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($newsId)]);

        exit;
    } else {
        echo "Invalid ObjectId format.";
    }
}

$searchQuery = '';
$filter = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = trim($_GET['search']);

    $filter = [
        '$or' => [
            ['title' => new MongoDB\BSON\Regex($searchQuery, 'i')],
            ['content' => new MongoDB\BSON\Regex($searchQuery, 'i')],
            ['author' => new MongoDB\BSON\Regex($searchQuery, 'i')],
            ['category' => new MongoDB\BSON\Regex($searchQuery, 'i')]
        ]
    ];
}

$newsList = $newsCollection->find($filter);

$newsArray = iterator_to_array($newsList);

$itemsPerPage = 5;
$totalItems = count($newsArray);
$totalPages = ceil($totalItems / $itemsPerPage);

$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

$paginatedNews = array_slice($newsArray, $offset, $itemsPerPage);
?>

</head>

<body>
    <div class="py-4">
        <div class="d-flex justify-content-end">
            <a href="create.php"><button type="button" class="btn btn-dark btn-lg fs-5 text-dark">Create some news</button></a>
        </div>
    </div>

    <div class="container">
        <div class="search-container">
            <h2>News Management</h2>
            <form method="GET" class="search-form">
                <input
                    type="text"
                    name="search"
                    placeholder="Search news..."
                    class="form-control"
                    value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($searchQuery)): ?>
                    <a href="list-news.php" class="btn d-flex align-items-center btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paginatedNews)): ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            <?= empty($searchQuery) ? 'No news found.' : "No results found for '{$searchQuery}'." ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paginatedNews as $index => $news): ?>
                        <tr>
                            <td><?= $offset + $index + 1 ?></td>
                            <td><?= htmlspecialchars($news['title']) ?></td>
                            <td><?= htmlspecialchars($news['author']) ?></td>
                            <td><?= htmlspecialchars($news['category'] ?? 'N/A') ?></td>
                            <td>
                                <?= date(
                                    'F d, Y',
                                    $news['created_at'] instanceof MongoDB\BSON\UTCDateTime
                                        ? $news['created_at']->toDateTime()->getTimestamp()
                                        : time()
                                ) ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit.php?id=<?= $news['_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a
                                        href="?delete=<?= $news['_id'] ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this news?');">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a
                        href="?page=<?= $i ?><?= !empty($searchQuery) ? "&search=" . urlencode($searchQuery) : '' ?>"
                        class="<?= $i == $currentPage ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</body>