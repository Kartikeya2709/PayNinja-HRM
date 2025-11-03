class GuidedTour {
    constructor() {
        this.steps = [
            {
                target: '.menu-item a[href*="home"]',
                title: 'Dashboard',
                content: 'Your central hub for HR activities and insights.',
                position: 'right'
            },
            {
                target: '.menu-item a[href*="attendance"]',
                title: 'Attendance',
                content: 'Track your daily attendance and view reports.',
                position: 'right'
            },
            {
                target: '.menu-item a[href*="leave"]',
                title: 'Leave Management',
                content: 'Request and manage your leave applications.',
                position: 'right'
            },
            {
                target: '.menu-item a[href*="payroll"]',
                title: 'Payroll',
                content: 'Access your salary details and payslips.',
                position: 'right'
            }
        ];
        
        this.currentStep = 0;
        this.init();
    }

    init() {
        // Create tour container
        if (!document.querySelector('.guided-tour-container')) {
            const container = document.createElement('div');
            container.className = 'guided-tour-container';
            container.innerHTML = `
                <div class="guided-tour-overlay"></div>
                <div class="guided-tour-modal">
                    <div class="guided-tour-content"></div>
                </div>
            `;
            document.body.appendChild(container);
        }

        this.container = document.querySelector('.guided-tour-container');
        this.modal = document.querySelector('.guided-tour-modal');
        this.content = document.querySelector('.guided-tour-content');

        // Bind methods
        this.next = this.next.bind(this);
        this.prev = this.prev.bind(this);
        this.close = this.close.bind(this);

        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.close();
        });
    }

    positionModal(target) {
        const targetEl = document.querySelector(target);
        if (!targetEl) return;

        const rect = targetEl.getBoundingClientRect();
        const modalRect = this.modal.getBoundingClientRect();

        // Position modal next to the highlighted element
        let left = rect.right + 20;
        let top = rect.top + (rect.height / 2) - (modalRect.height / 2);

        // Ensure modal stays within viewport
        if (left + modalRect.width > window.innerWidth) {
            left = rect.left - modalRect.width - 20;
        }

        if (top + modalRect.height > window.innerHeight) {
            top = window.innerHeight - modalRect.height - 20;
        }

        if (top < 20) top = 20;

        this.modal.style.left = `${left}px`;
        this.modal.style.top = `${top}px`;
    }

    highlight(target) {
        // Remove previous highlights
        document.querySelectorAll('.sidebar-highlight').forEach(el => {
            el.classList.remove('sidebar-highlight');
            // Remove highlight from parent menu-item if exists
            if (el.closest('.menu-item')) {
                el.closest('.menu-item').classList.remove('sidebar-highlight');
            }
        });

        if (!target) return;

        const element = document.querySelector(target);
        if (element) {
            element.classList.add('sidebar-highlight');
            // Also highlight the parent menu-item for better visibility
            if (element.closest('.menu-item')) {
                element.closest('.menu-item').classList.add('sidebar-highlight');
            }
            // Smooth scroll to the element
            element.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center'
            });
        }
    }

    render() {
        const step = this.steps[this.currentStep];
        
        // Update highlight
        this.highlight(step.target);
        
        // Position modal
        this.positionModal(step.target);

        // Generate progress dots
        const dots = this.steps.map((_, index) => 
            `<div class="guided-tour-dot ${index === this.currentStep ? 'active' : ''}"></div>`
        ).join('');

        // Update content
        this.content.innerHTML = `
            <h3>${step.title}</h3>
            <p>${step.content}</p>
            <div class="guided-tour-buttons">
                ${this.currentStep > 0 ? 
                    `<button class="guided-tour-button guided-tour-button-secondary" id="tourPrev">Previous</button>` 
                    : ''
                }
                <button class="guided-tour-button guided-tour-button-secondary" id="tourSkip">Skip Tour</button>
                <button class="guided-tour-button guided-tour-button-primary" id="tourNext">
                    ${this.currentStep === this.steps.length - 1 ? 'Finish' : 'Next'}
                </button>
            </div>
            <div class="guided-tour-progress">${dots}</div>
        `;

        // Wire up buttons
        document.getElementById('tourNext')?.addEventListener('click', this.next);
        document.getElementById('tourPrev')?.addEventListener('click', this.prev);
        document.getElementById('tourSkip')?.addEventListener('click', this.close);

        // Show modal with delay for smooth transition
        setTimeout(() => this.modal.classList.add('active'), 50);
    }

    start() {
        if (sessionStorage.getItem('guided_tour_completed')) return;
        
        this.currentStep = 0;
        document.body.classList.add('guided-tour-active');
        this.render();
    }

    next() {
        if (this.currentStep < this.steps.length - 1) {
            this.currentStep++;
            this.modal.classList.remove('active');
            setTimeout(() => this.render(), 300);
        } else {
            this.close();
        }
    }

    prev() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.modal.classList.remove('active');
            setTimeout(() => this.render(), 300);
        }
    }

    close() {
        this.modal.classList.remove('active');
        document.body.classList.remove('guided-tour-active');
        document.querySelectorAll('.sidebar-highlight').forEach(el => {
            el.classList.remove('sidebar-highlight');
        });
        sessionStorage.setItem('guided_tour_completed', 'true');
    }
}

// Initialize tour when document is ready
document.addEventListener('DOMContentLoaded', () => {
    const tour = new GuidedTour();
    // Start tour after a short delay
    setTimeout(() => tour.start(), 1000);
});