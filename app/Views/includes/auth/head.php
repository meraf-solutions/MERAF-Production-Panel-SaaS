        <?php if(!isset($myConfig)) {$myConfig = getMyConfig('', 0); } ?>
        <meta charset="utf-8" />        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="MERAF Production Portal for theme and app licensing" />
        <meta name="keywords" content="MERAF, Chatpiopn, Excel Maco, Excel VBA" />
        <meta name="author" content="MERAF Digital Solutions" />
        <meta name="email" content="contact@merafsolutions.com" />
        <meta name="website" content="https://merafsolutions.com" />
        <meta name="Version" content="v1.0" />
        
        <?php 
        // Initialize theme variable
        $theme = $myConfig['defaultTheme'];
        
        if($myConfig['defaultTheme'] === 'system') { 
            // For system theme, we'll get the actual scheme from JavaScript ?>
                <script type="text/javascript">
                // Function to detect color scheme preference
                function get_color_scheme() {
                        return (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) ? "dark" : "light";
                }

                // Function to update the color_scheme cookie with secure options
                function update_color_scheme() {
                        var scheme = get_color_scheme();
                        
                        // Calculate expiration date (1 year from now)
                        var expirationDate = new Date();
                        expirationDate.setTime(expirationDate.getTime() + (31536000 * 1000)); // 365 days in seconds
                        
                        // Format the date for cookie expiration
                        var expires = "expires=" + expirationDate.toUTCString();
                        
                        // Build cookie string with all parameters
                        var cookieValue = "color_scheme=" + encodeURIComponent(scheme) + "; " + 
                                        expires + "; " + 
                                        "path=/; " + 
                                        "secure; " + 
                                        "samesite=Strict";
                        
                        // Set the cookie
                        document.cookie = cookieValue;
                        
                        return scheme;
                }

                // Initial setup
                var current_scheme = update_color_scheme();
                
                // Listen for changes in the system color scheme and update the cookie accordingly
                if (window.matchMedia) {
                        window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", function() {
                            update_color_scheme();
                            location.reload(); // Reload to apply new theme
                        });
                }
                </script>
                <?php 
                // Set theme based on detected system preference
                $theme = isset($_COOKIE["color_scheme"]) ? $_COOKIE["color_scheme"] : "light"; // Default to light if cookie not set
                ?>
        <?php }
        else { ?>
                <script type="text/javascript">
                // Function to update the color_scheme cookie
                function update_color_scheme() {
                        Cookies.set("color_scheme", '<?= $myConfig['defaultTheme'] ?>', {
                                path: '/',
                                secure: true,
                                sameSite: 'Strict',
                                maxAge: 31536000
                        });
                }
                update_color_scheme();
                </script>
        <?php } ?>

        <?php
        $direction = service('request')->getLocale() === 'ar' ? '-rtl' : '';
        
        // Only allow theme cookie override if not system preference
        if(isset($_COOKIE["theme"])) {
            $theme = $_COOKIE['theme'];
        }

        if(strpos($theme, 'dark') !== false) {
                // default style
                $bootstrapCSS = base_url('assets/css/bootstrap-dark' . $direction . '.min.css');
                $styleCSS = base_url('assets/css/style-dark' . $direction . '.min.css');
                
                // Set the app's logo
                $appLogo = $myConfig['appLogo_dark'];
        }
        else {
                $bootstrapCSS = base_url('assets/css/bootstrap' . $direction . '.min.css');
                $styleCSS = base_url('assets/css/style' . $direction . '.min.css'); 
                
                // Set the app's logo
                $appLogo = $myConfig['appLogo_light'];
        }
        
        // Set the app's icon
        $appIcon = $myConfig['appIcon'];
        ?>

        <!-- favicon -->
        <link rel="shortcut icon" href="<?= $appIcon ?>">
        
        <!-- Css -->
        <!-- <link href="<?= base_url('assets/libs/tiny-slider/tiny-slider.css')?>" rel="stylesheet"> -->
        <!-- Bootstrap Css -->
        <link href="<?= $bootstrapCSS ?>" id="bootstrap-style" class="theme-opt" rel="stylesheet" type="text/css">
        <!-- Icons Css -->
        <link href="<?= base_url('assets/libs/@mdi/font/css/materialdesignicons.min.css')?>" rel="stylesheet" type="text/css">
        <link href="<?= base_url('assets/libs/@iconscout/unicons/css/line.css')?>" type="text/css" rel="stylesheet">
        <!-- Style Css-->
        <link href="<?= $styleCSS ?>" id="color-opt" class="theme-opt" rel="stylesheet" type="text/css">

        <style>
        .ajax-loader {
                background-color: rgba(58,122,157,0.5);
                position: fixed;
                z-index: 9999;
                width: 100%;
                height: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
        }

        <?php if($theme === '' || (strpos($theme, 'dark') !== false) ) { ?>
                #preloader {
                        background-image: linear-gradient(45deg, #1c2836, #1c2836);
                }
        <?php } ?>        
        </style>
