<?php
require_once '../../app/db.php';

$db = connectMongoDB();
$collection = $db->Category;

$categories = $collection->find();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../assets/img/Favicon.png">
    <link rel="stylesheet" href="./style.css">
    <title>Newsphere</title>
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
                    <a href="search.php" class="btn btn-outline-light">Search</a>
                </form>
            </div>
        </div>
    </nav>
</body>

</html>