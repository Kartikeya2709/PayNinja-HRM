// Custom Tour Implementation
class CustomTour {
    constructor(steps) {
        this.steps = steps;
        this.currentStep = 0;
        this.modal = document.getElementById('customTourModal');
        this.modalContent = document.getElementById('tour-content');
        
        // Bind methods
        this.next = this.next.bind(this);
        this.prev = this.prev.bind(this);
        this.close = this.close.bind(this);
    }

    createBlurOverlay() {
        if (!document.querySelector('.tour-blur-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'tour-blur-overlay';
            document.body.appendChild(overlay);
        }
    }

    removeBlurOverlay() {
        const overlay = document.querySelector('.tour-blur-overlay');
        if (overlay) overlay.remove();
    }

    highlightElement(selector) {
        // Remove previous highlights
        document.querySelectorAll('.tour-highlight').forEach(el => {
            el.classList.remove('tour-highlight');
        });
        
        // Remove previous dims
        document.querySelectorAll('.dimmed').forEach(el => {
            el.classList.remove('dimmed');
        });

        if (!selector) return;

        const el = document.querySelector(selector);
        if (el) {
            // Add highlight
            el.classList.add('tour-highlight');
            
            // Dim other menu items
            const sidebarItems = document.querySelectorAll('.menu-item');
            sidebarItems.forEach(item => {
                if (!item.contains(el)) {
                    item.classList.add('dimmed');
                }
            });

            // Ensure element is visible
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    renderStep() {
        const step = this.steps[this.currentStep];
        this.highlightElement(step.selector);

        const dotsHTML = this.steps
            .map((_, index) => `<div class="progress-dot ${index === this.currentStep ? 'active' : ''}"></div>`)
            .join('');

        this.modalContent.innerHTML = `
            <h4>${step.title}</h4>
            <p>${step.content}</p>
            <div class="tour-controls">
                ${this.currentStep > 0 ? '<button class="tour-btn tour-btn-secondary" id="prevBtn">Previous</button>' : ''}
                <button class="tour-btn tour-btn-secondary" id="skipBtn">Skip Tour</button>
                <button class="tour-btn tour-btn-primary" id="nextBtn">
                    ${this.currentStep === this.steps.length - 1 ? 'Finish' : 'Next'}
                </button>
            </div>
            <div class="progress-container">${dotsHTML}</div>
        `;

        // Wire up button events
        document.getElementById('nextBtn').addEventListener('click', this.next);
        document.getElementById('skipBtn').addEventListener('click', this.close);
        const prevBtn = document.getElementById('prevBtn');
        if (prevBtn) prevBtn.addEventListener('click', this.prev);
    }

    next() {
        if (this.currentStep < this.steps.length - 1) {
            this.currentStep++;
            this.renderStep();
        } else {
            this.close();
        }
    }

    prev() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.renderStep();
        }
    }

    close() {
        sessionStorage.setItem('rockethr_sidebar_tour_shown', 'true');
        this.removeBlurOverlay();
        this.modal.classList.remove('show');
        document.querySelectorAll('.tour-highlight').forEach(el => {
            el.classList.remove('tour-highlight');
        });
        document.querySelectorAll('.dimmed').forEach(el => {
            el.classList.remove('dimmed');
        });
    }

    start() {
        this.createBlurOverlay();
        this.modal.classList.add('show');
        this.currentStep = 0;
        this.renderStep();
    }
}

// Initialize tour when document is ready
document.addEventListener('DOMContentLoaded', () => {
    if (sessionStorage.getItem('rockethr_sidebar_tour_shown')) return;

    const tourSteps = [
        {
            title: 'Welcome to RocketHR',
            content: 'Let\'s take a quick tour of your dashboard and key features.',
            selector: null
        },
        {
            title: 'Dashboard Overview',
            content: 'Your dashboard shows key HR metrics and quick actions.',
            selector: '.menu-item a[href*="home"]'
        },
        {
            title: 'Attendance Management',
            content: 'Track your attendance, check-in/out, and view reports.',
            selector: '.menu-item a[href*="attendance"]'
        },
        {
            title: 'Leave Management',
            content: 'Apply for leave and monitor your leave balance.',
            selector: '.menu-item a[href*="leave"]'
        },
        {
            title: 'Payroll',
            content: 'Access your salary information and payslips.',
            selector: '.menu-item a[href*="payroll"]'
        },
        {
            title: 'Tour Complete',
            content: 'You\'re all set! Explore your dashboard to get started.',
            selector: null
        }
    ];

    const tour = new CustomTour(tourSteps);
    
    // Start tour after a short delay
    setTimeout(() => {
        tour.start();
    }, 800);
});