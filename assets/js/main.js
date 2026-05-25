document.addEventListener('DOMContentLoaded', function () {
    // Page loader - hide after load
    const loader = document.getElementById('page-loader');
    if (loader) {
        window.addEventListener('load', function () {
            setTimeout(function () {
                loader.classList.add('hidden');
            }, 400);
        });
        setTimeout(function () {
            loader.classList.add('hidden');
        }, 3000);
    }

    // Password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const input = document.querySelector(this.getAttribute('data-target'));
            if (!input) return;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        });
    });

    // Initialize and manage Bootstrap dropdowns
    if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(toggle) {
            // Create Dropdown instance for each toggle
            try {
                bootstrap.Dropdown.getOrCreateInstance(toggle);
            } catch (e) {
                console.error('Dropdown init error:', e);
            }
        });
    }

    // Close dropdown when clicking navigation links
    document.querySelectorAll('.dropdown-item[href]').forEach(function(link) {
        link.addEventListener('click', function() {
            const dropdown = this.closest('.dropdown-menu');

            if (dropdown) {
                const toggle = dropdown.previousElementSibling;

                if (toggle && typeof bootstrap !== 'undefined') {
                    const inst = bootstrap.Dropdown.getInstance(toggle);

                    if (inst) {
                        inst.hide();
                    }
                }
            }
        });
    });

    // Toast notification display from URL params
    showToastFromUrl();
});

function showToast(message, type) {
    type = type || 'success';
    const existing = document.querySelector('.toast-popup');
    if (existing) existing.remove();

    const bg = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-primary';
    const toast = document.createElement('div');
    toast.className = 'toast-popup alert ' + bg + ' text-white shadow-lg border-0';
    const wrap = document.createElement('div');
    wrap.className = 'd-flex align-items-center gap-2';
    wrap.innerHTML = '<i class="bi bi-check-circle-fill"></i><span></span>';
    wrap.querySelector('span').textContent = message;
    toast.appendChild(wrap);
    document.body.appendChild(toast);
    setTimeout(function () {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(function () { toast.remove(); }, 300);
    }, 3500);
}

function showToastFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const msg = params.get('toast');
    const type = params.get('toast_type') || 'success';
    if (msg) {
        showToast(decodeURIComponent(msg), type);
        const url = new URL(window.location);
        url.searchParams.delete('toast');
        url.searchParams.delete('toast_type');
        window.history.replaceState({}, '', url);
    }
}

