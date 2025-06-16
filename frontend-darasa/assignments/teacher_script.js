// Tab navigation functionality
document.querySelectorAll('.nav-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        // Update active tab
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        
        // Hide all sections
        document.getElementById('assignments-section').style.display = 'none';
        document.getElementById('create-section').style.display = 'none';
        document.getElementById('grading-section').style.display = 'none';
        
        // Show selected section
        if (tab.dataset.tab === 'assignments') {
            document.getElementById('assignments-section').style.display = 'block';
        } else if (tab.dataset.tab === 'create') {
            document.getElementById('create-section').style.display = 'block';
        } else if (tab.dataset.tab === 'grading') {
            document.getElementById('grading-section').style.display = 'block';
        }
    });
});

// New Assignment button functionality
document.getElementById('new-assignment-btn').addEventListener('click', () => {
    document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('[data-tab="create"]').classList.add('active');
    
    document.getElementById('assignments-section').style.display = 'none';
    document.getElementById('create-section').style.display = 'block';
    document.getElementById('grading-section').style.display = 'none';
});

// Form submission handling (would be handled by PHP in production)
document.getElementById('assignment-form').addEventListener('submit', (e) => {
    e.preventDefault();
    alert('Assignment created successfully! In a real application, this would be sent to the server.');
    // Here you would collect form data and send to PHP backend
});

// Student selection in grading interface
document.querySelectorAll('.student-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.student-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
    });
});

// Comment button functionality
document.querySelectorAll('.comment-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const comment = btn.textContent;
        const textarea = document.getElementById('feedback');
        textarea.value += (textarea.value ? "\n\n" : "") + comment;
    });
});

// Integration points simulation
document.querySelectorAll('.integration-point').forEach(element => {
    element.addEventListener('mouseenter', () => {
        element.style.boxShadow = '0 0 0 3px rgba(0, 123, 255, 0.5)';
    });
    element.addEventListener('mouseleave', () => {
        element.style.boxShadow = '';
    });
});