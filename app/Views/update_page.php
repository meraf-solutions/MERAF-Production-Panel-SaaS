<?php if($userData->id !== 1) { header("Location:" . base_url());exit(); } ?>

<?= $this->extend('layouts/single_page') ?>

<?= $this->section('content') ?>
	<section class="bg-home bg-circle-gradiant d-flex align-items-center">
		<div class="bg-overlay bg-overlay-white"></div>
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="card form-signin p-4 rounded shadow">
							<a href="<?= base_url() ?>"><img src="<?= $myConfig['appIcon'] ?>" class="avatar avatar-small mb-4 d-block mx-auto" alt="Logo"></a>
							<h5 class="mb-3 text-center"><?= $myConfig['appName'] ?></h5>
							<h6 class="mb-3 text-center"><?= $headingText ?></h6>

							<?php
							$disableUpdate = false;
							if(isset($CIlatestVersion) && isset($CIinstalledVersion)) {
								if($CIlatestVersion !== $CIinstalledVersion) {
									echo '<p class="mb-3 text-muted text-center">
												<span class="text-danger">v' . $CIinstalledVersion . '</span> &#8212;> <span class="text-success">' . $CIlatestVersion . '</span>
											</p>';
								}
								else {
									echo '<div class="alert alert-info fade show text-center" role="alert">'.lang('Pages.Latest_CodeIgniter_version').'</div>';
									$disableUpdate = true;
								}									
							}
							?>

							<?php
							if(isset($CIlatestVersion) || isset($CIinstalledVersion)) {
								// Check if exec() function is enabled								
								if (!function_exists('exec')) {
									echo '<div class="alert alert-danger fade show text-center" role="alert">'.lang('Pages.php_exec_error_notif').'</div>';
									$disableUpdate = true;
								}
								else {
									// Execute the 'composer --version' command
									exec('composer --version', $output, $returnCode);

									// Check if Composer command executed successfully and returned the version
									if ($returnCode === 0) {
										// echo "Composer is installed. Version: " . implode(PHP_EOL, $output);
									} else {
										$disableUpdate = true;
										echo '<div class="alert alert-danger fade show text-center" role="alert">'.lang('Pages.composer_error').'</div>';
									}
								}
							}
							?>						

							<div class="col-12" id="responseMsg"></div>

							<?php if(isset($CIlatestVersion)) { ?>
								<div class="col-12" id="composerLog"></div>
							<?php } ?>
			
							<button class="btn btn-primary w-100 mb-3" id="proceed-updater-confirm" <?php echo $disableUpdate ? 'disabled' : ''; ?> ><i class="uil uil-arrow-circle-up"></i> <?= $submitButton ?></button>
							<a href="<?= base_url() ?>" class="btn btn-secondary w-100" id="back-to-home" ><i class="uil uil-home"></i>  <?= lang('Pages.back_to_home') ?></a>

							<p class="mb-0 text-muted mt-3 text-center">
								<?= lang('Pages.footer_copyright', ['year' => date("Y"),'appName' => $myConfig['appName']]) ?>
							</p>

					</div>
				</div>
			</div>
		</div> <!--end container-->
	</section><!--end section-->
<?= $this->endSection() //End section('content')?>

<?= $this->section('modals') ?>
	<?php if(isset($CIlatestVersion)) { ?>
		<div class="modal fade" id="modal-composerLog" tabindex="-1" aria-labelledby="modal-composerLog-title" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
				<div class="modal-content rounded shadow border-0">
					<div class="modal-header border-bottom">
						<h5 class="modal-title" id="modal-composerLog-title"><?= lang('Pages.Composer_Output') ?></h5>
						<button type="button" class="btn btn-icon btn-close" data-bs-dismiss="modal" id="close-modal"><i class="uil uil-times fs-4 text-dark"></i></button>
					</div>
					<div class="modal-body">

					</div>
				</div>
			</div>
		</div>
	<?php } ?>	
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
	<script type="text/javascript">
		$(document).ready(function() {
			/****************
			// Handle updater
			****************/
			$('#proceed-updater-confirm').click(function(e) {
				e.preventDefault();                                        
				var updateCodeigniter = <?= isset($CIinstalledVersion) ? 'true' : 'false' ?>;
				var submitButton = $(this);

				if(updateCodeigniter === true) {
					var confirmUpdate = confirm("<?= lang('Pages.CI_updating_warning') ?>");

					if(confirmUpdate) {
						proceedWithUpdate(submitButton);
					}
				} else {
					proceedWithUpdate(submitButton);
				}
			});

			function proceedWithUpdate(submitButton) {
				var updateCodeigniter = <?= isset($CIinstalledVersion) ? 'true' : 'false' ?>;
				var composerLogResponse = $('#composerLog');
				var responseWrapper = $('#responseMsg');

				// enable button loading effect
				enableLoadingEffect(submitButton);
				
				// Make an AJAX request
				$.ajax({
					url: '<?= $actionURL ?>',
					method: 'GET',
					dataType: 'json', // Specify the expected data type
					success: function (response) {
						console.log(response.composerLog);
						if(updateCodeigniter === true) {
							if(response.composerLog) {
								var logContent = response.composerLog.replace(/",/g, '<br>').replace(/\["/g, '- ').replace(/\"\]/g, '').replace(/"/g, '- ').replace(/\] 0 \[/g, '').replace(/0 \[/g, '').replace(/-]/g, '');
								composerLogResponse.slideUp();
								composerLogResponse.html('<div class="alert alert-info alert-dismissible fade show" role="alert"><?= lang('Notifications.composer_update_message') ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> </button></div>');
								composerLogResponse.slideDown();

								// Set logContent inside modal body
								var modalBody = document.querySelector('#modal-composerLog .modal-body');
								modalBody.innerHTML = logContent;                                
							}
						}
												
						if (response.status == 1) {
							// Response fully success
							responseWrapper.slideUp();
							responseWrapper.html('<div class="alert alert-success alert-dismissible fade show text-center" role="alert">' + response.msg + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> </button></div>');
							responseWrapper.slideDown();
							submitButton.hide();
						} else {    
							// Response error in processing the request
							responseWrapper.slideUp();
							responseWrapper.html('<div class="alert alert-danger alert-dismissible fade show text-center" role="alert">' + response.msg + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> </button></div>');
							responseWrapper.slideDown();                  
						}
					},
					error: function (xhr, status, error) {
						// Response
						responseWrapper.slideUp();
						responseWrapper.html('<div class="alert alert-danger alert-dismissible fade show text-center" role="alert"><?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> </button></div>');
						responseWrapper.slideDown();
					},
					complete: function () {
						disableLoadingEffect(submitButton);
					}
				});
			}		
		});
	</script>
<?= $this->endSection() ?>