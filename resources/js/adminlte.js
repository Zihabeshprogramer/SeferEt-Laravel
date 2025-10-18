/**
 * AdminLTE v4.0.0 Integration for SeferEt
 * 
 * This file initializes AdminLTE and all required dependencies
 * for the B2B Partner Portal and Admin Dashboard interfaces.
 */
// Import jQuery (required by AdminLTE)
import $ from 'jquery';
window.$ = window.jQuery = $;
// Import Bootstrap
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
// Import AdminLTE
import 'admin-lte/dist/js/adminlte.min.js';
// Import additional plugins
import 'overlayScrollbars/browser/overlayscrollbars.browser.es6.min.js';
import 'select2/dist/js/select2.min.js';
import 'chart.js/auto';
import moment from 'moment';
// Make moment available globally
window.moment = moment;
// SeferEt Custom JavaScript
class SeferEtAdmin {
    constructor() {
        this.init();
    }
    init() {
        this.initializePlugins();
        this.setupEventListeners();
        this.initializeCharts();
        this.setupTooltips();
    }
    /**
     * Initialize AdminLTE plugins
     */
    initializePlugins() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
        // Initialize Overlay Scrollbars
        if (typeof OverlayScrollbars !== 'undefined') {
            OverlayScrollbars(document.querySelectorAll('.sidebar'), {
                className: 'os-theme-light',
                sizeAutoCapable: true,
                scrollbars: {
                    autoHide: 'leave'
                }
            });
        }
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        // Initialize popovers
        $('[data-bs-toggle="popover"]').popover();
    }
    /**
     * Setup global event listeners
     */
    setupEventListeners() {
        // Sidebar toggle
        $(document).on('click', '[data-widget="pushmenu"]', function(e) {
            e.preventDefault();
            $('body').toggleClass('sidebar-collapse');
        });
        // Card refresh functionality
        $(document).on('click', '[data-card-widget="refresh"]', function(e) {
            e.preventDefault();
            const card = $(this).closest('.card');
            card.addClass('card-refresh');
            // Simulate refresh delay
            setTimeout(() => {
                card.removeClass('card-refresh');
            }, 2000);
        });
        // Card collapse functionality
        $(document).on('click', '[data-card-widget="collapse"]', function(e) {
            e.preventDefault();
            const card = $(this).closest('.card');
            const cardBody = card.find('.card-body, .card-footer');
            if (cardBody.is(':visible')) {
                cardBody.slideUp();
                $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
            } else {
                cardBody.slideDown();
                $(this).find('i').removeClass('fa-plus').addClass('fa-minus');
            }
        });
        // Form validation feedback
        $('form').on('submit', function() {
            $(this).find('.btn[type="submit"]').prop('disabled', true).html(
                '<i class="fas fa-spinner fa-spin"></i> Processing...'
            );
        });
    }
    /**
     * Initialize Chart.js charts
     */
    initializeCharts() {
        // Dashboard revenue chart
        const revenueChartCanvas = document.getElementById('revenueChart');
        if (revenueChartCanvas) {
            const revenueChart = new Chart(revenueChartCanvas, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [12, 19, 3, 5, 2, 3],
                        backgroundColor: 'rgba(46, 139, 87, 0.1)',
                        borderColor: '#2E8B57',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        // Dashboard bookings chart
        const bookingsChartCanvas = document.getElementById('bookingsChart');
        if (bookingsChartCanvas) {
            const bookingsChart = new Chart(bookingsChartCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Confirmed', 'Pending', 'Cancelled'],
                    datasets: [{
                        data: [55, 30, 15],
                        backgroundColor: ['#2E8B57', '#FFD700', '#FF6B6B']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
    /**
     * Setup Bootstrap tooltips
     */
    setupTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    /**
     * Show success notification
     */
    showSuccessNotification(message) {
        this.showNotification(message, 'success');
    }
    /**
     * Show error notification
     */
    showErrorNotification(message) {
        this.showNotification(message, 'error');
    }
    /**
     * Show notification toast
     */
    showNotification(message, type = 'info') {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        // Create toast container if it doesn't exist
        if (!document.getElementById('toast-container')) {
            $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }
        const $toast = $(toastHtml);
        $('#toast-container').append($toast);
        const toast = new bootstrap.Toast($toast[0]);
        toast.show();
        // Remove toast element after it's hidden
        $toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    /**
     * Format currency for display
     */
    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }
    /**
     * Format date for display
     */
    formatDate(date, format = 'MMM DD, YYYY') {
        return moment(date).format(format);
    }
}
// Initialize SeferEt Admin when DOM is ready
$(document).ready(function() {
    window.SeferEtAdmin = new SeferEtAdmin();
    // Add fade-in animation to content
    $('.content-wrapper').addClass('fade-in');
});
// Export for module usage
export default SeferEtAdmin;
