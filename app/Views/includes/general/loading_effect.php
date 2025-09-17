        <?php if($myConfig['preloadEnabled']) { ?>
            <div id="preloader">
                <div id="status">
                    <div class="spinner">
                        <div class="double-bounce1"></div>
                        <div class="double-bounce2"></div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <!-- Toast -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 10000;"></div>
        
        <!-- Loader -->
        <div id="loading-indicator" class="ajax-loader" style="display: none;">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <img src="<?= base_url('assets/images/ajax-loader.gif') ?>" alt="loading icon">
                <div class="alert alert-info" role="alert" id="countdown-message" style="display: none;"></div>
            </div>
        </div>