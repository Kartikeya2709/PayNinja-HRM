class Tour {
    constructor() {
        this.steps = [
            {
                title: 'Welcome to RocketHR',
                content: 'Let\'s take a quick tour of your dashboard and key features.',
                target: null
            },
            {
                title: 'Dashboard Overview',
                content: 'Your dashboard shows key HR metrics and quick actions.',
                target: '.menu-item a[href*="home"]'
            },
            {
                title: 'Attendance Management',
                content: 'Track your attendance, check-in/out, and view reports.',
                target: '.menu-item a[href*="attendance"]'
            },
            {
                title: 'Leave Management',
                content: 'Apply for leave and monitor your leave balance.',
                target: '.menu-item a[href*="leave"]'
            },
            {
                title: 'Payroll',
                content: 'Access your salary information and payslips.',
                target: '.menu-item a[href*="payroll"]'
            }
        ];
        
        this.currentStep = 0;
        this.init();
    }

    init() {
        // Create tour container if it doesn't exist
        if (!document.querySelector('.tour-container')) {
            const container = document.createElement('div');
            container.className = 'tour-container';
            container.innerHTML = `
                <div class="tour-overlay"></div>
                <div class="tour-modal">
                    <div class="tour-content"></div>
                </div>
            `;
            document.body.appendChild(container);
        }

        this.container = document.querySelector('.tour-container');
        this.content = document.querySelector('.tour-content');
        
        // Bind methods
        this.next = this.next.bind(this);
        this.prev = this.prev.bind(this);
        this.close = this.close.bind(this);
    }

    start() {
        if (sessionStorage.getItem('tour_completed')) {
            return;
        }

        this.currentStep = 0;
        this.container.classList.add('active');
        this.render();
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
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Generate progress dots
        const dots = this.steps.map((_, index) => 
            `<div class="tour-dot ${index === this.currentStep ? 'active' : ''}"></div>`
        ).join('');

        // Update content
        this.content.innerHTML = `
            <h3>${step.title}</h3>
            <p>${step.content}</p>
            <div class="tour-buttons">
                ${this.currentStep > 0 ? `
                    <button class="tour-button tour-button-secondary" id="tourPrev">Previous</button>
                ` : ''}
                <button class="tour-button tour-button-secondary" id="tourSkip">Skip Tour</button>
                <button class="tour-button tour-button-primary" id="tourNext">
                    ${this.currentStep === this.steps.length - 1 ? 'Finish' : 'Next'}
                </button>
            </div>
            <div class="tour-progress">${dots}</div>
        `;

        // Wire up buttons
        const nextBtn = document.getElementById('tourNext');
        const prevBtn = document.getElementById('tourPrev');
        const skipBtn = document.getElementById('tourSkip');

        if (nextBtn) nextBtn.addEventListener('click', this.next);
        if (prevBtn) prevBtn.addEventListener('click', this.prev);
        if (skipBtn) skipBtn.addEventListener('click', this.close);
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
        sessionStorage.setItem('tour_completed', 'true');
    }
}

// Initialize tour when document is ready
document.addEventListener('DOMContentLoaded', () => {
    const tour = new Tour();
    // Start tour after a short delay
    setTimeout(() => tour.start(), 1000);
});