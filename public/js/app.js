// Initialize all charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    initializePayrollChart();
    initializeAttendanceChart();
    initializeDepartmentChart();
});

// Employee Calendar
function initializeCalendar() {
    const calendarEl = document.getElementById('employeeCalendar');
    if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true
        });
        calendar.render();
    }
}

// Payroll Expense Chart
function initializePayrollChart() {
    const ctx = document.getElementById('payrollChart');
    if (ctx) {
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(255, 99, 132, 0.4)');
        gradient.addColorStop(1, 'rgba(255, 99, 132, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Payroll Expense (₹ in Lakhs)',
                    data: [12, 15, 13, 14, 16, 18, 17, 19, 20, 18, 17, 21],
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: '#ff5c5c',
                    borderWidth: 2,
                    tension: 0.3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#ff5c5c',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            boxWidth: 20,
                            usePointStyle: false,
                            font: {
                                size: 13,
                                weight: 'bold'
                            }
                        },
                        position: 'top',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) {
                                return `₹${val}L`;
                            }
                        },
                        grid: { drawBorder: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
}

// Attendance Chart
function initializeAttendanceChart() {
    const ctxAttend = document.getElementById('attendanceChart');
    if (ctxAttend) {
        new Chart(ctxAttend.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'On Leave'],
                datasets: [{
                    label: 'Employees',
                    data: [115, 5, 8],
                    backgroundColor: ['#4bc0c0', '#ff6384', '#ffcd56']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}

// Department Chart
function initializeDepartmentChart() {
    const ctxDept = document.getElementById('departmentChart');
    if (!ctxDept) return;

    fetch('/department-chart-data')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(chartData => {
            new Chart(ctxDept.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Number of Employees',
                        data: chartData.data,
                        backgroundColor: chartData.colors,
                        borderWidth: 1,
                        borderColor: chartData.colors.map(color => color.replace('0.8', '1')),
                        hoverBackgroundColor: chartData.colors.map(color => color.replace('0.8', '0.9'))
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Employees: ${context.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    if (value % 1 === 0) {
                                        return value;
                                    }
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading department chart:', error);
            const ctx = ctxDept.getContext('2d');
            ctx.font = '16px Arial';
            ctx.fillStyle = '#666';
            ctx.textAlign = 'center';
            ctx.fillText('Failed to load department data', ctx.canvas.width/2, ctx.canvas.height/2);
        });
}


// Payroll Expense Chart
const ctx = document.getElementById('payrollChart');

const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(255, 99, 132, 0.4)');
gradient.addColorStop(1, 'rgba(255, 99, 132, 0)');


new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Payroll Expense (₹ in Lakhs)',
            data: [12, 15, 13, 14, 16, 18, 17, 19, 20, 18, 17, 21],
            fill: true,
            backgroundColor: gradient,
            borderColor: '#ff5c5c',
            borderWidth: 2,
            tension: 0.3,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#ff5c5c',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                labels: {
                    boxWidth: 20,
                    usePointStyle: false,
                    font: {
                        size: 13,
                        weight: 'bold'
                    }
                },
                position: 'top',
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: (val) => `₹${val}L`
                },
                grid: { drawBorder: false }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});


// Attendance Chart

const ctxAttend = document.getElementById('attendanceChart').getContext('2d');
new Chart(ctxAttend, {
    type: 'doughnut',
    data: {
        labels: ['Present', 'Absent', 'On Leave'],
        datasets: [{
            label: 'Employees',
            data: [115, 5, 8],
            backgroundColor: ['#4bc0c0', '#ff6384', '#ffcd56']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});


// Department-wise Employee Distribution Chart



document.addEventListener('DOMContentLoaded', loadDepartmentChart);


