class ModernTour {
    constructor() {
        this.steps = [
            {
                title: 'Welcome to PayNinja HRM',
                content: 'Let\'s take a quick tour of your dashboard. We\'ll show you where to find everything you need.',
                target: null,
                icon: '👋'
            },
            {
                title: 'Your Dashboard',
                content: 'This is your dashboard where you can see all your important HR metrics and quick actions.',
                target: '.menu-item a[href*="home"]',
                icon: '📊'
            },
            {
                title: 'Attendance Management',
                content: 'Track your attendance, check-in/out times, and view attendance reports here.',
                target: '.menu-item a[href*="attendance"]',
                icon: '🕒'
            },
            {
                title: 'Leave Management',
                content: 'Apply for leave, check your leave balance, and track leave requests.',
                target: '.menu-item a[href*="leave"]',
                icon: '✈️'
            },
            {
                title: 'Payroll Information',
                content: 'Access your salary information, payslips, and other payroll-related details.',
                target: '.menu-item a[href*="payroll"]',
                icon: '💰'
            }
        ];
        
        this.currentStep = 0;
        this.init();
    }

    init() {
        if (!document.querySelector('.modern-tour-container')) {
            const container = document.createElement('div');
            container.className = 'modern-tour-container';
            container.innerHTML = `
                <div class="modern-tour-overlay"></div>
                <div class="modern-tour-modal">
                    <div class="modern-tour-content"></div>
                </div>
            `;
            document.body.appendChild(container);
        }

        this.container = document.querySelector('.modern-tour-container');
        this.content = document.querySelector('.modern-tour-content');
        
        // Bind methods
        this.next = this.next.bind(this);
        this.prev = this.prev.bind(this);
        this.close = this.close.bind(this);
        
        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.container.classList.contains('active')) {
                this.close();
            }
        });
    }

    start() {
        if (sessionStorage.getItem('modern_tour_completed')) {
            return;
        }

        this.currentStep = 0;
        this.container.classList.add('active');
        this.render();
        
        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';
    }

    render() {
        const step = this.steps[this.currentStep];
        
        // Clear previous highlight
        document.querySelectorAll('.tour-highlight').forEach(el => {
            el.classList.remove('tour-highlight');
        });

        // Highlight new target if exists
        if (step.target) {
            const target = document.querySelector(step.target);
            if (target) {
                target.classList.add('tour-highlight');
                // Ensure target is visible
                target.scrollIntoView({ block: 'center' });
            }
        }

        // Generate progress dots
        const dots = this.steps.map((_, index) => 
            `<div class="modern-tour-dot ${index === this.currentStep ? 'active' : ''}"></div>`
        ).join('');

        // Update content with animation
        this.content.style.opacity = '0';
        setTimeout(() => {
            this.content.innerHTML = `
                <div class="step-icon" style="font-size: 2rem; margin-bottom: 1rem;">
                    ${step.icon}
                </div>
                <h3>${step.title}</h3>
                <p>${step.content}</p>
                <div class="modern-tour-buttons">
                    ${this.currentStep > 0 ? `
                        <button class="modern-tour-button modern-tour-button-secondary" id="tourPrev">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                    ` : ''}
                    <button class="modern-tour-button modern-tour-button-secondary" id="tourSkip">
                        Skip Tour
                    </button>
                    <button class="modern-tour-button modern-tour-button-primary" id="tourNext">
                        ${this.currentStep === this.steps.length - 1 ? 'Finish' : 'Next'} 
                        ${this.currentStep === this.steps.length - 1 ? '✨' : '<i class="fas fa-arrow-right"></i>'}
                    </button>
                </div>
                <div class="modern-tour-progress">${dots}</div>
            `;
            this.content.style.opacity = '1';

            // Wire up buttons
            const nextBtn = document.getElementById('tourNext');
            const prevBtn = document.getElementById('tourPrev');
            const skipBtn = document.getElementById('tourSkip');

            if (nextBtn) nextBtn.addEventListener('click', this.next);
            if (prevBtn) prevBtn.addEventListener('click', this.prev);
            if (skipBtn) skipBtn.addEventListener('click', this.close);
        }, 300);
    }

    next() {
        if (this.currentStep < this.steps.length - 1) {
            this.currentStep++;
            this.render();
        } else {
            this.close();
        }
    }

    prev() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.render();
        }
    }

    close() {
        this.container.classList.remove('active');
        document.querySelectorAll('.tour-highlight').forEach(el => {
            el.classList.remove('tour-highlight');
        });
        sessionStorage.setItem('modern_tour_completed', 'true');
        document.documentElement.style.scrollBehavior = 'auto';
    }
}

// Initialize tour when document is ready
document.addEventListener('DOMContentLoaded', () => {
    const tour = new ModernTour();
    // Start tour after a short delay
    setTimeout(() => tour.start(), 1000);
});