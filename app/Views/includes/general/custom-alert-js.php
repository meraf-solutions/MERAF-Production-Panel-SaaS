<script type="text/javascript">
// Hide tooltip on click
$('[data-bs-toggle="tooltip"]').click(function(){
    $(this).tooltip('hide');
});

// Auto-close dissmisible alerts
function fadeOutAlerts(selector, delay) {
    window.setTimeout(function() {
        $(selector).fadeTo(500, 0).slideUp(500, function(){
            $(this).remove(); 
        });
    }, delay);
}

function resetForm(form, retainValueInput) {
    // Remove the "is-valid" class from all elements with class "is-valid"
    form.find('.is-valid').removeClass('is-valid');
    
    // Clear the values of all elements except those specified by retainValueInput
    form.find('input').not(retainValueInput).val('');
    
    // Reset select elements to their first (blank) option
    form.find('select').not(retainValueInput).each(function() {
        this.selectedIndex = 0;
    });
    
    // Clear the values of all elements except those specified by retainValueInput
    form.find('textarea').not(retainValueInput).val('');
}

function resetValidations(form) {
    form.find('.is-valid').removeClass('is-valid');
}
            
// Function to enable button loading effect
function enableLoadingEffect(button) {
    button.data('original-text', button.html()); // Save the original button text
    button.html('<span class="h6 mb-0"><?= lang('Pages.Loading_button') ?> <i class="mdi mdi-loading mdi-spin mb-0 align-middle"></i></span>').prop('disabled', true);
    $('#loading-indicator').show();
}

// Function to disable button loading effect
function disableLoadingEffect(button) {
    button.html(button.data('original-text')).prop('disabled', false);
    $('#loading-indicator').hide();
}

// Function to run the counter
function runCounter() {
    try {
        const counter = document.querySelectorAll('.counter-value');
        const speed = 2500; // The lower the slower

        counter.forEach(counter_value => {
            const updateCount = () => {
                const target = +counter_value.getAttribute('data-target');
                const count = +counter_value.innerText;

                // Lower inc to slow and higher to slow
                var inc = target / speed;

                if (inc < 1) {
                    inc = 1;
                }

                // Check if target is reached
                if (count < target) {
                    // Add inc to count and output in counter_value
                    counter_value.innerText = (count + inc).toFixed(0);
                    // Call function every ms
                    setTimeout(updateCount, 1);
                } else {
                    counter_value.innerText = target;
                }
            };

            updateCount();
        });
    } catch (err) {

    }
}

// Redirect page with/without countdown
function delayedRedirect(url, delay = 4000, countdownElement = document.getElementById('countdown-message')) {
    if (countdownElement) {

        // Show both the countdown element and its parent loader
        if (countdownElement === document.getElementById('countdown-message')) {
            countdownElement.style.display = 'block';
            document.getElementById('loading-indicator').style.display = 'flex';
        }
        
        var seconds = Math.floor(delay / 1000);
        var countdownTimer = setInterval(function() {
            seconds--;
            countdownElement.innerHTML = "You will be redirected in " + seconds + " second" + (seconds > 1 ? "s" : "") + "...";
            
            if (seconds <= 0) {
                clearInterval(countdownTimer);
            }
        }, 1000);
    }
    return setTimeout(function() {
        window.location.href = url;
    }, delay);
}

// Get the user's timezone using Intl API or from session if exists
<?= "const sessionTimezone = " . (session()->has('detected_timezone') ? "'" . session()->get('detected_timezone') . "'" : "null") . ";" ?>
const userTimezone = sessionTimezone ?? Intl.DateTimeFormat().resolvedOptions().timeZone;

function formatDateTime(data) {
    try {
        // Check for null, undefined, or invalid input
        if (data == null || data === '0000-00-00 00:00:00') {
            return 'N/A';
        }

        // Handle different input types
        let dateStr;
        
        // If it's an object with a date property
        if (typeof data === 'object' && data.date) {
            if (data.date === '0000-00-00 00:00:00') {
                return 'N/A';
            }
            dateStr = data.date;
        } 
        // If it's already a string
        else if (typeof data === 'string') {
            dateStr = data;
        }
        // If no valid date string found
        else {
            throw new Error('Invalid date format');
        }

        // Remove milliseconds if present
        dateStr = dateStr.split('.')[0];

        // Replace space with T for ISO format and add Z for UTC
        dateStr = dateStr.replace(' ', 'T') + 'Z';
        const date = new Date(dateStr);
        
        // Check if the date is valid
        if (isNaN(date.getTime())) {
            throw new Error('Invalid date');
        }

        // Format the date
        const formattedDate = new Intl.DateTimeFormat('en-US', {
            timeZone: userTimezone,
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        }).format(date);

        // Get GMT offset
        const formatter = new Intl.DateTimeFormat('en-US', {
            timeZone: userTimezone,
            timeZoneName: 'longOffset'
        });
        const offset = formatter.formatToParts(date)
            .find(part => part.type === 'timeZoneName')?.value
            .replace('GMT', '') || '+00:00';

        return `${formattedDate} (GMT${offset})`;
    } catch (error) {
        console.error('Datetime parsing error:', error);
        return 'Invalid Date';
    }
}

