<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1;
        }

        :root {
            --msc-primary: #018790;
            --msc-primary-rgb: 1, 135, 144;
            --msc-secondary: #005461;
            --msc-secondary-rgb: 0, 84, 97;
            --msc-accent-rgb: 0, 183, 181;

            --bs-primary: var(--msc-primary);
            --bs-primary-rgb: var(--msc-primary-rgb);
            --bs-secondary: var(--msc-secondary);
            --bs-secondary-rgb: var(--msc-secondary-rgb);
        }

        .btn-primary {
            --bs-btn-color: #fff;
            --bs-btn-bg: var(--msc-primary);
            --bs-btn-border-color: var(--msc-primary);
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #007b86;
            --bs-btn-hover-border-color: #007b86;
            --bs-btn-focus-shadow-rgb: var(--msc-accent-rgb);
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: #006d76;
            --bs-btn-active-border-color: #006d76;
        }

        .btn-outline-primary {
            --bs-btn-color: var(--msc-primary);
            --bs-btn-border-color: var(--msc-primary);
            --bs-btn-hover-bg: var(--msc-primary);
            --bs-btn-hover-border-color: var(--msc-primary);
            --bs-btn-focus-shadow-rgb: var(--msc-accent-rgb);
            --bs-btn-active-bg: #006d76;
            --bs-btn-active-border-color: #006d76;
        }

        .navbar .nav-link:hover,
        .navbar .nav-link.active {
            color: var(--msc-primary) !important;
        }

        .filter-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .filter-actions .btn {
            min-height: 38px;
        }

        .filter-actions .btn.btn-sm {
            min-height: 31px;
        }
    </style>
</head>
<body>
    @include('layouts.header')

    <main class="container-fluid py-3">
        @include('partials.alerts')
        @yield('content')
    </main>

    @include('partials.confirm-modal')

    @include('layouts.footer') 
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const modalElement = document.getElementById('confirmModal');
            if (!modalElement || typeof bootstrap === 'undefined') {
                return;
            }

            const modal = new bootstrap.Modal(modalElement);
            const messageEl = document.getElementById('confirmModalMessage');
            const titleEl = document.getElementById('confirmModalLabel');
            const confirmBtn = document.getElementById('confirmModalConfirmBtn');

            let pendingForm = null;
            let pendingLink = null;

            function setModalContent(options) {
                const title = options.title || 'Bitte bestätigen';
                const message = options.message || 'Möchtest du fortfahren?';
                const confirmText = options.confirmText || 'OK';
                const confirmVariant = options.confirmVariant || 'danger';

                if (titleEl) {
                    titleEl.innerHTML = '<i class="fa fa-exclamation-triangle me-2" aria-hidden="true"></i>' + title;
                }
                if (messageEl) {
                    messageEl.textContent = message;
                }
                if (confirmBtn) {
                    confirmBtn.textContent = confirmText;
                    confirmBtn.className = 'btn btn-' + confirmVariant;
                }
            }

            // Intercept form submits that opt-in via data-confirm
            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;

                const message = form.getAttribute('data-confirm');
                if (!message) return;

                if (form.dataset.confirmed === '1') {
                    form.dataset.confirmed = '0';
                    return;
                }

                event.preventDefault();
                pendingForm = form;
                pendingLink = null;

                setModalContent({
                    title: form.getAttribute('data-confirm-title') || 'Bitte bestätigen',
                    message,
                    confirmText: form.getAttribute('data-confirm-button') || 'OK',
                    confirmVariant: form.getAttribute('data-confirm-variant') || 'danger',
                });
                modal.show();
            }, true);

            // Intercept link clicks that opt-in via data-confirm
            document.addEventListener('click', function (event) {
                const link = event.target?.closest ? event.target.closest('a[data-confirm]') : null;
                if (!link) return;

                event.preventDefault();
                pendingLink = link;
                pendingForm = null;

                setModalContent({
                    title: link.getAttribute('data-confirm-title') || 'Bitte bestätigen',
                    message: link.getAttribute('data-confirm') || 'Möchtest du fortfahren?',
                    confirmText: link.getAttribute('data-confirm-button') || 'OK',
                    confirmVariant: link.getAttribute('data-confirm-variant') || 'danger',
                });
                modal.show();
            }, true);

            if (confirmBtn) {
                confirmBtn.addEventListener('click', function () {
                    modal.hide();

                    if (pendingForm) {
                        pendingForm.dataset.confirmed = '1';
                        pendingForm.requestSubmit ? pendingForm.requestSubmit() : pendingForm.submit();
                        pendingForm = null;
                        return;
                    }

                    if (pendingLink) {
                        window.location.href = pendingLink.href;
                        pendingLink = null;
                    }
                });
            }
        })();
    </script>
</body>
</html>
