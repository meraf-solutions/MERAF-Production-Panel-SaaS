document.addEventListener('DOMContentLoaded', function() {
    const notificationIcon = document.getElementById('notification-icon');
    const notificationBadge = document.getElementById('notification-badge');
    const notificationList = document.getElementById('notification-list');
    const unreadCount = document.getElementById('unread-count');
    const markAllAsReadBtn = document.getElementById('mark-all-as-read');
    let page = 1;
    let loading = false;

    function debounce(fn, delay) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function loadNotifications(append = false) {
        if (loading) return;
        loading = true;

        if (!append) {
            notificationList.innerHTML = '<div class="text-center p-3"><span class="h6 mb-0"><i class="mdi mdi-loading mdi-spin mb-0 align-middle"></i></span></div>';
        }

        fetch(`/notification/getUnreadNotifications?page=${page}`)
            .then(response => response.json())
            .then(data => {
                if (!append) {
                    notificationList.innerHTML = '';
                }
            
                if (unreadCount) unreadCount.textContent = data.unreadCount;
            
                if (notificationIcon && notificationBadge) {
                    if (data.unreadCount > 0) {
                        notificationIcon.classList.add('text-danger');
                        notificationBadge.classList.remove('d-none');
                    } else {
                        notificationIcon.classList.remove('text-danger');
                        notificationBadge.classList.add('d-none');
                    }
                }
            
                if (data.notifications.length === 0) {
                    notificationList.innerHTML = `
                        <div class="text-center text-muted p-4">
                            <i class="ti ti-bell-off fs-1 mb-2"></i>
                            <p class="mb-0">${lang_NoNotification}</p>
                        </div>
                    `;
                    notificationList.removeEventListener('ps-scroll-y', handleScroll);
                } else {
                    data.notifications.forEach(notification => {
                        const notificationItem = createNotificationItem(notification);
                        notificationList.appendChild(notificationItem);
                    });
                }
            
                loading = false;
            })            
            .catch(error => {
                console.error('Error loading notifications:', error);
                loading = false;
            });
    }

    function createNotificationItem(notification) {
        const notificationItem = document.createElement('div');
        notificationItem.className = 'dropdown-item features feature-primary key-feature pt-2 pb-2 border-bottom';
        notificationItem.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="icon text-center rounded-circle me-2">
                    <i class="ti ti-bell"></i>
                </div>
                <div class="flex-1 notification-info">
                    <h6 class="mb-0 text-dark title">${notification.message}</h6>
                    <small class="text-muted">${formatDateTime(notification.created_at)}</small>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-icon btn-close" style="background: unset;" data-notification-id="${notification.id}">
                        <i class="uil uil-times fs-4 text-danger"></i>
                    </button>
                </div>
            </div>
        `;

        notificationItem.addEventListener('click', function(event) {
            if (!event.target.closest('.btn-close')) {
                if (notification.link) {
                    window.location.href = notification.link;
                }
                markAsRead(notification.id);
            }
        });

        const closeButton = notificationItem.querySelector('.btn-close');
        closeButton.addEventListener('click', function(event) {
            event.stopPropagation();

            const notificationId = this.getAttribute('data-notification-id');
            
            // Store reference to original icon before removing
            const originalIcon = this.querySelector('i');
            const originalIconClass = originalIcon ? originalIcon.className : 'uil uil-times fs-4 text-danger';
            
            // Clear the existing icon
            if (originalIcon) {
                originalIcon.remove();
            }

            // Create new loading icon
            const loadingIcon = document.createElement('i');
            loadingIcon.className = 'mdi mdi-loading mdi-spin mb-0 align-middle text-danger';
            
            // Append the new icon
            this.appendChild(loadingIcon);

            markAsRead(notificationId, this, originalIconClass);
        });

        return notificationItem;
    }

    function restoreOriginalIcon(buttonElement, originalIconClass) {
        if (buttonElement && originalIconClass) {
            // Remove loading icon
            const loadingIcon = buttonElement.querySelector('.mdi-loading');
            if (loadingIcon) {
                loadingIcon.remove();
            }
            
            // Restore original icon
            const restoredIcon = document.createElement('i');
            restoredIcon.className = originalIconClass;
            buttonElement.appendChild(restoredIcon);
        }
    }

    function markAsRead(id, buttonElement = null, originalIconClass = null) {
        if (!id) {
            console.error('Invalid notification ID');
            restoreOriginalIcon(buttonElement, originalIconClass);
            return;
        }

        fetch(`/notification/markAsRead/${id}`, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    page = 1;
                    loadNotifications();
                } else {
                    console.error('Failed to mark notification as read:', data.message);
                    restoreOriginalIcon(buttonElement, originalIconClass);
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
                restoreOriginalIcon(buttonElement, originalIconClass);
            });
    }

    function markAllAsRead() {
        const defaultText = markAllAsReadBtn.innerHTML;

        markAllAsReadBtn.innerHTML = '<i class="ti ti-checks"></i> Initiating...';
        markAllAsReadBtn.disabled = true;

        fetch('/notification/markAllAsRead', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
            .then(response => response.text())
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    throw new Error('Invalid JSON response');
                }
            })
            .then(data => {
                if (data.success) {
                    loadNotifications();
                    showToast('success', 'Successfully marked all notifications as read.');
                } else {
                    console.error('Failed to mark all as read:', data.message);
                    showToast('danger', 'Error occurred while marking all notifications as read.');
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
                showToast('danger', 'Error occurred while marking all notifications as read.');
            })
            .finally(() => {
                markAllAsReadBtn.innerHTML = defaultText;
                markAllAsReadBtn.disabled = false;
            });
    }

    function handleScroll(event) {
        const { scrollTop, scrollHeight, clientHeight } = event.target;
        if (scrollTop + clientHeight >= scrollHeight - 5 && !loading) {
            page++;
            loadNotifications(true);
        }
    }

    loadNotifications();
    setInterval(() => {
        page = 1;
        loadNotifications();
    }, 60000);

    if (markAllAsReadBtn) {
        markAllAsReadBtn.addEventListener('click', function () {
            markAllAsRead();
        });
    } else {
        console.error('markAllAsReadBtn not found in the DOM');
    }

    try {
        const simpleBar = new SimpleBar(document.getElementById('notification-dropdown'));
        simpleBar.getScrollElement().addEventListener('scroll', debounce(handleScroll, 200));
    } catch (e) {
        console.warn('SimpleBar not initialized:', e);
    }

    // Make loadNotifications available globally
    window.loadNotifications = loadNotifications;
});
