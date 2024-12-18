   <?php
    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>

   <nav class="navbar navbar-expand-lg bg-light p-0 border-bottom shadow">
       <div class="d-flex justify-content-center py-3 h-100 m-0 border-bottom shadow align-items-center bg-dark" style="width: 15rem;">
           <img src="../assets/img/Logo.png" alt="Logo" style="width: 10rem;">
       </div>
       <div class="collapse navbar-collapse ms-3" id="navbarNav">
           <ul class="navbar-nav me-auto">
               <li class="nav-item text-dark fw-bold fs-4">
                   <?php
                    if ($currentPage == 'dashboard.php') {
                        echo 'Dashboard';
                    } elseif ($currentPage == 'list-news.php') {
                        echo 'List News';
                    } elseif ($currentPage == 'create.php') {
                        echo 'Create News';
                    } elseif ($currentPage == 'edit.php') {
                        echo 'Edit News';
                    }
                    ?>
               </li>
           </ul>
           <ul class="navbar-nav">
               <li class="nav-item dropdown">
                   <a
                       class="nav-link"
                       href="#"
                       id="navbarDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                       <div class="d-flex align-items-center">
                           <img src="../assets/img/Profile.png" alt="Profile" srcset="">
                           <div class="fw-bold fs-6 mx-3">Putra Siregar</div>
                       </div>
                   </a>
                   <ul class="dropdown-menu dropdown-menu" aria-labelledby="navbarDropdown">
                       <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                   </ul>
               </li>
           </ul>
       </div>
   </nav>