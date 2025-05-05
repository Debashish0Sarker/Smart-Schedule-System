<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard - Smart Schedule System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Smart Schedule System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#dashboard">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#courses">My Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#assignments">Assignments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#progress">Progress Reports</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="dropdown me-3">
                        <button class="btn btn-light position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                                0
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" id="notificationsList">
                            <li><span class="dropdown-item">No new notifications</span></li>
                        </ul>
                    </div>
                    <span id="studentName" class="text-light me-3"></span>
                    <button id="logoutBtn" class="btn btn-light">Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Dashboard Section -->
        <div id="dashboard-section">
            <h2>Welcome to your dashboard</h2>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">Upcoming Assignments</div>
                        <div class="card-body">
                            <ul class="list-group" id="upcomingAssignments">
                                <li class="list-group-item">Loading assignments...</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">Course Progress</div>
                        <div class="card-body" id="courseProgressSummary">
                            Loading progress data...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Section -->
        <div id="courses-section" class="d-none">
            <h2>My Courses</h2>
            <div class="row mt-3">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Enrolled Courses</span>
                            <button class="btn btn-sm btn-primary" id="enrollCourseBtn">Enroll in Course</button>
                        </div>
                        <div class="card-body">
                            <div id="enrollmentFormContainer" class="mb-3 d-none">
                                <select class="form-select mb-2" id="courseSelect">
                                    <option value="">Select a course to enroll</option>
                                </select>
                                <button id="submitEnrollment" class="btn btn-sm btn-success">Confirm Enrollment</button>
                                <button id="cancelEnrollment" class="btn btn-sm btn-secondary">Cancel</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Course Name</th>
                                            <th>Code</th>
                                            <th>Grade</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="coursesTable">
                                        <tr>
                                            <td colspan="5" class="text-center">Loading courses...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments Section -->
        <div id="assignments-section" class="d-none">
            <h2>My Assignments</h2>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <select class="form-select w-auto" id="assignmentCourseFilter">
                    <option value="all">All Courses</option>
                </select>
                <select class="form-select w-auto" id="assignmentStatusFilter">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Course</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="assignmentsTable">
                        <tr>
                            <td colspan="5" class="text-center">Loading assignments...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Progress Reports Section -->
        <div id="progress-section" class="d-none">
            <h2>Progress Reports</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-header">Overall Progress</div>
                        <div class="card-body" id="overallProgress">
                            Loading progress data...
                        </div>
                    </div>
                </div>
                <div class="col-md-8 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Progress Reports</span>
                                <select class="form-select form-select-sm w-auto" id="reportTypeFilter">
                                    <option value="daily">Daily Reports</option>
                                    <option value="weekly">Weekly Reports</option>
                                    <option value="semester">Semester Reports</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="progressReportsList">
                                <p class="text-center">Loading progress reports...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check if logged in
        const token = localStorage.getItem('student_token');
        const studentData = JSON.parse(localStorage.getItem('student_data') || '{}');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        if (!token) {
            window.location.href = '/student/login';
        }

        // Display student name
        document.getElementById('studentName').textContent = studentData.name || 'Student';

        // Navigation handling
        const sections = ['dashboard', 'courses', 'assignments', 'progress'];
        const navLinks = document.querySelectorAll('.nav-link');

        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                
                // Update active nav
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                // Show target section
                sections.forEach(section => {
                    const sectionElement = document.getElementById(`${section}-section`);
                    if (section === targetId) {
                        sectionElement.classList.remove('d-none');
                    } else {
                        sectionElement.classList.add('d-none');
                    }
                });
                
                if (targetId === 'courses') loadCourses();
                if (targetId === 'assignments') loadAssignments();
                if (targetId === 'progress') loadProgressReports();
            });
        });

        // API Calls
        async function fetchWithAuth(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            };
            
            return fetch(url, { ...defaultOptions, ...options });
        }

        // Load initial dashboard data
        async function loadDashboard() {
            try {
                // Load upcoming assignments
                const assignmentsResponse = await fetchWithAuth('/api/students/assignments/upcoming');
                const assignments = await assignmentsResponse.json();
                
                displayUpcomingAssignments(assignments);
                
                // Load course progress
                const coursesResponse = await fetchWithAuth('/api/students/courses');
                const courses = await coursesResponse.json();
                
                displayCourseProgressSummary(courses);
                
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        function displayUpcomingAssignments(assignments) {
            const container = document.getElementById('upcomingAssignments');
            
            if (assignments.length === 0) {
                container.innerHTML = '<li class="list-group-item">No upcoming assignments</li>';
                return;
            }
            
            container.innerHTML = assignments.map(assignment => `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${assignment.title}</strong> - ${assignment.course.name}
                        <br>
                        <small>Due: ${new Date(assignment.due_date).toLocaleDateString()}</small>
                    </div>
                    <span class="badge bg-${assignment.status === 'pending' ? 'warning' : 
                                            assignment.status === 'completed' ? 'success' : 'danger'}">
                        ${assignment.status}
                    </span>
                </li>
            `).join('');
        }

        function displayCourseProgressSummary(courses) {
            const container = document.getElementById('courseProgressSummary');
            
            if (courses.length === 0) {
                container.innerHTML = '<p>You are not enrolled in any courses</p>';
                return;
            }
            
            container.innerHTML = courses.map(course => `
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>${course.name}</strong>
                        <span>${course.pivot.grade ? course.pivot.grade + '%' : 'No grade yet'}</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" 
                             style="width: ${course.pivot.progress || 0}%" 
                             aria-valuenow="${course.pivot.progress || 0}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            ${course.pivot.progress || 0}%
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Logout functionality
        document.getElementById('logoutBtn').addEventListener('click', async function() {
            try {
                await fetchWithAuth('/api/students/logout', { method: 'POST' });
            } catch (error) {
                console.error('Error during logout:', error);
            } finally {
                localStorage.removeItem('student_token');
                localStorage.removeItem('student_data');
                window.location.href = '/student/login';
            }
        });

        // Load initial data
        loadDashboard();

        // Course loading and management functions
        async function loadCourses() {
            try {
                const coursesResponse = await fetchWithAuth('/api/students/courses');
                const courses = await coursesResponse.json();
                
                displayCourses(courses);
                
                // Also load available courses for enrollment
                const allCoursesResponse = await fetchWithAuth('/api/courses');
                const allCourses = await allCoursesResponse.json();
                
                displayAvailableCourses(allCourses, courses);
                
            } catch (error) {
                console.error('Error loading courses:', error);
            }
        }

        function displayCourses(courses) {
            const container = document.getElementById('coursesTable');
            
            if (courses.length === 0) {
                container.innerHTML = '<tr><td colspan="5" class="text-center">You are not enrolled in any courses</td></tr>';
                return;
            }
            
            container.innerHTML = courses.map(course => `
                <tr>
                    <td>${course.name}</td>
                    <td>${course.code}</td>
                    <td>${course.pivot.grade ? course.pivot.grade + '%' : 'No grade yet'}</td>
                    <td><span class="badge bg-${course.pivot.status === 'enrolled' ? 'success' : 'secondary'}">${course.pivot.status}</span></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewCourseDetails(${course.id})">Details</button>
                    </td>
                </tr>
            `).join('');
        }

        function displayAvailableCourses(allCourses, enrolledCourses) {
            const select = document.getElementById('courseSelect');
            select.innerHTML = '<option value="">Select a course to enroll</option>';
            
            const enrolledIds = enrolledCourses.map(c => c.id);
            
            // Filter out already enrolled courses
            const availableCourses = allCourses.filter(course => !enrolledIds.includes(course.id));
            
            if (availableCourses.length === 0) {
                select.innerHTML += '<option disabled>No available courses</option>';
                return;
            }
            
            availableCourses.forEach(course => {
                select.innerHTML += `<option value="${course.id}">${course.name} (${course.code})</option>`;
            });
        }

        // Enrollment form toggle
        document.getElementById('enrollCourseBtn').addEventListener('click', function() {
            const formContainer = document.getElementById('enrollmentFormContainer');
            formContainer.classList.toggle('d-none');
        });
        
        document.getElementById('cancelEnrollment').addEventListener('click', function() {
            document.getElementById('enrollmentFormContainer').classList.add('d-none');
        });
        
        document.getElementById('submitEnrollment').addEventListener('click', async function() {
            const courseId = document.getElementById('courseSelect').value;
            
            if (!courseId) {
                alert('Please select a course');
                return;
            }
            
            try {
                const response = await fetchWithAuth(`/api/students/courses/${courseId}/enroll`, {
                    method: 'POST'
                });
                
                if (response.ok) {
                    alert('Successfully enrolled in course');
                    document.getElementById('enrollmentFormContainer').classList.add('d-none');
                    loadCourses(); // Reload the courses
                } else {
                    const data = await response.json();
                    alert(`Error: ${data.message || 'Something went wrong'}`);
                }
            } catch (error) {
                console.error('Error enrolling in course:', error);
                alert('An error occurred while enrolling in the course');
            }
        });

        // Assignment loading and management
        async function loadAssignments() {
            try {
                // First, load courses for the filter dropdown
                const coursesResponse = await fetchWithAuth('/api/students/courses');
                const courses = await coursesResponse.json();
                
                populateCourseFilter(courses);
                
                // Then load the assignments
                const courseFilter = document.getElementById('assignmentCourseFilter').value;
                const statusFilter = document.getElementById('assignmentStatusFilter').value;
                
                let url = '/api/students/assignments';
                if (courseFilter !== 'all') {
                    url += `/course/${courseFilter}`;
                }
                if (statusFilter !== 'all') {
                    url += `?status=${statusFilter}`;
                }
                
                const assignmentsResponse = await fetchWithAuth(url);
                const assignments = await assignmentsResponse.json();
                
                displayAssignments(assignments);
                
            } catch (error) {
                console.error('Error loading assignments:', error);
            }
        }

        function populateCourseFilter(courses) {
            const select = document.getElementById('assignmentCourseFilter');
            select.innerHTML = '<option value="all">All Courses</option>';
            
            courses.forEach(course => {
                select.innerHTML += `<option value="${course.id}">${course.name}</option>`;
            });
        }

        function displayAssignments(assignments) {
            const container = document.getElementById('assignmentsTable');
            
            if (assignments.length === 0) {
                container.innerHTML = '<tr><td colspan="5" class="text-center">No assignments found</td></tr>';
                return;
            }
            
            container.innerHTML = assignments.map(assignment => `
                <tr>
                    <td>${assignment.title}</td>
                    <td>${assignment.course.name}</td>
                    <td>${new Date(assignment.due_date).toLocaleDateString()}</td>
                    <td>
                        <span class="badge bg-${
                            assignment.status === 'pending' ? 'warning' : 
                            assignment.status === 'completed' ? 'success' : 'danger'
                        }">${assignment.status}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="viewAssignmentDetails(${assignment.id})">View</button>
                        ${assignment.status === 'pending' ? 
                        `<button class="btn btn-sm btn-success" onclick="markAsComplete(${assignment.id})">Complete</button>` : ''}
                    </td>
                </tr>
            `).join('');
        }

        // Filter event listeners
        document.getElementById('assignmentCourseFilter').addEventListener('change', loadAssignments);
        document.getElementById('assignmentStatusFilter').addEventListener('change', loadAssignments);

        // Progress report loading and display
        async function loadProgressReports() {
            try {
                // Load overall progress
                const progressResponse = await fetchWithAuth('/api/students/progress/summary');
                const progressData = await progressResponse.json();
                
                displayOverallProgress(progressData);
                
                // Load specific reports based on filter
                const reportType = document.getElementById('reportTypeFilter').value;
                const reportsResponse = await fetchWithAuth(`/api/students/progress/reports/${reportType}`);
                const reports = await reportsResponse.json();
                
                displayProgressReports(reports);
                
            } catch (error) {
                console.error('Error loading progress reports:', error);
                // Show dummy data since the API might not be implemented yet
                displayDummyProgressData();
            }
        }

        function displayOverallProgress(data) {
            const container = document.getElementById('overallProgress');
            
            // If we have actual data, display it
            if (data) {
                container.innerHTML = `
                    <div class="mb-3">
                        <h5>Overall Course Completion</h5>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" 
                                style="width: ${data.overall_completion || 0}%" 
                                aria-valuenow="${data.overall_completion || 0}" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                ${data.overall_completion || 0}%
                            </div>
                        </div>
                    </div>
                    <div>
                        <h5>Assignment Completion</h5>
                        <p>Total Assignments: ${data.total_assignments || 0}</p>
                        <p>Completed: ${data.completed_assignments || 0}</p>
                        <p>Pending: ${data.pending_assignments || 0}</p>
                        <p>Overdue: ${data.overdue_assignments || 0}</p>
                    </div>
                `;
            } else {
                // Display dummy data if API not ready
                displayDummyProgressData();
            }
        }
        
        function displayProgressReports(reports) {
            const container = document.getElementById('progressReportsList');
            
            if (!reports || reports.length === 0) {
                container.innerHTML = '<p class="text-center">No progress reports available</p>';
                return;
            }
            
            container.innerHTML = reports.map(report => `
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <span>${report.course.name}</span>
                        <span>${new Date(report.report_date).toLocaleDateString()}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Completion Rate:</strong> ${report.completion_rate}%
                        </div>
                        <div class="mb-2">
                            <strong>Current Grade:</strong> ${report.current_grade || 'Not graded'}
                        </div>
                        <div class="mb-2">
                            <strong>Assignments:</strong> ${report.completed_assignments} completed, 
                            ${report.pending_assignments} pending
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        // Temporary function in case the API is not implemented
        function displayDummyProgressData() {
            const overallContainer = document.getElementById('overallProgress');
            overallContainer.innerHTML = `
                <div class="mb-3">
                    <h5>Overall Course Completion</h5>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" 
                            style="width: 65%" 
                            aria-valuenow="65" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                            65%
                        </div>
                    </div>
                </div>
                <div>
                    <h5>Assignment Completion</h5>
                    <p>Total Assignments: 10</p>
                    <p>Completed: 6</p>
                    <p>Pending: 3</p>
                    <p>Overdue: 1</p>
                </div>
            `;
            
            const reportsContainer = document.getElementById('progressReportsList');
            reportsContainer.innerHTML = `
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <span>Introduction to Computer Science</span>
                        <span>${new Date().toLocaleDateString()}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Completion Rate:</strong> 75%
                        </div>
                        <div class="mb-2">
                            <strong>Current Grade:</strong> 88%
                        </div>
                        <div class="mb-2">
                            <strong>Assignments:</strong> 3 completed, 1 pending
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <span>Data Structures and Algorithms</span>
                        <span>${new Date().toLocaleDateString()}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Completion Rate:</strong> 60%
                        </div>
                        <div class="mb-2">
                            <strong>Current Grade:</strong> 92%
                        </div>
                        <div class="mb-2">
                            <strong>Assignments:</strong> 3 completed, 2 pending
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Event listener for report type filter
        document.getElementById('reportTypeFilter').addEventListener('change', loadProgressReports);
    </script>
</body>
</html>