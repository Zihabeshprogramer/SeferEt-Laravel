// ===========================================
// SeferEt Customer Frontend JavaScript
// Bootstrap 5 functionality and interactions
// ===========================================
// Import Bootstrap CSS (compiled with our custom styles)
import '../sass/customer.scss';
// Import Bootstrap JavaScript
import { Tooltip, Toast, Dropdown, Collapse, Modal, Offcanvas } from 'bootstrap';
// Import Font Awesome icons
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { 
    faHome, 
    faUser, 
    faSearch, 
    faPlane, 
    faHeart,
    faStar,
    faMapMarkerAlt,
    faCalendarAlt,
    faPhone,
    faEnvelope,
    faBars,
    faTimes,
    faChevronRight,
    faCheck,
    faExclamationTriangle,
    faInfoCircle,
    faEdit,
    faTrash,
    faShare,
    faBookmark,
    faFilter,
    faSort,
    faMosque,
    faGlobe,
    faShieldAlt,
    faHandshake,
    faBuilding,
    faSignInAlt,
    faUserPlus,
    faSignOutAlt,
    faTachometerAlt,
    faCalendarCheck,
    faUserEdit,
    faBriefcase,
    faBox,
    faPlaneArrival,
    faPlaneDeparture,
    faCheckCircle,
    faDollarSign,
    faShoppingCart,
    faEye,
    faHeadset,
    faUserCircle,
    faHistory,
    faBolt,
    faRefresh,
    faBoxOpen,
    faMountain,
    faImage,
    faLocationOn,
    faFavorite,
    faAirplaneTicket,
    faExplore,
    faTravelExplore,
    faSwapHoriz,
    faFlightTakeoff,
    faFlightLand,
    faSwapVert,
    faFilterList,
    faHotel,
    faRestaurant,
    faWifi,
    faParking,
    faPool,
    faFitnessCenter,
    faSpa,
    faLandscape,
    faNotifications,
    faMoreVert
} from '@fortawesome/free-solid-icons';
// Add icons to library
library.add(
    faHome, faUser, faSearch, faPlane, faHeart, faStar, faMapMarkerAlt, 
    faCalendarAlt, faPhone, faEnvelope, faBars, faTimes, faChevronRight, 
    faCheck, faExclamationTriangle, faInfoCircle, faEdit, faTrash, faShare,
    faBookmark, faFilter, faSort, faMosque, faGlobe, faShieldAlt, faHandshake,
    faBuilding, faSignInAlt, faUserPlus, faSignOutAlt, faTachometerAlt,
    faCalendarCheck, faUserEdit, faBriefcase, faBox, faPlaneArrival,
    faPlaneDeparture, faCheckCircle, faDollarSign, faShoppingCart, faEye,
    faHeadset, faUserCircle, faHistory, faBolt, faRefresh, faBoxOpen,
    faMountain, faImage, faLocationOn, faFavorite, faAirplaneTicket,
    faExplore, faTravelExplore, faSwapHoriz, faFlightTakeoff, faFlightLand,
    faSwapVert, faFilterList, faHotel, faRestaurant, faWifi, faParking,
    faPool, faFitnessCenter, faSpa, faLandscape, faNotifications, faMoreVert
);
// Convert any existing <i> tags to SVGs
dom.watch();
// ===========================================
// DOM Ready Functions
// ===========================================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl));
    // Initialize all Bootstrap toasts
    const toastElList = document.querySelectorAll('.toast');
    const toastList = [...toastElList].map(toastEl => new Toast(toastEl));
    // Initialize dropdowns
    const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
    const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new Dropdown(dropdownToggleEl));
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    // Add smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    // Add loading states to buttons
    const submitButtons = document.querySelectorAll('button[type="submit"], .btn-submit');
    submitButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Add loading state
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            // Remove loading state after 3 seconds (fallback)
            setTimeout(() => {
                this.disabled = false;
                this.innerHTML = this.getAttribute('data-original-text') || 'Submit';
            }, 3000);
        });
    });
    // Initialize custom search functionality
    initializeSearch();
    // Initialize favorites functionality
    initializeFavorites();
    // Initialize destination cards
    initializeDestinationCards();
    // Initialize form enhancements
    initializeFormEnhancements();
});
// ===========================================
// Search Functionality
// ===========================================
function initializeSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    const searchResults = document.querySelectorAll('.search-results');
    searchInputs.forEach(function(input) {
        let searchTimeout;
        input.addEventListener('input', function() {
            const query = this.value.trim();
            // Clear previous timeout
            clearTimeout(searchTimeout);
            // Debounce search
            searchTimeout = setTimeout(() => {
                if (query.length >= 2) {
                    performSearch(query, this);
                }
            }, 300);
        });
    });
}
function performSearch(query, inputElement) {
    // This would typically make an AJAX call to your Laravel backend
    // Show loading state
    inputElement.classList.add('searching');
    // Simulate API call
    setTimeout(() => {
        inputElement.classList.remove('searching');
        // Handle results here
    }, 1000);
}
// ===========================================
// Favorites Functionality
// ===========================================
function initializeFavorites() {
    const favoriteButtons = document.querySelectorAll('.favorite-btn, .btn-favorite');
    favoriteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const isFavorited = this.classList.contains('favorited');
            const itemId = this.getAttribute('data-item-id');
            const itemType = this.getAttribute('data-item-type') || 'destination';
            // Toggle favorite state
            toggleFavorite(itemId, itemType, !isFavorited, this);
        });
    });
}
function toggleFavorite(itemId, itemType, isFavorite, buttonElement) {
    // Update UI immediately for better UX
    if (isFavorite) {
        buttonElement.classList.add('favorited');
        buttonElement.innerHTML = '<i class="fas fa-heart"></i>';
    } else {
        buttonElement.classList.remove('favorited');
        buttonElement.innerHTML = '<i class="far fa-heart"></i>';
    }
    // Make AJAX call to Laravel backend
    fetch('/api/favorites', {
        method: isFavorite ? 'POST' : 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            item_id: itemId,
            item_type: itemType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Revert UI changes if API call failed
            if (isFavorite) {
                buttonElement.classList.remove('favorited');
                buttonElement.innerHTML = '<i class="far fa-heart"></i>';
            } else {
                buttonElement.classList.add('favorited');
                buttonElement.innerHTML = '<i class="fas fa-heart"></i>';
            }
            showToast('Error', 'Failed to update favorites', 'error');
        } else {
            showToast('Success', isFavorite ? 'Added to favorites' : 'Removed from favorites', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Something went wrong', 'error');
    });
}
// ===========================================
// Destination Cards
// ===========================================
function initializeDestinationCards() {
    const destinationCards = document.querySelectorAll('.destination-card');
    destinationCards.forEach(function(card) {
        // Add hover effects
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
        // Add click handling for card navigation
        const cardLink = card.getAttribute('data-href');
        if (cardLink) {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function(e) {
                // Don't navigate if clicking on buttons
                if (!e.target.closest('.btn, .favorite-btn')) {
                    window.location.href = cardLink;
                }
            });
        }
    });
}
// ===========================================
// Form Enhancements
// ===========================================
function initializeFormEnhancements() {
    // Add floating label effect
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(function(input) {
        // Add focus/blur effects
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
        // Initialize state for fields with values
        if (input.value) {
            input.parentElement.classList.add('focused');
        }
    });
    // Add password visibility toggle
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            // Toggle icon
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });
}
// ===========================================
// Utility Functions
// ===========================================
// Show toast notification
function showToast(title, message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    const toastElement = document.createElement('div');
    toastElement.className = `toast align-items-center text-bg-${type} border-0`;
    toastElement.setAttribute('role', 'alert');
    toastElement.setAttribute('aria-live', 'assertive');
    toastElement.setAttribute('aria-atomic', 'true');
    toastElement.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong>${title}</strong><br>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toastElement);
    const toast = new Toast(toastElement);
    toast.show();
    // Remove element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}
// Create toast container if it doesn't exist
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1100';
    document.body.appendChild(container);
    return container;
}
// Format currency
function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}
// Format date
function formatDate(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    };
    return new Intl.DateTimeFormat('en-US', {...defaultOptions, ...options}).format(new Date(date));
}
// Debounce function
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}
// Export functions for global use
window.SeferEt = {
    showToast,
    formatCurrency,
    formatDate,
    debounce,
    toggleFavorite
};
