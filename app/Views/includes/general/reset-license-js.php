<script type="text/javascript">
	$(document).ready(function() {
        
		/*******************************************
		// Handle the search license detail requests
		*******************************************/
        $('#search-license-button').on('click', function (e) {
            e.preventDefault();

            var form = $('#search-license-form');
            var licenseInput = $('#licenseInput');
            var submitButton = $(this);
            var selectWrapper = $('#select-domain-device-wrapper');
            var hiddenInputLicense = $('#verified-license');
            var domainWrapper = $('#domain-table');
            var tableDomain = $('#domain-list');
            var deviceWrapper = $('#device-table');
            var tableDevice = $('#device-list');
            var captcha = $('#captcha');
            var noDomainDeviceNotification = $('#no_registered_domain_device');

            // reset the tables and hide
            selectWrapper.hide();
            $('#checkAll-domain').prop('checked', false);
            $('#checkAll-device').prop('checked', false);
            tableDomain.html('');
            domainWrapper.hide();
            tableDevice.html('');
            deviceWrapper.hide();

            // Define a regular expression for not allowed characters
            var disallowedCharsRegex_licenseKey = /[~!#$%&*\-_+=|:.]/;
            var disallowedCharsRegex_numeric = /[^0-9]/;

            // enable button loading effect
            enableLoadingEffect(submitButton);

            // Remove existing 'is-invalid' classes
            form.find('.is-invalid').removeClass('is-invalid');

            // Start validations

            // Iterate over other license key field and validate
            if (licenseInput.val() === '') {
                licenseInput.addClass('is-invalid');

                selectWrapper.slideUp();

                // Disable loading effect
                disableLoadingEffect(submitButton);
            // } else if (disallowedCharsRegex_licenseKey.test(licenseInput.val()) || licenseInput.val().length !== 40) {
            //     licenseInput.addClass('is-invalid');
            } else {
                licenseInput.addClass('is-valid');
            }

            // Iterate captcha
            if (captcha.val() === '') {
                captcha.addClass('is-invalid');

                selectWrapper.slideUp();

                // Disable loading effect
                disableLoadingEffect(submitButton);
            } else if (disallowedCharsRegex_numeric.test(captcha.val())) {
                captcha.addClass('is-invalid');

                selectWrapper.slideUp();
                // Disable loading effect
                disableLoadingEffect(submitButton);
            } else {
                captcha.addClass('is-valid');
            }            
            // End validations

            // Check if there are any elements with 'is-invalid' class
            if (form.find('.is-invalid').length === 0) {
                // Use AJAX to submit the form data
                $.ajax({
                    url: '<?= base_url('reset-license/search') ?>',
                    method: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        if (response.status == 1) {
                            var licenseDetails = response.data;
                            // Response fully success
                            noDomainDeviceNotification.hide();

                            // build the tables
                            // for domain list
                            if (licenseDetails['registered_domains'].length > 0) {
                                var domainHtml = '';
                                $.each(licenseDetails['registered_domains'], function (key, domain) {
                                    domainHtml += '<tr>';
                                    domainHtml += '<td class="p-3"><div class="form-check"><input class="form-check-input" type="checkbox" value="' + domain.domain_name + '" id="' + domain.domain_name + '" name="' + domain.domain_name + '"></div></td>';
                                    domainHtml += '<td class="align-middle"><label for="' + domain.domain_name + '" class="form-label">' + domain.domain_name + '</label></td>';
                                    domainHtml += '</tr>';
                                });
                                tableDomain.html(domainHtml);
                                domainWrapper.show();
                                selectWrapper.show();
                            }

                            // for device list
                            if (licenseDetails['registered_devices'].length > 0) {
                                var deviceHtml = '';
                                $.each(licenseDetails['registered_devices'], function (key, device) {
                                    deviceHtml += '<tr>';

                                    deviceHtml += '<td class="p-3"><div class="form-check"><input class="form-check-input" type="checkbox" value="' + device.device_name + '" id="' + device.device_name + '" name="' + device.device_name + '"></div></td>';
                                    deviceHtml += '<td class="align-middle"><label for="' + device.device_name + '" class="form-label">' + device.device_name + '</label></td>';
                                    
                                    deviceHtml += '</tr>';
                                });
                                tableDevice.html(deviceHtml);
                                deviceWrapper.show();
                                selectWrapper.show();
                            }

                            if( (licenseDetails['registered_domains'].length === 0) && (licenseDetails['registered_devices'].length === 0) ) {
                                noDomainDeviceNotification.slideDown();
                            }

                            hiddenInputLicense.val(licenseDetails['license_key']);
                        } else if (response.status == 2) {
                            // Response success but with error
                            showToast('info', response.msg);
                        } else {
                            // Response error in processing the request
                            showToast('danger', response.msg);
                        }
                    },
					error: function (xhr, status, error) {
                        // Show error toast
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
					},
                    complete: function () {
                        disableLoadingEffect(submitButton);
                    }
                });
            }
        });

		/***********************************
		// Handle the delete detail requests
		***********************************/
        $('#delete-domain-device-submit').on('click', function (e) {
            e.preventDefault();

            var form = $('#remove-domain-device-form');
            var searchLicenseForm = $('#search-license-form');
            var submitButton = $(this);
            var selectWrapper = $('#select-domain-device-wrapper');
            var tableDomain = $('#domain-list');
            var tableDevice = $('#device-list');            

            var selectedDomain = [];
            $('tbody#domain-list input[type="checkbox"]:checked').each(function () {
                selectedDomain.push($(this).val());
            });

            form.find('input[name="selected-domain"]').val(selectedDomain.join(','));

            var selectedDevice = [];
            $('tbody#device-list input[type="checkbox"]:checked').each(function () {
                selectedDevice.push($(this).val());
            });

            form.find('input[name="selected-device"]').val(selectedDevice.join(','));

			// Enable loading effect
			enableLoadingEffect(submitButton);

            // Display a confirmation dialog box
            var confirmDelete = confirm("<?= lang('Pages.confirmation_delete_selected') ?>");

            if (confirmDelete) {                
				// Proceed with AJAX request if user confirms
				$.ajax({
                    url: '<?= base_url('reset-license/delete-selected') ?>',
                    method: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        let toastType = 'info';

                        if (response.status == 1) {
                            toastType = 'success';
                            resetForm(searchLicenseForm);
                            resetValidations(searchLicenseForm);   
                            selectWrapper.hide(); 
                            tableDomain.html('');
                            tableDevice.html('');
                        } else if (response.status == 2) {
                            toastType = 'info';
                            resetForm(searchLicenseForm);
                            resetValidations(searchLicenseForm);   
                            selectWrapper.hide(); 
                            tableDomain.html('');
                            tableDevice.html('');
                        } else {
                            toastType = 'danger';
                        }

                        showToast(toastType, response.msg);
					},
					error: function (xhr, status, error) {
						// Show error toast
                        showToast('danger', '<?= lang('Pages.ajax_no_response') ?>' + status.toUpperCase() + ' ' + xhr.status);
					},
                    complete: function () {
                        disableLoadingEffect(submitButton);
                    }
				});
            } else {
				// User cancelled the deletion action
				// Disable loading effect
				disableLoadingEffect(submitButton);
			}
        });

    });

	// Function to enable/disable the delete button based on checkbox state
    function updateDeleteButtonState() {
        var anyCheckboxChecked1 = $('tbody#domain-list input[type="checkbox"]:checked').length > 0;
        var anyCheckboxChecked2 = $('tbody#device-list input[type="checkbox"]:checked').length > 0;
        $('#delete-domain-device-submit').prop('disabled', !(anyCheckboxChecked1 || anyCheckboxChecked2));      
    }    

	/****
	 * For domain selection
	 */	
	// Check/uncheck all checkboxes when "checkAll-domain" is clicked
	$('#checkAll-domain').on('change', function () {
		var isChecked = $(this).prop('checked');
		$('tbody#domain-list').find('input[type="checkbox"]').prop('checked', isChecked);
		updateDeleteButtonState();
	});

	// Check/uncheck "checkAll-domain" based on the state of individual checkboxes
	$(document).on('change', 'tbody#domain-list input[type="checkbox"]', function () {
		var allChecked = $('tbody#domain-list input[type="checkbox"]:checked').length === $('tbody#domain-list input[type="checkbox"]').length;
		$('#checkAll-domain').prop('checked', allChecked);
		updateDeleteButtonState();
	});

	// Uncheck "checkAll-domain" if any individual checkbox is unchecked
	$(document).on('change', 'tbody#domain-list input[type="checkbox"]', function () {
		if (!$(this).prop('checked')) {
			$('#checkAll-domain').prop('checked', false);
		}
	});	

	/****
	 * For device selection
	 */	
	// Check/uncheck all checkboxes when "checkAll-device" is clicked
	$('#checkAll-device').on('change', function () {
		var isChecked = $(this).prop('checked');
		$('tbody#device-list').find('input[type="checkbox"]').prop('checked', isChecked);
		updateDeleteButtonState();
	});

	// Check/uncheck "checkAll-device" based on the state of individual checkboxes
	$(document).on('change', 'tbody#device-list input[type="checkbox"]', function () {
		var allChecked = $('tbody#device-list input[type="checkbox"]:checked').length === $('tbody#device-list input[type="checkbox"]').length;
		$('#checkAll-device').prop('checked', allChecked);
		updateDeleteButtonState();
	});

	// Uncheck "checkAll-device" if any individual checkbox is unchecked
	$(document).on('change', 'tbody#device-list input[type="checkbox"]', function () {
		if (!$(this).prop('checked')) {
			$('#checkAll-device').prop('checked', false);
		}
	});	    

	// Call the function initially to set the button state
	updateDeleteButtonState();	    
</script>