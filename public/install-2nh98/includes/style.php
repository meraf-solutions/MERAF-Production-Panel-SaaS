        <!-- Bootstrap Css --> 
        <link href="assets/css/bootstrap.min.css" id="bootstrap-style" class="theme-opt" rel="stylesheet" type="text/css">
        <!-- Icons Css -->
        <!-- <link href="assets/libs/@mdi/font/css/materialdesignicons.min.css" rel="stylesheet" type="text/css"> -->
        <link href="assets/libs/@iconscout/unicons/css/line.css" type="text/css" rel="stylesheet">
        <!-- Style Css-->
        <link href="assets/css/style.min.css" id="color-opt" class="theme-opt" rel="stylesheet" type="text/css">

        <style>
            .full-page-background {
                background-image: url('assets/images/installer-bg.jpg');
                background-size: 150% 150%;
                background-position: center center;
            }

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

            .spinning-icon {
                display: flex;
                justify-content: center;
                align-items: center;
                animation: spin 2s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }

        </style>