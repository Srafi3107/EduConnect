// Custom Javascript
document.addEventListener("DOMContentLoaded", function() {
    // Add any global initialization logic here
    console.log("Home Tutor Finding System Loaded.");
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            let bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
