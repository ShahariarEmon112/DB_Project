/**
 * GameArena - Main JavaScript
 * KUET Gaming Tournament Management System
 */

// =====================================================
// AJAX Helper
// =====================================================
const GameArena = {
    /**
     * Make AJAX request
     */
    async request(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const config = { ...defaults, ...options };

        if (config.body && typeof config.body === 'object') {
            config.body = JSON.stringify(config.body);
        }

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('Request Error:', error);
            throw error;
        }
    },

    /**
     * GET request
     */
    async get(url) {
        return this.request(url);
    },

    /**
     * POST request
     */
    async post(url, data) {
        return this.request(url, {
            method: 'POST',
            body: data
        });
    },

    /**
     * PUT request
     */
    async put(url, data) {
        return this.request(url, {
            method: 'PUT',
            body: data
        });
    },

    /**
     * DELETE request
     */
    async delete(url) {
        return this.request(url, {
            method: 'DELETE'
        });
    },

    /**
     * Show success alert
     */
    showSuccess(message) {
        this.showAlert(message, 'success');
    },

    /**
     * Show error alert
     */
    showError(message) {
        this.showAlert(message, 'danger');
    },

    /**
     * Show alert
     */
    showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    },

    /**
     * Confirm dialog
     */
    confirm(message) {
        return new Promise((resolve) => {
            if (window.confirm(message)) {
                resolve(true);
            } else {
                resolve(false);
            }
        });
    },

    /**
     * Format date
     */
    formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateStr).toLocaleDateString('en-US', options);
    },

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-BD', {
            style: 'currency',
            currency: 'BDT',
            minimumFractionDigits: 0
        }).format(amount);
    }
};

// =====================================================
// Form Handler
// =====================================================
document.addEventListener('DOMContentLoaded', function() {

    // Form submission handler
    const forms = document.querySelectorAll('.ajax-form');
    forms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            submitBtn.disabled = true;

            try {
                const formData = new FormData(form);
                const response = await GameArena.post(form.action, Object.fromEntries(formData));

                if (response.success) {
                    GameArena.showSuccess(response.message);
                    if (response.redirect) {
                        setTimeout(() => window.location.href = response.redirect, 1000);
                    }
                } else {
                    GameArena.showError(response.errors ? response.errors.join(', ') : response.message);
                }
            } catch (error) {
                GameArena.showError('An error occurred. Please try again.');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    });

    // Delete buttons
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const url = this.href;
            const name = this.dataset.name || 'this item';

            if (await GameArena.confirm(`Are you sure you want to delete ${name}?`)) {
                try {
                    const response = await GameArena.delete(url);
                    if (response.success) {
                        GameArena.showSuccess(response.message);
                        setTimeout(() => location.reload(), 1000);
                    }
                } catch (error) {
                    GameArena.showError('Failed to delete.');
                }
            }
        });
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let timeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const query = this.value.toLowerCase();
                document.querySelectorAll('.searchable-item').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(query) ? '' : 'none';
                });
            }, 300);
        });
    }

    // Filter functionality
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            applyFilters();
        });
    });

    function applyFilters() {
        const filters = {};
        document.querySelectorAll('.filter-select').forEach(select => {
            filters[select.dataset.filter] = select.value;
        });

        document.querySelectorAll('.filterable-item').forEach(item => {
            let show = true;
            for (const [key, value] of Object.entries(filters)) {
                if (value && item.dataset[key] !== value) {
                    show = false;
                    break;
                }
            }
            item.style.display = show ? '' : 'none';
        });
    }

    // Sort functionality
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const [field, direction] = this.value.split('_');
            sortTable(field, direction);
        });
    }

    function sortTable(field, direction) {
        const tbody = document.querySelector('.sortable-table tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const aVal = a.querySelector(`[data-sort="${field}"]`)?.textContent || '';
            const bVal = b.querySelector(`[data-sort="${field}"]`)?.textContent || '';

            let comparison = 0;
            const aNum = parseFloat(aVal.replace(/[^0-9.-]/g, ''));
            const bNum = parseFloat(bVal.replace(/[^0-9.-]/g, ''));

            if (!isNaN(aNum) && !isNaN(bNum)) {
                comparison = aNum - bNum;
            } else {
                comparison = aVal.localeCompare(bVal);
            }

            return direction === 'desc' ? -comparison : comparison;
        });

        rows.forEach(row => tbody.appendChild(row));
    }

    // Auto-hide alerts
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            alert.remove();
        }, 5000);
    });
});
