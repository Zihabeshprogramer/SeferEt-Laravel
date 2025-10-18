/**
 * Itinerary Builder - JavaScript
 * Handles drag-and-drop itinerary management, activity creation, and multi-day planning
 */
class ItineraryBuilder {
    constructor(options = {}) {
        this.options = {
            containerId: 'itineraryContainer',
            apiRoutes: {
                storeActivity: '',
                updateActivity: '',
                deleteActivity: '',
                reorderActivities: ''
            },
            maxDays: 30,
            ...options
        };
        this.activities = [];
        this.currentDay = 1;
        this.isDirty = false;
    }
    initialize() {
        this.setupContainer();
        this.setupDayTabs();
        this.setupActivityModal();
        this.setupSortable();
        this.loadExistingActivities();
    }
    setupContainer() {
        const container = document.getElementById(this.options.containerId);
        if (!container) {
            console.error('Itinerary container not found');
            return;
        }
        this.container = container;
    }
    setupDayTabs() {
        const tabsContainer = document.querySelector('.itinerary-days-tabs');
        const contentContainer = document.querySelector('.itinerary-days-content');
        if (!tabsContainer || !contentContainer) return;
        // Add day button
        const addDayBtn = document.getElementById('addDayBtn');
        if (addDayBtn) {
            addDayBtn.addEventListener('click', () => this.addDay());
        }
        // Day tab switching
        this.bindDayTabEvents();
    }
    bindDayTabEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.day-tab')) {
                const dayNumber = parseInt(e.target.dataset.day);
                this.switchToDay(dayNumber);
            }
            if (e.target.matches('.remove-day-btn')) {
                const dayNumber = parseInt(e.target.dataset.day);
                this.removeDay(dayNumber);
            }
        });
    }
    setupActivityModal() {
        const modal = document.getElementById('activityModal');
        if (!modal) return;
        const addActivityBtns = document.querySelectorAll('.add-activity-btn');
        addActivityBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const dayNumber = parseInt(e.target.dataset.day);
                this.openActivityModal(dayNumber);
            });
        });
        const form = document.getElementById('activityForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveActivity();
            });
        }
    }
    setupSortable() {
        // Initialize sortable for existing day containers
        this.initializeSortableForDay(this.currentDay);
    }
    initializeSortableForDay(dayNumber) {
        const dayContainer = document.getElementById(`day-${dayNumber}-activities`);
        if (!dayContainer || typeof Sortable === 'undefined') return;
        new Sortable(dayContainer, {
            group: 'activities',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: (evt) => {
                this.handleActivityReorder(dayNumber, evt);
            }
        });
    }
    addDay() {
        const duration = document.getElementById('duration')?.value || 1;
        const currentDays = document.querySelectorAll('.day-tab').length;
        if (currentDays >= parseInt(duration)) {
            alert(`Maximum ${duration} days allowed for this package`);
            return;
        }
        const newDayNumber = currentDays + 1;
        this.createDayTab(newDayNumber);
        this.createDayContent(newDayNumber);
        this.switchToDay(newDayNumber);
        this.initializeSortableForDay(newDayNumber);
    }
    removeDay(dayNumber) {
        if (dayNumber === 1) {
            alert('Cannot remove the first day');
            return;
        }
        if (confirm('Are you sure you want to remove this day and all its activities?')) {
            // Remove activities for this day
            this.activities = this.activities.filter(activity => activity.day_number !== dayNumber);
            // Remove DOM elements
            const tab = document.querySelector(`[data-day="${dayNumber}"]`);
            const content = document.getElementById(`day-${dayNumber}`);
            if (tab) tab.remove();
            if (content) content.remove();
            // Renumber remaining days
            this.renumberDays();
            // Switch to day 1
            this.switchToDay(1);
            this.markDirty();
        }
    }
    renumberDays() {
        const tabs = document.querySelectorAll('.day-tab');
        const contents = document.querySelectorAll('.day-content');
        tabs.forEach((tab, index) => {
            const newDayNumber = index + 1;
            const oldDayNumber = parseInt(tab.dataset.day);
            tab.dataset.day = newDayNumber;
            tab.textContent = `Day ${newDayNumber}`;
            // Update activities data
            this.activities.forEach(activity => {
                if (activity.day_number === oldDayNumber) {
                    activity.day_number = newDayNumber;
                }
            });
        });
        contents.forEach((content, index) => {
            const newDayNumber = index + 1;
            content.id = `day-${newDayNumber}`;
            const activitiesContainer = content.querySelector('.day-activities');
            if (activitiesContainer) {
                activitiesContainer.id = `day-${newDayNumber}-activities`;
            }
        });
    }
    createDayTab(dayNumber) {
        const tabsContainer = document.querySelector('.itinerary-days-tabs');
        const addDayBtn = document.getElementById('addDayBtn');
        const tab = document.createElement('button');
        tab.className = 'day-tab';
        tab.dataset.day = dayNumber;
        tab.innerHTML = `
            Day ${dayNumber}
            ${dayNumber > 1 ? `<span class="remove-day-btn" data-day="${dayNumber}">&times;</span>` : ''}
        `;
        tabsContainer.insertBefore(tab, addDayBtn);
    }
    createDayContent(dayNumber) {
        const contentContainer = document.querySelector('.itinerary-days-content');
        const dayContent = document.createElement('div');
        dayContent.id = `day-${dayNumber}`;
        dayContent.className = 'day-content';
        dayContent.style.display = 'none';
        dayContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Day ${dayNumber} Activities</h5>
                <button type="button" class="btn btn-primary btn-sm add-activity-btn" data-day="${dayNumber}">
                    <i class="fas fa-plus me-1"></i> Add Activity
                </button>
            </div>
            <div class="day-activities" id="day-${dayNumber}-activities">
                <div class="empty-day-message text-muted text-center py-4">
                    <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                    <p>No activities planned for this day yet.</p>
                    <p class="small">Click "Add Activity" to get started.</p>
                </div>
            </div>
        `;
        contentContainer.appendChild(dayContent);
        // Re-bind event listeners for new add activity button
        const addBtn = dayContent.querySelector('.add-activity-btn');
        if (addBtn) {
            addBtn.addEventListener('click', (e) => {
                const dayNum = parseInt(e.target.dataset.day);
                this.openActivityModal(dayNum);
            });
        }
    }
    switchToDay(dayNumber) {
        // Update tab active states
        document.querySelectorAll('.day-tab').forEach(tab => {
            tab.classList.toggle('active', parseInt(tab.dataset.day) === dayNumber);
        });
        // Update content visibility
        document.querySelectorAll('.day-content').forEach(content => {
            const dayNum = parseInt(content.id.split('-')[1]);
            content.style.display = dayNum === dayNumber ? 'block' : 'none';
        });
        this.currentDay = dayNumber;
    }
    openActivityModal(dayNumber = null) {
        const modal = document.getElementById('activityModal');
        if (!modal) return;
        // Reset form
        const form = document.getElementById('activityForm');
        if (form) form.reset();
        // Set day number
        const dayInput = document.getElementById('activityDay');
        if (dayInput) dayInput.value = dayNumber || this.currentDay;
        // Show modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
    async saveActivity() {
        const form = document.getElementById('activityForm');
        const formData = new FormData(form);
        const activityData = {
            day_number: formData.get('day_number'),
            title: formData.get('title'),
            description: formData.get('description'),
            start_time: formData.get('start_time'),
            duration_minutes: formData.get('duration_minutes'),
            location: formData.get('location'),
            is_included: formData.get('is_included') === '1',
            additional_cost: formData.get('additional_cost') || 0,
            special_requirements: formData.get('special_requirements'),
            is_highlighted: formData.get('is_highlighted') === '1'
        };
        try {
            // Add to local activities array
            const newActivity = {
                id: Date.now(), // Temporary ID
                ...activityData,
                display_order: this.getNextDisplayOrder(activityData.day_number)
            };
            this.activities.push(newActivity);
            this.renderActivity(newActivity);
            this.markDirty();
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('activityModal'));
            modal.hide();
            this.showSuccess('Activity added successfully');
        } catch (error) {
            console.error('Activity save error:', error);
            this.showError('Failed to save activity');
        }
    }
    renderActivity(activity) {
        const dayContainer = document.getElementById(`day-${activity.day_number}-activities`);
        if (!dayContainer) return;
        // Remove empty message if it exists
        const emptyMessage = dayContainer.querySelector('.empty-day-message');
        if (emptyMessage) emptyMessage.remove();
        const activityElement = document.createElement('div');
        activityElement.className = 'activity-item';
        activityElement.dataset.activityId = activity.id;
        activityElement.innerHTML = this.getActivityHTML(activity);
        dayContainer.appendChild(activityElement);
        // Bind events for the new activity
        this.bindActivityEvents(activityElement);
    }
    getActivityHTML(activity) {
        return `
            <div class="activity-card">
                <div class="activity-header">
                    <div class="activity-time">
                        <i class="fas fa-clock"></i>
                        ${activity.start_time || 'TBD'} 
                        ${activity.duration_minutes ? `(${activity.duration_minutes} min)` : ''}
                    </div>
                    <div class="activity-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary edit-activity-btn" 
                                data-activity-id="${activity.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-activity-btn"
                                data-activity-id="${activity.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                        <div class="activity-drag-handle">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                    </div>
                </div>
                <div class="activity-content">
                    <h6 class="activity-title">
                        ${activity.title}
                        ${activity.is_highlighted ? '<span class="badge badge-warning">Highlight</span>' : ''}
                    </h6>
                    <p class="activity-description">${activity.description || ''}</p>
                    ${activity.location ? `<p class="activity-location"><i class="fas fa-map-marker-alt"></i> ${activity.location}</p>` : ''}
                    ${!activity.is_included ? `<p class="activity-cost"><i class="fas fa-dollar-sign"></i> ${activity.additional_cost}</p>` : ''}
                </div>
            </div>
        `;
    }
    bindActivityEvents(activityElement) {
        const editBtn = activityElement.querySelector('.edit-activity-btn');
        const deleteBtn = activityElement.querySelector('.delete-activity-btn');
        if (editBtn) {
            editBtn.addEventListener('click', (e) => {
                const activityId = e.target.closest('[data-activity-id]').dataset.activityId;
                this.editActivity(activityId);
            });
        }
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                const activityId = e.target.closest('[data-activity-id]').dataset.activityId;
                this.deleteActivity(activityId);
            });
        }
    }
    editActivity(activityId) {
        const activity = this.activities.find(a => a.id == activityId);
        if (!activity) return;
        // Populate modal with activity data
        const modal = document.getElementById('activityModal');
        const form = document.getElementById('activityForm');
        Object.keys(activity).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = !!activity[key];
                } else {
                    field.value = activity[key];
                }
            }
        });
        // Add edit mode indicator
        form.dataset.editingId = activityId;
        // Show modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
    deleteActivity(activityId) {
        if (confirm('Are you sure you want to delete this activity?')) {
            // Remove from activities array
            this.activities = this.activities.filter(a => a.id != activityId);
            // Remove from DOM
            const activityElement = document.querySelector(`[data-activity-id="${activityId}"]`);
            if (activityElement) activityElement.remove();
            // Show empty message if no activities left in day
            const dayContainer = activityElement?.closest('.day-activities');
            if (dayContainer && dayContainer.children.length === 0) {
                dayContainer.innerHTML = `
                    <div class="empty-day-message text-muted text-center py-4">
                        <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                        <p>No activities planned for this day yet.</p>
                        <p class="small">Click "Add Activity" to get started.</p>
                    </div>
                `;
            }
            this.markDirty();
            this.showSuccess('Activity deleted successfully');
        }
    }
    handleActivityReorder(dayNumber, evt) {
        const activityId = evt.item.dataset.activityId;
        const newIndex = evt.newIndex;
        // Update display_order for activities in this day
        const dayActivities = this.activities.filter(a => a.day_number == dayNumber);
        dayActivities.forEach((activity, index) => {
            activity.display_order = index + 1;
        });
        this.markDirty();
    }
    getNextDisplayOrder(dayNumber) {
        const dayActivities = this.activities.filter(a => a.day_number == dayNumber);
        return dayActivities.length + 1;
    }
    loadExistingActivities() {
        // This would be called when editing an existing package
        // Implementation depends on how activities are passed from server
    }
    getActivitiesData() {
        return this.activities;
    }
    markDirty() {
        this.isDirty = true;
        if (window.packageWizard) {
            window.packageWizard.markDirty();
        }
    }
    showSuccess(message) {
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
        }
    }
    showError(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            console.error('Error:', message);
        }
    }
}
// Auto-initialize if container exists
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('itineraryContainer')) {
        window.itineraryBuilder = new ItineraryBuilder();
    }
});
