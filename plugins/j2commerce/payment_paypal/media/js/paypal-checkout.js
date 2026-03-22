/**
 * @package     J2Commerce
 * @subpackage  Plugin.J2Commerce.PaymentPaypal
 *
 * @copyright   Copyright (C) 2024-2026 J2Commerce, LLC. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

'use strict';

(function () {
    let sdkLoaded = false;
    let sdkLoading = false;
    let buttonsRendered = false;
    let debug = false;
    let observer = null;

    const debugLog = (...args) => {
        if (debug) {
            console.log('[PayPal Debug]', ...args);
        }
    };

    // PayPal's cross-origin button iframe opens a popup via its own window.open().
    // On localhost/self-signed SSL the popup can't communicate back and stays blank.
    // We can't prevent it (cross-origin), so we detect and auto-close it from
    // inside the createOrder callback (which only fires on actual button click).
    const nativeOpen = window.open;

    const closeOrphanedPopup = () => {
        if (document.hasFocus()) return; // no popup stole focus
        try {
            const w = nativeOpen.call(window, '', '__zoid__paypal_checkout__');
            if (w && !w.closed) {
                try {
                    const href = w.location.href;
                    if (!href || href === 'about:blank') {
                        w.close();
                        window.focus();
                        console.log('[PayPal] Closed orphaned blank popup');
                    }
                } catch (e) {
                    // Cross-origin error = popup navigated to paypal.com = working, leave it
                }
            }
        } catch (e) { /* ignore */ }
    };

    const loadPayPalSDK = (sdkUrl) => {
        debugLog('Loading PayPal SDK from:', sdkUrl);
        return new Promise((resolve, reject) => {
            if (typeof paypal !== 'undefined') {
                sdkLoaded = true;
                debugLog('PayPal SDK already loaded');
                resolve();
                return;
            }

            if (sdkLoading) {
                debugLog('PayPal SDK loading in progress, waiting...');
                const checkInterval = setInterval(() => {
                    if (typeof paypal !== 'undefined') {
                        clearInterval(checkInterval);
                        debugLog('PayPal SDK loaded (while waiting)');
                        resolve();
                    }
                }, 100);
                return;
            }

            sdkLoading = true;

            const script = document.createElement('script');
            script.src = sdkUrl;

            script.onload = () => {
                sdkLoaded = true;
                sdkLoading = false;
                debugLog('PayPal SDK loaded successfully');
                resolve();
            };

            script.onerror = (e) => {
                sdkLoading = false;
                console.error('[PayPal] Failed to load SDK:', e);
                reject(new Error('Failed to load PayPal SDK'));
            };

            document.head.appendChild(script);
        });
    };

    const initializePayPalButtons = (container) => {
        if (!container || container.dataset.paypalInitialized === 'true' || buttonsRendered) {
            debugLog('Skipping init — already initialized');
            return;
        }

        debug = container.dataset.debug === 'true';
        debugLog('Initializing PayPal buttons');

        // Stop observing once we found the container
        if (observer) {
            observer.disconnect();
            observer = null;
        }

        const orderId = container.dataset.orderId;
        const createOrderUrl = container.dataset.createOrderUrl;
        const captureOrderUrl = container.dataset.captureOrderUrl;
        const csrfToken = container.dataset.csrfToken;
        const currency = container.dataset.currency || 'USD';
        const amount = container.dataset.amount;
        const sandbox = container.dataset.sandbox === 'true';
        const clientId = container.dataset.clientId;

        debugLog('Configuration:', { orderId, currency, amount, sandbox });

        const errorContainer = document.getElementById('paypal-error-message');
        const processingContainer = document.getElementById('paypal-processing-message');

        if (!clientId) {
            console.error('[PayPal] No client ID found in data attributes');
            return;
        }

        const showError = (message) => {
            if (errorContainer) {
                errorContainer.textContent = message;
                errorContainer.classList.remove('d-none');
            }
            if (processingContainer) {
                processingContainer.classList.add('d-none');
            }
        };

        const showProcessing = () => {
            if (processingContainer) {
                processingContainer.classList.remove('d-none');
            }
            if (errorContainer) {
                errorContainer.classList.add('d-none');
            }
        };

        const hideMessages = () => {
            if (errorContainer) {
                errorContainer.classList.add('d-none');
            }
            if (processingContainer) {
                processingContainer.classList.add('d-none');
            }
        };

        const baseUrl = sandbox ? 'https://www.sandbox.paypal.com/sdk/js' : 'https://www.paypal.com/sdk/js';
        const sdkUrl = `${baseUrl}?client-id=${encodeURIComponent(clientId)}&currency=${encodeURIComponent(currency)}&intent=capture&components=buttons`;

        container.dataset.paypalInitialized = 'true';
        buttonsRendered = true;

        loadPayPalSDK(sdkUrl)
            .then(() => {
                if (typeof paypal === 'undefined') {
                    throw new Error('PayPal SDK loaded but paypal object not available');
                }

                debugLog('Rendering PayPal buttons');

                return paypal.Buttons({
                    style: {
                        layout: 'vertical',
                        color: 'gold',
                        shape: 'rect',
                        label: 'paypal',
                        height: 45
                    },

                    createOrder: async () => {
                        try {
                            debugLog('createOrder: Starting order creation');
                            hideMessages();

                            // Auto-close orphaned blank popup (localhost/self-signed SSL issue).
                            // PayPal SDK opens a popup from its cross-origin iframe; on localhost
                            // it stays blank. We try closing it after a delay, then retry once.
                            setTimeout(() => {
                                closeOrphanedPopup();
                                setTimeout(closeOrphanedPopup, 2000);
                            }, 1500);

                            const requestBody = {
                                order_id: orderId,
                                currency: currency,
                                amount: amount,
                                [csrfToken]: '1'
                            };

                            const response = await fetch(createOrderUrl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(requestBody)
                            });

                            const data = await response.json();

                            debugLog('createOrder: Response:', { status: response.status, data });

                            if (!response.ok || !data.success) {
                                throw new Error(data.error || 'Failed to create PayPal order');
                            }

                            debugLog('createOrder: PayPal order ID:', data.paypal_order_id);
                            return data.paypal_order_id;
                        } catch (error) {
                            console.error('[PayPal] createOrder error:', error);
                            showError(error.message || 'Failed to initialize payment. Please try again.');
                            throw error;
                        }
                    },

                    onApprove: async (data) => {
                        try {
                            debugLog('onApprove: Capturing payment for order:', data.orderID);
                            showProcessing();

                            const requestBody = {
                                paypal_order_id: data.orderID,
                                order_id: orderId,
                                [csrfToken]: '1'
                            };

                            const response = await fetch(captureOrderUrl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(requestBody)
                            });

                            const result = await response.json();

                            debugLog('onApprove: Capture response:', { status: response.status, result });

                            if (!response.ok || !result.success) {
                                if (result.error && result.error.includes('INSTRUMENT_DECLINED')) {
                                    showError('Your payment method was declined. Please try a different payment method.');
                                    return data.restart();
                                }
                                throw new Error(result.error || 'Payment capture failed');
                            }

                            if (result.redirect) {
                                debugLog('onApprove: Redirecting to:', result.redirect);
                                window.location.href = result.redirect;
                            } else {
                                showError('Payment completed but redirect URL is missing.');
                            }
                        } catch (error) {
                            console.error('[PayPal] onApprove error:', error);
                            showError(error.message || 'Payment processing failed. Please contact support.');
                        }
                    },

                    onCancel: () => {
                        debugLog('onCancel: Payment cancelled by user');
                        showError('Payment was cancelled. You can try again or choose a different payment method.');
                    },

                    onError: (err) => {
                        console.error('[PayPal] Button error:', err);
                        const msg = err?.message || (typeof err === 'string' ? err : 'Unknown error');
                        showError('PayPal error: ' + msg);
                    }
                }).render('#paypal-button-container');
            })
            .catch((err) => {
                console.error('[PayPal] Initialization failed:', err);
                showError('Failed to load PayPal payment button. Please refresh the page.');
                container.dataset.paypalInitialized = 'false';
                buttonsRendered = false;
            });
    };

    const observeForPayPalContainer = () => {
        observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                for (const node of mutation.addedNodes) {
                    if (node.nodeType !== 1) continue;

                    if (node.id === 'paypal-button-container') {
                        initializePayPalButtons(node);
                        return;
                    }

                    if (node.querySelector) {
                        const container = node.querySelector('#paypal-button-container');
                        if (container) {
                            initializePayPalButtons(container);
                            return;
                        }
                    }
                }
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('paypal-button-container');
        if (container) {
            initializePayPalButtons(container);
        }

        observeForPayPalContainer();
    });
})();
