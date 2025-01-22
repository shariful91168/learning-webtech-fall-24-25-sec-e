// Utility function to validate form fields
function validateForm(fields) {
    for (const field of fields) {
        if (!field.value.trim()) {
            alert(`The ${field.name || field.id} field is required.`);
            field.focus();
            return false;
        }
        // Additional field-specific validation
        if (field.type === 'email' && !validateEmail(field.value)) {
            alert('Please enter a valid email address.');
            field.focus();
            return false;
        }
        if (field.type === 'file' && field.files.length === 0) {
            alert('Please upload a file.');
            field.focus();
            return false;
        }
    }
    return true;
}

// Email validation helper function
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Utility function to send AJAX requests using JSON
function sendAjaxRequest(url, data, responseElementId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function () {
        const responseElement = document.getElementById(responseElementId);
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                responseElement.innerText = response.message || 'Operation successful.';
            } catch (e) {
                responseElement.innerText = xhr.responseText || 'Unexpected response.';
            }
        } else {
            responseElement.innerText = `Error: ${xhr.statusText}`;
        }
    };
    xhr.onerror = function () {
        alert('Failed to connect to the server. Please try again.');
    };
    xhr.send(JSON.stringify(data));
}

// Apply for Jobs
document.getElementById('applyJobForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const fields = [document.getElementById('jobTitle'), document.getElementById('resume')];
    if (!validateForm(fields)) return;
    const data = { jobTitle: fields[0].value, resume: fields[1].value };
    sendAjaxRequest('../CONTROLLER/apply_job.php', data, 'applyJobResponse');
});

// Build Resume
document.getElementById('resumeBuilderForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const fields = [
        document.getElementById('name'),
        document.getElementById('skills'),
        document.getElementById('experience'),
        document.getElementById('education'),
    ];
    if (!validateForm(fields)) return;
    const data = {
        name: fields[0].value,
        skills: fields[1].value,
        experience: fields[2].value,
        education: fields[3].value,
    };
    sendAjaxRequest('../CONTROLLER/resume_builder.php', data, 'resumeResponse');
});

// Schedule Interview
document.getElementById('scheduleInterviewForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const fields = [document.getElementById('applicantId'), document.getElementById('interviewDate')];
    if (!validateForm(fields)) return;
    const data = { applicantId: fields[0].value, interviewDate: fields[1].value };
    sendAjaxRequest('../CONTROLLER/schedule_interview.php', data, 'scheduleResponse');
});

// Job Post Analytics
document.getElementById('jobAnalyticsForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const fields = [document.getElementById('jobId'), document.getElementById('startDate'), document.getElementById('endDate')];
    if (!validateForm(fields)) return;
    const data = { jobId: fields[0].value, startDate: fields[1].value, endDate: fields[2].value };
    sendAjaxRequest('../CONTROLLER/job_post_analytics.php', data, 'analyticsResponse');
});

// Applicant Tracking
document.getElementById('applicantTrackingForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const fields = [document.getElementById('applicantId')];
    if (!validateForm(fields)) return;
    const data = { applicantId: fields[0].value };
    sendAjaxRequest('../CONTROLLER/applicant_tracking.php', data, 'trackingResponse');
});
