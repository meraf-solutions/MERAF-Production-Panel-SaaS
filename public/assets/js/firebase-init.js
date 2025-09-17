// Firebase initialization and token management

document.addEventListener('DOMContentLoaded', function() {
	if (!firebase.messaging.isSupported()) {
		return;
	}

	const firebaseConfig = {
		apiKey: "AIzaSyCaYTzJlW7GnUvIh53gCvJPCFjCrPkKPDY",
		authDomain: "meraf-production-panel.firebaseapp.com",
		projectId: "meraf-production-panel",
		storageBucket: "meraf-production-panel.firebasestorage.app",
		messagingSenderId: "828627714480",
		appId: "1:828627714480:web:4ff282be255f86571fd675",
		measurementId: "G-VFV21JNX7L"
	};

	firebase.initializeApp(firebaseConfig);

	let messaging;
	let swRegistration;

	const enableNotificationsBtn = document.getElementById('enable-notifications');
	const allowWebpushPrompt = document.getElementById('webpush-allow');
	const disallowWebpushPrompt = document.getElementById('webpush-deny');
	const webpushWindowPrompt = document.getElementById('webpush-prompt');
	const registeredNotificationBtn = document.getElementById('registered-device');
	const defaultText = enableNotificationsBtn ? enableNotificationsBtn.innerHTML : '';
	const webpushPromptDefaultText = allowWebpushPrompt ? allowWebpushPrompt.innerHTML : '';

	const deviceId = getDeviceId();
	const viewMode = isStandaloneMode() ? 'standalone' : 'browser';

	fetch('/notification/current-device', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-Requested-With': 'XMLHttpRequest'
		},
		body: JSON.stringify({ deviceId })
	});

	function generateDeviceId() {
		const fingerprint = `${navigator.userAgent}|${screen.width}x${screen.height}|${screen.colorDepth}|${Intl.DateTimeFormat().resolvedOptions().timeZone}|${navigator.language}`;
		let hash = 0;
		for (let i = 0; i < fingerprint.length; i++) {
			hash = ((hash << 5) - hash) + fingerprint.charCodeAt(i);
			hash = hash & 0xFFFFFFFF;
		}
		hash = hash & 0x7FFFFFFF;
		return hash.toString(16);
	}

	function getDeviceId() {
		let deviceId = getCookie('device_id');
		if (!deviceId) {
			deviceId = generateDeviceId();
			console.log('Current device ID: ' + deviceId);
			setCookie('device_id', deviceId, 365);
		}
		return deviceId;
	}

	function setCookie(name, value, days) {
		const date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		document.cookie = `${name}=${value}; expires=${date.toUTCString()}; path=/; SameSite=Lax`;
	}

	function getCookie(name) {
		const nameEQ = name + '=';
		const ca = document.cookie.split(';');
		for (let c of ca) {
			while (c.charAt(0) === ' ') c = c.substring(1);
			if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length);
		}
		return null;
	}

	function requestNotificationPermission() {
		enableNotificationsBtn.innerHTML = `<i class="ti ti-bell"></i> ${lang_CheckingPermisisions}`;
		enableNotificationsBtn.disabled = true;

		allowWebpushPrompt.innerHTML = lang_CheckingPermisisions;
		allowWebpushPrompt.disabled = true;


		Notification.requestPermission().then(permission => {
			if (permission === 'granted') {
				if ('serviceWorker' in navigator) {
					navigator.serviceWorker.register('/service-worker.js', { scope: '/' })
						.then(function(registration) {
							console.log('✅ FCM Service Worker registered with scope:', registration.scope);
							navigator.serviceWorker.ready.then((readyRegistration) => {
								console.log('✅ FCM Service Worker is active and ready.');
								getTokenWithRetry(readyRegistration);
							});
						}).catch(function(err) {
							console.error('❌ FCM Service Worker registration failed:', err);
							showToast('danger', lang_Failed_to_register_SW);
							resetNotificationButton();
						});
				}
			} else {
				showToast('danger', lang_Permission_denied);
				resetNotificationButton();
			}
		});
	}

	function getTokenWithRetry(registration, retries = 5, delay = 2000) {
		messaging.getToken({
			vapidKey: 'BPL_95JEVtLgctB-IP6IiJyZRZ5fzdROZnkeU7zknM8-BlJHGw83QkVo_vMWoAnsBbQYVL1FCbH0XioIGsDZGRY',
			serviceWorkerRegistration: registration
		})
		.then(currentToken => {
			if (currentToken) {
				saveTokenToServer(currentToken).then(success => {
					if (success) {
						enableNotificationsBtn.style.display = 'none';
						webpushWindowPrompt.style.display = 'none';
						registeredNotificationBtn.style.display = 'inline-block';
						showToast('success', lang_Success_enabled_push_notification);
					} else {
						showToast('info', lang_Unable_to_verify_permission);
						resetNotificationButton();
					}
				});
			} else if (retries > 0) {
				console.log(`Token not ready. Retrying in ${delay}ms...`);
				setTimeout(() => getTokenWithRetry(registration, retries - 1, delay), delay);
			} else {
				console.error('Token still not available after retries.');
				showToast('danger', lang_Failed_confirming_permission);
				resetNotificationButton();
			}
		})
		.catch(err => {
			console.error('Error retrieving token:', err);
			showToast('danger', lang_Error_retrieving_device_token);
			resetNotificationButton();
		});
	}

	function resetNotificationButton() {
		enableNotificationsBtn.innerHTML = defaultText;
		enableNotificationsBtn.disabled = false;

		showWebpushPrompt(true);
		allowWebpushPrompt.innerHTML = webpushPromptDefaultText;
		allowWebpushPrompt.disabled = false;
	}

	function saveTokenToServer(token) {
		const deviceInfo = {
			userAgent: navigator.userAgent,
			deviceId,
			platform: navigator.platform,
			screenSize: `${screen.width}x${screen.height}`,
			mode: viewMode
		};

		enableNotificationsBtn.innerHTML = `<i class="ti ti-bell"></i> ${lang_Registering_device}`;
		enableNotificationsBtn.disabled = true;

		allowWebpushPrompt.innerHTML = lang_Registering_device;
		allowWebpushPrompt.disabled = true;

		return fetch('/notification/registerToken', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
				'X-Device-ID': deviceId
			},
			body: JSON.stringify({ token, device: JSON.stringify(deviceInfo) })
		})
			.then(response => response.json())
			.then(data => data.success)
			.catch(error => {
				console.error('Error saving token to server:', error);
				return false;
			});
		
	}

	// Check notification subscription and token
	function checkNotificationStatus() {
		if (!messaging || !swRegistration) return;

		if (Notification.permission === 'granted') {
			messaging.getToken({
				vapidKey: 'BPL_95JEVtLgctB-IP6IiJyZRZ5fzdROZnkeU7zknM8-BlJHGw83QkVo_vMWoAnsBbQYVL1FCbH0XioIGsDZGRY',
				serviceWorkerRegistration: swRegistration
			}).then(currentToken => {
				if (currentToken) {
					checkDeviceRegistration(deviceId, currentToken,).then(isRegistered => {
						if (isRegistered) {
							enableNotificationsBtn.style.display = 'none';
							registeredNotificationBtn.style.display = 'inline-block';
							webpushWindowPrompt.style.display = 'none';
						} else {
							enableNotificationsBtn.style.display = 'inline-block';
							registeredNotificationBtn.style.display = 'none';
							showWebpushPrompt(true);
						}
					});
				} else {
					console.warn('⚠️ No registration token available.');
					enableNotificationsBtn.style.display = 'inline-block';
					registeredNotificationBtn.style.display = 'none';
					showWebpushPrompt(true);
				}
			}).catch(err => {
				console.error('❌ Error checking token:', err);
				enableNotificationsBtn.style.display = 'inline-block';
				registeredNotificationBtn.style.display = 'none';
				showWebpushPrompt(true);
			});
		} else {
			enableNotificationsBtn.style.display = 'inline-block';
			registeredNotificationBtn.style.display = 'none';
			showWebpushPrompt(true);
		}
	}
	
	let lastRefresh = 0;

	// Handle token refresh
	function refreshToken() {
		if (!messaging || !swRegistration) return;

		if (Date.now() - lastRefresh < 60000) {
			// Don't refresh again if within 1 minute
			return;
		}
		lastRefresh = Date.now();

		if (Notification.permission === 'granted') {
			messaging.getToken({
				vapidKey: 'BPL_95JEVtLgctB-IP6IiJyZRZ5fzdROZnkeU7zknM8-BlJHGw83QkVo_vMWoAnsBbQYVL1FCbH0XioIGsDZGRY',
				serviceWorkerRegistration: swRegistration
			})
			.then(currentToken => {
				if (currentToken) {
					saveTokenToServer(currentToken); // Update last_used timestamp, etc.
				} else {
					console.warn('⚠️ No token to refresh');
					resetNotificationButton();
					enableNotificationsBtn.style.display = 'inline-block';
					registeredNotificationBtn.style.display = 'none';
					showWebpushPrompt(true);
				}
			})
			.catch(err => {
				console.error('❌ Error refreshing token:', err);
			});
		}
	}
	
	// Call refreshToken on page load
	window.addEventListener('load', function() {
		if (firebase.messaging.isSupported()) {
			setTimeout(refreshToken, 3000); // Delay to ensure everything is loaded
		}
	});

	function checkDeviceRegistration(deviceId, token) {
		return fetch('/notification/checkDeviceRegistration', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-Requested-With': 'XMLHttpRequest'
			},
			body: JSON.stringify({ deviceId, token })
		})
		.then(response => response.json())
		.then(data => data.isRegistered)
		.catch(error => {
			console.error('Error checking device registration:', error);
			return false;
		});
	}

	function registerOnMessageHandler() {
		if (messaging && typeof messaging.onMessage === 'function') {
			messaging.onMessage(payload => {
				// For data-only messages, all notification content is in the data payload
				const data = payload.data || {};
				
				// Mark as foreground message
				data.foreground = 'true';
				
				// Refresh notifications list if function exists
				if (typeof loadNotifications === 'function') loadNotifications();
				
				// Show toast notification using data from payload.data
				if (typeof showToast === 'function' && data.title && data.body) {
					showToast('info', `${data.title}: ${data.body} <a href="${data.link}" class="alert-link">[link]</a>`);
					loadNotifications();
				}
			});
		} else {
			console.warn('📭 messaging is not ready yet, retrying...');
			setTimeout(registerOnMessageHandler, 500); // retry after short delay
		}
	}	

	// Register Service Worker
	if ('serviceWorker' in navigator && firebase.messaging.isSupported()) {
		navigator.serviceWorker.register('/service-worker.js', { scope: '/' })
			.then(function (registration) {
				console.log('✅ FCM Service Worker registered with scope:', registration.scope);
				swRegistration = registration;

				navigator.serviceWorker.ready.then(function (readyRegistration) {
					console.log('✅ FCM Service Worker is active and ready.');
					messaging = firebase.messaging();
					registerOnMessageHandler();
					checkNotificationStatus();

					if (isStandaloneMode()) {
						console.log('📱 App running in standalone (PWA) mode');
						refreshToken();
					} else {
						console.log('🧭 App running in browser/tab mode');
					}
				});
			})
			.catch(function (err) {
				console.error('❌ FCM Service Worker registration failed:', err);
				showToast('danger', 'Failed to register service worker.');
				resetNotificationButton();
			});
	}

	function disallowWebpushPromptCookie() {
		// Set expiration date to 1 year from now
		const expiryDate = new Date();
		expiryDate.setFullYear(expiryDate.getFullYear() + 1);
		const expires = "expires=" + expiryDate.toUTCString();
	
		let cookieValue = "webprompt_disallowed=true; " +
						  expires + "; " +
						  "path=/; " +
						  "secure; " +
						  "samesite=Strict";
	
		// Set the cookie
		document.cookie = cookieValue;

		// Hide the webprompt
		webpushWindowPrompt.style.display = 'none';
	}

	function getCookie(name) {
		const value = `; ${document.cookie}`;
		const parts = value.split(`; ${name}=`);
		if (parts.length === 2) {
			return parts.pop().split(';').shift();
		}
		return null;
	}
	
	function showWebpushPrompt(enabled) {
		const cookieValue = getCookie('webprompt_disallowed');
	
		// Hide the prompt if:
		// - The function parameter `enabled` is false
		// - OR the cookie exists and its value is 'true'
		if (enabled === false || cookieValue === 'true') {
			webpushWindowPrompt.style.display = 'none';
		} else {
			webpushWindowPrompt.style.display = 'inline-block'
		}
	}

	function isStandaloneMode() {
		return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
	}


	if (enableNotificationsBtn || allowWebpushPrompt) {
		enableNotificationsBtn.addEventListener('click', requestNotificationPermission);
		allowWebpushPrompt.addEventListener('click', requestNotificationPermission);
	} else {
		console.error('Enable notifications button not found');
	}

	if (disallowWebpushPrompt) {
		disallowWebpushPrompt.addEventListener('click', disallowWebpushPromptCookie);
	}
});