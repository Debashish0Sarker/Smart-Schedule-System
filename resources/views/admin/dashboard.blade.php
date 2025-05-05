<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Smart Schedule System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <button id="logoutBtn" class="btn btn-light">Logout</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div id="authError" class="alert alert-danger d-none">
            Authentication error. Please <a href="/admin/login">login again</a>.
        </div>
        
        <!-- Analytics Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">Total Students</h6>
                        <h2 class="card-text" id="totalStudents">0</h2>
                        <p class="mb-0"><small>Active enrollments</small></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">Active Courses</h6>
                        <h2 class="card-text" id="totalCourses">0</h2>
                        <p class="mb-0"><small>Current semester</small></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">Pending Approvals</h6>
                        <h2 class="card-text" id="pendingApprovals">0</h2>
                        <p class="mb-0"><small>Student registrations</small></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="card-title">Open Complaints</h6>
                        <h2 class="card-text" id="openComplaints">0</h2>
                        <p class="mb-0"><small>Needs attention</small></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="dashboardContent" class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Student Management</h5>
                        <div class="d-flex">
                            <input type="text" id="searchInput" class="form-control form-control-sm me-2" placeholder="Search students...">
                            <button id="searchBtn" class="btn btn-sm btn-primary">Search</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTable">
                                    <!-- Student data will be loaded here dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Student Complaints</h5>
                    </div>
                    <div class="card-body">
                        <div id="complaintsList" class="list-group">
                            <!-- Complaints will be loaded here dynamically -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Management Section -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Course Management</h5>
                        <button class="btn btn-primary btn-sm" onclick="openAddCourseModal()">Add New Course</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Students</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="coursesTable">
                                    <!-- Course data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Progress Section -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Academic Progress</h5>
                        <div>
                            <button class="btn btn-primary btn-sm me-2" onclick="generateReport()">Generate Report</button>
                            <select id="reportType" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="overall">Overall Report</option>
                                <option value="course">Course Report</option>
                                <option value="student">Student Report</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <!-- Overall Stats -->
                            <div class="col-md-6">
                                <canvas id="gradeDistributionChart"></canvas>
                            </div>
                            <div class="col-md-6">
                                <canvas id="coursePerformanceChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Performance Metrics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Average Grade</h6>
                                        <h3 class="card-text" id="averageGrade">0%</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Passing Rate</h6>
                                        <h3 class="card-text" id="passingRate">0%</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Total Enrollments</h6>
                                        <h3 class="card-text" id="totalEnrollments">0</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Completion Rate</h6>
                                        <h3 class="card-text" id="completionRate">0%</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Top Students -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Top Performing Students</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Average Grade</th>
                                                <th>Courses Enrolled</th>
                                            </tr>
                                        </thead>
                                        <tbody id="topStudentsTable">
                                            <!-- Top students will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Generation Modal -->
            <div class="modal fade" id="reportModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Generate Progress Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="reportForm">
                                <div class="mb-3">
                                    <label for="reportDateRange" class="form-label">Date Range</label>
                                    <select id="reportDateRange" class="form-select">
                                        <option value="week">Last Week</option>
                                        <option value="month">Last Month</option>
                                        <option value="semester" selected>Current Semester</option>
                                    </select>
                                </div>
                                <div id="reportSelection" class="mb-3 d-none">
                                    <label for="reportTarget" class="form-label">Select Target</label>
                                    <select id="reportTarget" class="form-select">
                                        <!-- Options will be loaded dynamically -->
                                    </select>
                                </div>
                            </form>
                            <div id="reportContent" class="mt-4">
                                <!-- Report content will be loaded here -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="downloadReport()">Download Report</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Add Course Modal -->
        <div class="modal fade" id="addCourseModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addCourseForm">
                            <div class="mb-3">
                                <label for="courseCode" class="form-label">Course Code</label>
                                <input type="text" class="form-control" id="courseCode" required>
                            </div>
                            <div class="mb-3">
                                <label for="courseName" class="form-label">Course Name</label>
                                <input type="text" class="form-control" id="courseName" required>
                            </div>
                            <div class="mb-3">
                                <label for="courseDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="courseDescription" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="addCourse()">Add Course</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollment Management Modal -->
        <div class="modal fade" id="enrollmentModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Course Enrollments</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Enrolled Students</h6>
                                <div class="list-group" id="enrolledStudentsList">
                                    <!-- Enrolled students will be loaded here -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Available Students</h6>
                                <div class="list-group" id="availableStudentsList">
                                    <!-- Available students will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Details Modal -->
        <div class="modal fade" id="courseDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Course Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Course Information</h6>
                                <table class="table">
                                    <tr>
                                        <th>Code:</th>
                                        <td id="detailsCourseCode"></td>
                                    </tr>
                                    <tr>
                                        <th>Name:</th>
                                        <td id="detailsCourseName"></td>
                                    </tr>
                                    <tr>
                                        <th>Description:</th>
                                        <td id="detailsCourseDescription"></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td id="detailsCourseStatus"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Statistics</h6>
                                <table class="table">
                                    <tr>
                                        <th>Total Students:</th>
                                        <td id="detailsTotalStudents"></td>
                                    </tr>
                                    <tr>
                                        <th>Average Grade:</th>
                                        <td id="detailsAverageGrade"></td>
                                    </tr>
                                    <tr>
                                        <th>Assignments:</th>
                                        <td id="detailsAssignmentCount"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Course Modal -->
        <div class="modal fade" id="editCourseModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editCourseForm">
                            <input type="hidden" id="editCourseId">
                            <div class="mb-3">
                                <label for="editCourseCode" class="form-label">Course Code</label>
                                <input type="text" class="form-control" id="editCourseCode" required>
                            </div>
                            <div class="mb-3">
                                <label for="editCourseName" class="form-label">Course Name</label>
                                <input type="text" class="form-control" id="editCourseName" required>
                            </div>
                            <div class="mb-3">
                                <label for="editCourseDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="editCourseDescription" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editCourseActive">
                                    <label class="form-check-label" for="editCourseActive">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="updateCourse()">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Get token from session first, then localStorage, then cookie
        let token = '{{ session("admin_token") }}' || localStorage.getItem('token');
        
        // Fallback to cookie if other methods fail
        if (!token) {
            const tokenMatch = document.cookie.match(/(?:^|;\s*)admin_token=([^;]*)/);
            if (tokenMatch) token = tokenMatch[1];
        }

        // If we got a token from session, store it in localStorage
        if (token && !localStorage.getItem('token')) {
            localStorage.setItem('token', token);
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const dashboardContent = document.getElementById('dashboardContent');
        const authError = document.getElementById('authError');
        
        // Verify authentication
        async function verifyAuth() {
            if (!token) {
                showAuthError();
                return false;
            }
            
            try {
                // Test the token with our auth-check endpoint
                const response = await fetch('/api/admin/auth-check', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (!response.ok || !data.authenticated) {
                    showAuthError();
                    return false;
                }
                
                // Hide error and show content if auth successful
                authError.classList.add('d-none');
                dashboardContent.classList.remove('d-none');
                return true;
            } catch (error) {
                console.error('Auth verification error:', error);
                showAuthError();
                return false;
            }
        }
        
        function showAuthError() {
            // Clear tokens and show error message
            localStorage.removeItem('token');
            document.cookie = 'admin_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
            authError.classList.remove('d-none');
            dashboardContent.classList.add('d-none');
            
            // Redirect to login after a short delay
            setTimeout(() => {
                window.location.href = '/admin/login';
            }, 2000);
        }

        // Load Students
        async function searchStudents(query = '') {
            if (!await verifyAuth()) return;
            
            try {
                const url = query 
                    ? `/api/admin/students/search?${new URLSearchParams({name: query}).toString()}`
                    : '/api/admin/students/search';
                    
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    if (response.status === 401 || response.status === 403) {
                        // Authentication failed
                        await verifyAuth();
                        return;
                    }
                    throw new Error('Failed to fetch students');
                }
                
                const data = await response.json();
                displayStudents(data);
            } catch (error) {
                console.error('Error fetching students:', error);
            }
        }

        function displayStudents(students) {
            const tbody = document.getElementById('studentsTable');
            tbody.innerHTML = '';
            
            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No students found</td></tr>';
                return;
            }
            
            students.forEach(student => {
                const statusBadgeClass = student.status === 'approved' ? 'bg-success' : 'bg-warning';
                
                tbody.innerHTML += `
                    <tr>
                        <td>${student.id}</td>
                        <td>${student.name}</td>
                        <td>${student.email}</td>
                        <td><span class="badge ${statusBadgeClass}">${student.status || 'pending'}</span></td>
                        <td>
                            ${student.status !== 'approved' ? `<button class="btn btn-sm btn-success me-2" onclick="approveUser(${student.id})">Approve</button>` : ''}
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(${student.id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }

        // User Management Functions
        async function approveUser(studentId) {
            if (!await verifyAuth()) return;
            
            try {
                const response = await fetch(`/api/admin/students/${studentId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    alert('User approved successfully');
                    // Refresh the student list
                    await searchStudents();
                } else {
                    const data = await response.json();
                    alert(`Error: ${data.message || 'Something went wrong'}`);
                    
                    if (response.status === 401 || response.status === 403) {
                        await verifyAuth();
                    }
                }
            } catch (error) {
                console.error('Error approving user:', error);
                alert('Failed to approve user');
            }
        }

        async function deleteUser(studentId) {
            if (!await verifyAuth()) return;
            
            if (confirm('Are you sure you want to delete this user?')) {
                try {
                    const response = await fetch(`/api/admin/students/${studentId}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'include'
                    });
                    
                    if (response.ok) {
                        alert('User deleted successfully');
                        // Refresh the student list
                        await searchStudents();
                    } else {
                        const data = await response.json();
                        alert(`Error: ${data.message || 'Something went wrong'}`);
                        
                        if (response.status === 401 || response.status === 403) {
                            await verifyAuth();
                        }
                    }
                } catch (error) {
                    console.error('Error deleting user:', error);
                    alert('Failed to delete user');
                }
            }
        }

        // Load Complaints
        async function loadComplaints() {
            if (!await verifyAuth()) return;
            
            try {
                const response = await fetch('/api/admin/complaints', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    if (response.status === 401 || response.status === 403) {
                        await verifyAuth();
                        return;
                    }
                    throw new Error('Failed to fetch complaints');
                }
                
                const data = await response.json();
                displayComplaints(data);
            } catch (error) {
                console.error('Error fetching complaints:', error);
            }
        }

        function displayComplaints(complaints) {
            const list = document.getElementById('complaintsList');
            
            if (!complaints || complaints.length === 0) {
                list.innerHTML = '<div class="list-group-item">No complaints found</div>';
                return;
            }
            
            list.innerHTML = '';
            complaints.forEach(complaint => {
                const date = new Date(complaint.created_at).toLocaleDateString();
                const statusBadge = complaint.status === 'replied' 
                    ? '<span class="badge bg-success ms-1">Replied</span>' 
                    : '<span class="badge bg-warning ms-1">Pending</span>';
                
                list.innerHTML += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-1">${complaint.subject} ${statusBadge}</h5>
                            <small>${date}</small>
                        </div>
                        <p class="mb-1">${complaint.message}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small>From: ${complaint.student?.name || 'Unknown student'}</small>
                            ${complaint.status !== 'replied' ? `<button class="btn btn-sm btn-primary" onclick="replyToComplaint(${complaint.id})">Reply</button>` : ''}
                        </div>
                    </div>
                `;
            });
        }

        async function replyToComplaint(complaintId) {
            if (!await verifyAuth()) return;
            
            const reply = prompt('Enter your reply:');
            if (reply) {
                try {
                    const response = await fetch(`/api/admin/complaints/${complaintId}/reply`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'include',
                        body: JSON.stringify({ reply })
                    });
                    
                    if (response.ok) {
                        alert('Reply sent successfully');
                        await loadComplaints();
                    } else {
                        const data = await response.json();
                        alert(`Error: ${data.message || 'Failed to send reply'}`);
                        
                        if (response.status === 401 || response.status === 403) {
                            await verifyAuth();
                        }
                    }
                } catch (error) {
                    console.error('Error sending reply:', error);
                    alert('Failed to send reply');
                }
            }
        }

        // Helper function to refresh CSRF token
        async function refreshCsrfToken() {
            try {
                const response = await fetch('/refresh-csrf', {
                    credentials: 'include'
                });
                const data = await response.json();
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                return data.token;
            } catch (error) {
                console.error('Error refreshing CSRF token:', error);
                return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            }
        }

        // Load dashboard statistics
        async function loadDashboardStats() {
            if (!await verifyAuth()) return;
            
            try {
                const response = await fetch('/api/admin/dashboard/stats', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    const stats = await response.json();
                    document.getElementById('totalStudents').textContent = stats.totalStudents || 0;
                    document.getElementById('totalCourses').textContent = stats.totalCourses || 0;
                    document.getElementById('pendingApprovals').textContent = stats.pendingApprovals || 0;
                    document.getElementById('openComplaints').textContent = stats.openComplaints || 0;
                }
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
            }
        }

        // Course Management Functions
        let courseModal;
        
        function openAddCourseModal() {
            if (!courseModal) {
                courseModal = new bootstrap.Modal(document.getElementById('addCourseModal'));
            }
            courseModal.show();
        }
        
        async function loadCourses() {
            if (!await verifyAuth()) return;
            
            try {
                const response = await fetch('/api/admin/courses', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    const courses = await response.json();
                    displayCourses(courses);
                }
            } catch (error) {
                console.error('Error loading courses:', error);
            }
        }
        
        function displayCourses(courses) {
            const tbody = document.getElementById('coursesTable');
            tbody.innerHTML = '';
            
            if (courses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No courses found</td></tr>';
                return;
            }
            
            courses.forEach(course => {
                const statusBadge = course.active 
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
                    
                tbody.innerHTML += `
                    <tr>
                        <td>${course.code}</td>
                        <td>${course.name}</td>
                        <td>${course.students_count || 0}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-primary me-2" onclick="openEnrollmentModal(${course.id})">Enrollments</button>
                            <button class="btn btn-sm btn-info me-2" onclick="viewCourseDetails(${course.id})">View</button>
                            <button class="btn btn-sm btn-warning me-2" onclick="editCourse(${course.id})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteCourse(${course.id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }
        
        async function addCourse() {
            if (!await verifyAuth()) return;
            
            const code = document.getElementById('courseCode').value.trim();
            const name = document.getElementById('courseName').value.trim();
            const description = document.getElementById('courseDescription').value.trim();
            
            try {
                const response = await fetch('/api/admin/courses', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include',
                    body: JSON.stringify({ code, name, description })
                });
                
                if (response.ok) {
                    courseModal.hide();
                    document.getElementById('addCourseForm').reset();
                    alert('Course added successfully');
                    await loadCourses();
                } else {
                    const data = await response.json();
                    alert(`Error: ${data.message || 'Failed to add course'}`);
                }
            } catch (error) {
                console.error('Error adding course:', error);
                alert('Failed to add course');
            }
        }

        let enrollmentModal;
        let currentCourseId;
        
        function openEnrollmentModal(courseId) {
            currentCourseId = courseId;
            if (!enrollmentModal) {
                enrollmentModal = new bootstrap.Modal(document.getElementById('enrollmentModal'));
            }
            loadEnrollmentStatus(courseId);
            enrollmentModal.show();
        }
        
        async function loadEnrollmentStatus(courseId) {
            if (!await verifyAuth()) return;
            
            try {
                const response = await fetch(`/api/admin/courses/${courseId}/enrollment-status`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    displayEnrollmentLists(data.enrolled, data.available);
                }
            } catch (error) {
                console.error('Error loading enrollment status:', error);
            }
        }
        
        function displayEnrollmentLists(enrolled, available) {
            const enrolledList = document.getElementById('enrolledStudentsList');
            const availableList = document.getElementById('availableStudentsList');
            
            enrolledList.innerHTML = enrolled.map(student => `
                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
                        onclick="manageEnrollment(${student.id}, 'remove')">
                    ${student.name} (${student.student_id})
                    <span class="badge bg-danger">Remove</span>
                </button>
            `).join('');
            
            availableList.innerHTML = available.map(student => `
                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
                        onclick="manageEnrollment(${student.id}, 'enroll')">
                    ${student.name} (${student.student_id})
                    <span class="badge bg-success">Add</span>
                </button>
            `).join('');
        }
        
        async function manageEnrollment(studentId, action) {
            if (!await verifyAuth()) return;
            
            try {
                const response = await fetch(`/api/admin/courses/${currentCourseId}/enrollments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        student_ids: [studentId],
                        action: action
                    })
                });
                
                if (response.ok) {
                    // Refresh the enrollment lists
                    await loadEnrollmentStatus(currentCourseId);
                    // Refresh the course list to update student counts
                    await loadCourses();
                } else {
                    const data = await response.json();
                    alert(`Error: ${data.message || 'Failed to update enrollment'}`);
                }
            } catch (error) {
                console.error('Error managing enrollment:', error);
                alert('Failed to update enrollment');
            }
        }

        // Academic Progress Functions
        let gradeDistributionChart;
        let coursePerformanceChart;
        let reportModal;
        
        // Initialize Charts
        function initializeCharts() {
            const gradeCtx = document.getElementById('gradeDistributionChart').getContext('2d');
            const courseCtx = document.getElementById('coursePerformanceChart').getContext('2d');
            
            gradeDistributionChart = new Chart(gradeCtx, {
                type: 'pie',
                data: {
                    labels: ['A', 'B', 'C', 'D', 'F'],
                    datasets: [{
                        data: [0, 0, 0, 0, 0],
                        backgroundColor: [
                            '#28a745',
                            '#17a2b8',
                            '#ffc107',
                            '#fd7e14',
                            '#dc3545'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Grade Distribution'
                        }
                    }
                }
            });
            
            coursePerformanceChart = new Chart(courseCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Average Grade',
                        data: [],
                        backgroundColor: '#007bff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Course Performance'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }
        
        async function loadAcademicProgress() {
            if (!await verifyAuth()) return;
            
            try {
                const response = await fetch('/api/admin/academic-progress', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    updateProgressDisplay(data);
                }
            } catch (error) {
                console.error('Error loading academic progress:', error);
            }
        }
        
        function updateProgressDisplay(data) {
            // Update metrics
            document.getElementById('averageGrade').textContent = `${Math.round(data.overall_stats.average_grade)}%`;
            document.getElementById('passingRate').textContent = `${Math.round(data.overall_stats.passing_rate)}%`;
            document.getElementById('totalEnrollments').textContent = data.overall_stats.total_enrollments;
            document.getElementById('completionRate').textContent = `${Math.round(data.overall_stats.completion_rate)}%`;
            
            // Update grade distribution chart
            const distribution = {A: 0, B: 0, C: 0, D: 0, F: 0};
            data.course_performance.forEach(course => {
                course.students.forEach(student => {
                    const grade = student.pivot.grade;
                    if (grade >= 90) distribution.A++;
                    else if (grade >= 80) distribution.B++;
                    else if (grade >= 70) distribution.C++;
                    else if (grade >= 60) distribution.D++;
                    else distribution.F++;
                });
            });
            
            gradeDistributionChart.data.datasets[0].data = Object.values(distribution);
            gradeDistributionChart.update();
            
            // Update course performance chart
            coursePerformanceChart.data.labels = data.course_performance.map(c => c.code);
            coursePerformanceChart.data.datasets[0].data = data.course_performance.map(c => c.average_grade);
            coursePerformanceChart.update();
            
            // Update top students table
            const tbody = document.getElementById('topStudentsTable');
            tbody.innerHTML = data.top_students.map(student => `
                <tr>
                    <td>${student.student_id}</td>
                    <td>${student.name}</td>
                    <td>${Math.round(student.average_grade)}%</td>
                    <td>${student.courses_count || 0}</td>
                </tr>
            `).join('');
        }
        
        function generateReport() {
            if (!reportModal) {
                reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
            }
            
            const reportType = document.getElementById('reportType').value;
            const reportSelection = document.getElementById('reportSelection');
            const reportTarget = document.getElementById('reportTarget');
            
            if (reportType !== 'overall') {
                reportSelection.classList.remove('d-none');
                // Load appropriate options based on report type
                loadReportTargets(reportType);
            } else {
                reportSelection.classList.add('d-none');
            }
            
            reportModal.show();
        }
        
        async function loadReportTargets(type) {
            if (!await verifyAuth()) return;
            
            const reportTarget = document.getElementById('reportTarget');
            reportTarget.innerHTML = '<option value="">Loading...</option>';
            
            try {
                let options = [];
                if (type === 'course') {
                    const response = await fetch('/api/admin/courses', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'include'
                    });
                    if (response.ok) {
                        const courses = await response.json();
                        options = courses.map(c => `<option value="${c.id}">${c.code} - ${c.name}</option>`);
                    }
                } else if (type === 'student') {
                    const response = await fetch('/api/admin/students/search', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'include'
                    });
                    if (response.ok) {
                        const students = await response.json();
                        options = students.map(s => `<option value="${s.id}">${s.student_id} - ${s.name}</option>`);
                    }
                }
                
                reportTarget.innerHTML = '<option value="">Select...</option>' + options.join('');
            } catch (error) {
                console.error('Error loading report targets:', error);
                reportTarget.innerHTML = '<option value="">Error loading options</option>';
            }
        }
        
        async function downloadReport() {
            if (!await verifyAuth()) return;
            
            const type = document.getElementById('reportType').value;
            const dateRange = document.getElementById('reportDateRange').value;
            const targetId = type !== 'overall' ? document.getElementById('reportTarget').value : null;
            
            try {
                const response = await fetch('/api/admin/progress-report', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        type,
                        id: targetId,
                        date_range: dateRange
                    })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    displayReport(data);
                } else {
                    alert('Failed to generate report');
                }
            } catch (error) {
                console.error('Error generating report:', error);
                alert('Failed to generate report');
            }
        }
        
        function displayReport(data) {
            const content = document.getElementById('reportContent');
            // Format and display the report data based on its type
            // This is a simplified example - you would want to format this nicely
            content.innerHTML = `
                <div class="report-container">
                    <h6>Report Summary</h6>
                    <p>Period: ${data.period.start} to ${data.period.end}</p>
                    <div class="metrics">
                        ${Object.entries(data.metrics).map(([key, value]) => `
                            <div class="metric-item">
                                <strong>${key.replace(/_/g, ' ').toUpperCase()}:</strong>
                                ${typeof value === 'number' ? Math.round(value) + (key.includes('rate') ? '%' : '') : value}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        let courseDetailsModal;
        let editCourseModal;
        
        function viewCourseDetails(courseId) {
            if (!courseDetailsModal) {
                courseDetailsModal = new bootstrap.Modal(document.getElementById('courseDetailsModal'));
            }
            
            loadCourseDetails(courseId);
            courseDetailsModal.show();
        }
        
        async function loadCourseDetails(courseId) {
            if (!await verifyAuth()) return;
            
            try {
                const response = await fetch(`/api/admin/courses/${courseId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    displayCourseDetails(data);
                }
            } catch (error) {
                console.error('Error loading course details:', error);
            }
        }
        
        function displayCourseDetails(data) {
            document.getElementById('detailsCourseCode').textContent = data.course.code;
            document.getElementById('detailsCourseName').textContent = data.course.name;
            document.getElementById('detailsCourseDescription').textContent = data.course.description || 'No description';
            document.getElementById('detailsCourseStatus').innerHTML = 
                data.course.active ? '<span class="badge bg-success">Active</span>' : 
                                   '<span class="badge bg-secondary">Inactive</span>';
            document.getElementById('detailsTotalStudents').textContent = data.total_students;
            document.getElementById('detailsAverageGrade').textContent = 
                data.average_grade ? `${Math.round(data.average_grade)}%` : 'No grades';
            document.getElementById('detailsAssignmentCount').textContent = data.assignments_count;
        }
        
        function editCourse(courseId) {
            if (!editCourseModal) {
                editCourseModal = new bootstrap.Modal(document.getElementById('editCourseModal'));
            }
            
            loadCourseForEdit(courseId);
            editCourseModal.show();
        }
        
        async function loadCourseForEdit(courseId) {
            if (!await verifyAuth()) return;
            
            try {
                const response = await fetch(`/api/admin/courses/${courseId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const course = data.course;
                    
                    document.getElementById('editCourseId').value = course.id;
                    document.getElementById('editCourseCode').value = course.code;
                    document.getElementById('editCourseName').value = course.name;
                    document.getElementById('editCourseDescription').value = course.description || '';
                    document.getElementById('editCourseActive').checked = course.active;
                }
            } catch (error) {
                console.error('Error loading course for edit:', error);
            }
        }
        
        async function updateCourse() {
            if (!await verifyAuth()) return;
            
            const courseId = document.getElementById('editCourseId').value;
            const data = {
                code: document.getElementById('editCourseCode').value.trim(),
                name: document.getElementById('editCourseName').value.trim(),
                description: document.getElementById('editCourseDescription').value.trim(),
                active: document.getElementById('editCourseActive').checked
            };
            
            try {
                const response = await fetch(`/api/admin/courses/${courseId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include',
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    editCourseModal.hide();
                    alert('Course updated successfully');
                    await loadCourses();
                } else {
                    const errorData = await response.json();
                    alert(`Error: ${errorData.message || 'Failed to update course'}`);
                }
            } catch (error) {
                console.error('Error updating course:', error);
                alert('Failed to update course');
            }
        }
        
        async function deleteCourse(courseId) {
            if (!await verifyAuth()) return;
            
            if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
                try {
                    const response = await fetch(`/api/admin/courses/${courseId}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'include'
                    });
                    
                    if (response.ok) {
                        alert('Course deleted successfully');
                        await loadCourses();
                    } else {
                        const data = await response.json();
                        alert(`Error: ${data.message || 'Failed to delete course'}`);
                    }
                } catch (error) {
                    console.error('Error deleting course:', error);
                    alert('Failed to delete course');
                }
            }
        }

        // Initialize charts when content loads
        document.addEventListener('DOMContentLoaded', () => {
            initializeCharts();
            // Add report type change handler
            document.getElementById('reportType').addEventListener('change', (e) => {
                const reportSelection = document.getElementById('reportSelection');
                if (e.target.value !== 'overall') {
                    reportSelection.classList.remove('d-none');
                    loadReportTargets(e.target.value);
                } else {
                    reportSelection.classList.add('d-none');
                }
            });
        });

        // Initialize with all data
        verifyAuth().then(isAuthenticated => {
            if (isAuthenticated) {
                loadDashboardStats();
                searchStudents();
                loadComplaints();
                loadCourses();
                loadAcademicProgress();
            }
        });

        // Event Listeners
        document.getElementById('searchBtn').addEventListener('click', () => {
            const query = document.getElementById('searchInput').value.trim();
            searchStudents(query);
        });

        // Search on Enter key in search input
        document.getElementById('searchInput').addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                const query = document.getElementById('searchInput').value.trim();
                searchStudents(query);
            }
        });

        document.getElementById('logoutBtn').addEventListener('click', async () => {
            try {
                await refreshCsrfToken();
                
                await fetch('/api/admin/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                // Clear all auth tokens
                localStorage.removeItem('token');
                document.cookie = 'admin_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
                
                // Redirect to login
                window.location.href = '/admin/login';
            } catch (error) {
                console.error('Error during logout:', error);
                // Even if logout API fails, clear tokens and redirect
                localStorage.removeItem('token');
                window.location.href = '/admin/login';
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>