function convertDateFormat(data) {
    try {
        // Handle empty or invalid input
        if (!data) {
            return '';
        }

        // First, remove the GMT offset part
        const cleanData = data.replace(/\s*\(GMT[+-]\d{2}:\d{2}\)/, '');
        
        // Parse the date parts
        // Example input: "Jan 03, 2025, 12:39:04"
        const parts = cleanData.match(/([A-Za-z]+)\s+(\d{2}),\s+(\d{4}),\s+(\d{2}):(\d{2}):(\d{2})/);
        
        if (!parts) {
            throw new Error('Invalid date format');
        }

        // Get the month number (0-based index)
        const months = {
            'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
            'May': '05', 'Jun': '06', 'Jul': '07', 'Aug': '08',
            'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12'
        };

        const month = months[parts[1]];
        const day = parts[2];
        const year = parts[3];
        const hours = parts[4];
        const minutes = parts[5];
        const seconds = parts[6];

        // Return formatted string
        // console.log('Converted value: ' + `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`);
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    } catch (error) {
        console.error('Date conversion error:', error);
        return 'Invalid Date';
    }
}

// Send the timezone to the server when the page loads
<?php if (!session()->has('detected_timezone')) : ?>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/set-timezone', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({ timezone: userTimezone })
        });
    });
<?php endif; ?>

// Add timezone header to all fetch requests
// const originalFetch = window.fetch;
// window.fetch = function(url, options = {}) {
//     if (!options.headers) {
//         options.headers = {};
//     }
//     options.headers['X-Timezone'] = userTimezone;
//     return originalFetch(url, options);
// };

// Function to show specific toast
function updateToastTheme() {
    const isDarkMode = document.documentElement.getAttribute("data-bs-theme") === "dark";
    document.querySelectorAll(".toast-body").forEach(toastBody => {
        toastBody.classList.toggle("bg-dark", isDarkMode);
        toastBody.classList.toggle("text-light", isDarkMode);
        toastBody.classList.toggle("bg-light", !isDarkMode);
        toastBody.classList.toggle("text-dark", !isDarkMode);
    });
}

// Initialize
updateToastTheme();

// Function to show specific toast
function showToast(type, message) {
    // Add vibration for danger type on mobile
    if (type === 'danger' && navigator.vibrate) {
        navigator.vibrate(200); // Vibrate for 200ms
    }

    const container = document.querySelector('.toast-container');

    // Toast header mappings
    const toastHeaderIcon = {
        'success': 'check-circle',
        'danger': 'close-octagon',
        'warning': 'alert',
        'info': 'information'
    };

    const toastHeaderMapping = {
        'success': '<?= lang('Pages.Success') ?>',
        'danger': '<?= lang('Pages.Error') ?>',
        'warning': '<?= lang('Pages.Attention') ?>',
        'info': '<?= lang('Pages.Info') ?>'
    };

    const headerIcon = toastHeaderIcon[type] || 'information'; // Fallback icon
    const headerText = toastHeaderMapping[type] || '<?= lang('Pages.Info') ?>'; // Fallback text

    const toastHtml = `
        <div class="toast mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header text-bg-${type} border-bottom-0">
                <i class="rounded me-2 mdi mdi-${headerIcon}"></i>
                <strong class="me-auto">${headerText}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-light text-dark border border-${type} bg-transparent text-${type} border-top-0">
                ${message}
            </div>
        </div>
    `;
    
    // Add toast to container
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    // Get the newly added toast
    const newToast = container.lastElementChild;
    
    // Initialize and show toast
    const toast = new bootstrap.Toast(newToast, {
        autohide: true,
        delay: 4000
    });
    
    toast.show();
    
    // Remove toast element after it's hidden
    newToast.addEventListener('hidden.bs.toast', () => {
        newToast.remove();
    });
}
</script>