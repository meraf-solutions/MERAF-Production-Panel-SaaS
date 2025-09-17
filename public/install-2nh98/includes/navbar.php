        <!-- Loader -->
        <div id="loading-indicator" class="ajax-loader" style="display: none;">
            <img src="assets/images/ajax-loader.gif" alt="loading icon">
        </div>  

        <!-- Navbar Start -->
        <header id="topnav" class="defaultscroll sticky">
            <div class="container">
                <!-- Logo container-->
                <a class="logo" href="index.php">
                    <img src="assets/images/meraf-appLogo.png" height="56" class="logo-light-mode" alt="">
                    <img src="assets/images/meraf-appLogo.png" height="56" class="logo-dark-mode" alt="">
                </a>
                <!-- End Logo container-->
                <div class="menu-extras">
                    <div class="menu-item">
                        <!-- Mobile menu toggle-->
                        <a class="navbar-toggle" id="isToggle" onclick="toggleMenu()">
                            <div class="lines">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </a>
                        <!-- End mobile menu toggle-->
                    </div>
                </div>

                <div id="navigation">
                    <!-- Navigation Menu-->   
                    <ul class="navigation-menu nav-left">
                        <li><a href="index.php" class="sub-menu-item">Home</a></li>
                        <li><a href="install.php" class="sub-menu-item">Install Web App</a></li>
                        <li><a href="<?= $documentationURL ?>" class="sub-menu-item">Documentation</a></li>
                        <li><a href="mailto: <?= $companyContact ?>" class="sub-menu-item">Support Request</a></li>
                    </ul><!--end navigation menu-->
                </div><!--end navigation-->                

            </div><!--end container-->
        </header><!--end header-->
        <!-- Navbar End -->