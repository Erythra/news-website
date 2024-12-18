<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
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

<div class="container">
        <div class="p-4">
            <header class="mb-4">
                <h1>Welcome Admin!</h1>
                <p class="text-white">Ready to add, edit, or publish some news?</p>
            </header>

            <div class="row mb-4">
                <div class="col-3">
                    <div class="card border-0 shadow" style="max-width: 15rem; height: 8rem;">
                        <div class="row g-0">
                            <div class="col-12">
                                <div class="card-body">
                                    <div class="border-start border-4 border-primary ps-2">
                                        <div class="d-flex justify-content-between">
                                            <div class="card-text fw-bold text-secondary fs-6">Total News</div>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16">
                                                <path fill="#6C757D" d="M4 9V8h1v1zM1 4a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v6.5a.5.5 0 0 0 1 0V4a2 2 0 0 1 2 2v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 10.5zm2.5 1a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 0-.5-.5zm4 0a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z" />
                                            </svg>
                                        </div>
                                        <div class="card-title fw-bold fs-3">6</div>
                                    </div>
                                    <div class="card-text small text-secondary justify-content-end">Since last week</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card border-0 shadow" style="max-width: 15rem; height: 8rem;">
                        <div class="row g-0">
                            <div class="col-12">
                                <div class="card-body">
                                    <div class="border-start border-4 border-primary ps-2">
                                        <div class="d-flex justify-content-between">
                                            <div class="card-text fw-bold text-secondary fs-6">Total Categories</div>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16">
                                                <path fill="#6C757D" d="M4 9V8h1v1zM1 4a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v6.5a.5.5 0 0 0 1 0V4a2 2 0 0 1 2 2v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 10.5zm2.5 1a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 0-.5-.5zm4 0a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z" />
                                            </svg>
                                        </div>
                                        <div class="card-title fw-bold fs-3">6</div>
                                    </div>
                                    <div class="card-text small text-secondary justify-content-end">Since last week</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <a href="create.php" target="_blank" class="text-decoration-none">
                        <div class="card text-center border-0 shadow" style="max-width: 15rem; height: 8rem;">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center h-100">
                                <div class="card-text fw-bold text-secondary fs-6">Add some news?</div>
                                <div class="card-title fw-bold fs-3">+</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>


            </div>
        </div>
    </div>
</body>