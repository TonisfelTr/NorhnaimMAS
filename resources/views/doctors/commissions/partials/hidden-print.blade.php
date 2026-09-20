@once
    <script>
        (() => {
            'use strict';

            if (window.__commissionHiddenPrintReady) {
                return;
            }

            window.__commissionHiddenPrintReady = true;

            let activeFrame = null;
            let cleanupTimer = null;
            let activeTrigger = null;

            const printRoutePattern = /^\/doctors\/commissions\/(?:military\/)?[^/]+\/print\/?$/;

            const getPrintUrl = (trigger) => {
                const rawUrl = trigger.dataset.printUrl || trigger.getAttribute('href') || '';

                if (!rawUrl) {
                    return null;
                }

                try {
                    const url = new URL(rawUrl, window.location.href);

                    return printRoutePattern.test(url.pathname) ? url.href : null;
                } catch (error) {
                    console.error('[commission-print] invalid print URL', error);
                    return null;
                }
            };

            const setTriggerBusy = (trigger, busy) => {
                if (!trigger) {
                    return;
                }

                if (busy) {
                    trigger.setAttribute('aria-busy', 'true');
                    trigger.setAttribute('aria-disabled', 'true');
                    return;
                }

                trigger.removeAttribute('aria-busy');
                trigger.removeAttribute('aria-disabled');
            };

            const cleanup = () => {
                window.clearTimeout(cleanupTimer);
                cleanupTimer = null;

                if (activeFrame) {
                    activeFrame.remove();
                    activeFrame = null;
                }

                setTriggerBusy(activeTrigger, false);
                activeTrigger = null;
            };

            const fail = (message, error = null) => {
                if (error) {
                    console.error('[commission-print]', error);
                }

                cleanup();
                window.alert(message);
            };

            const startPrint = (trigger, url) => {
                if (activeFrame) {
                    return;
                }

                activeTrigger = trigger;
                setTriggerBusy(trigger, true);

                const frame = document.createElement('iframe');
                activeFrame = frame;

                frame.title = 'Печатная версия документа';
                frame.tabIndex = -1;
                frame.setAttribute('aria-hidden', 'true');

                Object.assign(frame.style, {
                    position: 'fixed',
                    top: '0',
                    left: '-10000px',
                    width: '1px',
                    height: '1px',
                    border: '0',
                    opacity: '0',
                    pointerEvents: 'none',
                });

                frame.addEventListener('load', async () => {
                    window.clearTimeout(cleanupTimer);
                    cleanupTimer = null;

                    const printWindow = frame.contentWindow;
                    const printDocument = frame.contentDocument;

                    if (!printWindow || !printDocument) {
                        fail('Не удалось открыть форму печати. Повторите попытку.');
                        return;
                    }

                    try {
                        if (printDocument.fonts?.ready) {
                            await printDocument.fonts.ready;
                        }

                        printWindow.addEventListener('afterprint', cleanup, { once: true });

                        // Страховочная очистка на случай, если браузер не пришлёт afterprint.
                        // Большой интервал не мешает пользователю спокойно настроить печать.
                        cleanupTimer = window.setTimeout(cleanup, 300000);

                        window.requestAnimationFrame(() => {
                            window.requestAnimationFrame(() => {
                                try {
                                    printWindow.focus();
                                    printWindow.print();
                                } catch (error) {
                                    fail('Не удалось вызвать окно печати. Повторите попытку.', error);
                                }
                            });
                        });
                    } catch (error) {
                        fail('Не удалось подготовить документ к печати.', error);
                    }
                }, { once: true });

                frame.addEventListener('error', () => {
                    fail('Не удалось загрузить печатную форму. Повторите попытку.');
                }, { once: true });

                cleanupTimer = window.setTimeout(() => {
                    if (activeFrame) {
                        fail('Подготовка печати заняла слишком много времени. Повторите попытку.');
                    }
                }, 30000);

                frame.src = url;
                document.body.append(frame);
            };

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest(
                    '[data-commission-print], [data-military-print], [data-print-url], a[href*="/doctors/commissions/"][href*="/print"]'
                );

                if (!trigger) {
                    return;
                }

                const url = getPrintUrl(trigger);

                if (!url) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                startPrint(trigger, url);
            }, true);
        })();
    </script>
@endonce
