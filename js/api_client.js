/**
 * JavaScript API Client for Attendance System
 * Provides helper functions to call backend PHP APIs
 */

const API = {
    BASE_URL: window.location.href.replace(/(\/[^\/]*)?$/, '/'),

    // ==================== AUTHENTICATION ====================
    
    async login(username, password) {
        return this._post('api_auth.php?action=login', {
            username,
            password
        });
    },

    async logout() {
        return this._post('api_auth.php?action=logout', {});
    },

    async getCurrentUser() {
        return this._get('api_auth.php?action=current-user');
    },

    async changePassword(oldPassword, newPassword) {
        return this._post('api_auth.php?action=change-password', {
            old_password: oldPassword,
            new_password: newPassword
        });
    },

    // ==================== STUDENTS ====================
    
    async getAllStudents() {
        return this._get('api_students.php?action=all');
    },

    async getStudent(id) {
        return this._get(`api_students.php?action=get&id=${id}`);
    },

    async getStudentsBySection(section, semester = null) {
        let url = `api_students.php?action=section&section=${section}`;
        if (semester) url += `&semester=${semester}`;
        return this._get(url);
    },

    async createStudent(studentData) {
        return this._post('api_students.php?action=create', studentData);
    },

    async updateStudent(id, studentData) {
        return this._put(`api_students.php?action=update&id=${id}`, studentData);
    },

    async deleteStudent(id) {
        return this._delete(`api_students.php?action=delete&id=${id}`);
    },

    // ==================== ATTENDANCE ====================
    
    async createAttendanceSession(data) {
        return this._post('api_attendance.php?action=create-session', data);
    },

    async markAttendance(sessionId, studentId, status, remarks = null) {
        return this._post('api_attendance.php?action=mark-attendance', {
            session_id: sessionId,
            student_id: studentId,
            attendance_status: status,
            remarks: remarks
        });
    },

    async getAttendanceRecord(sessionId) {
        return this._get(`api_attendance.php?action=record&session_id=${sessionId}`);
    },

    async getAttendanceReport(studentId = null, subjectId = null, startDate = null, endDate = null) {
        let url = 'api_attendance.php?action=report';
        if (studentId) url += `&student_id=${studentId}`;
        if (subjectId) url += `&subject_id=${subjectId}`;
        if (startDate) url += `&start_date=${startDate}`;
        if (endDate) url += `&end_date=${endDate}`;
        return this._get(url);
    },

    async getSessionsForFaculty(facultyId) {
        return this._get(`api_attendance.php?action=sessions&faculty_id=${facultyId}`);
    },

    // ==================== FACULTY ====================
    
    async getAllFaculty() {
        return this._get('api_faculty.php?action=all');
    },

    async getFaculty(id) {
        return this._get(`api_faculty.php?action=get&id=${id}`);
    },

    async getFacultyByDepartment(deptId) {
        return this._get(`api_faculty.php?action=department&dept_id=${deptId}`);
    },

    async createFaculty(facultyData) {
        return this._post('api_faculty.php?action=create', facultyData);
    },

    async updateFaculty(id, facultyData) {
        return this._put(`api_faculty.php?action=update&id=${id}`, facultyData);
    },

    async deleteFaculty(id) {
        return this._delete(`api_faculty.php?action=delete&id=${id}`);
    },

    // ==================== ADMIN ====================
    
    async getDashboardStats() {
        return this._get('api_admin.php?action=dashboard-stats');
    },

    async getAllUsers(role = null) {
        let url = 'api_admin.php?action=users';
        if (role) url += `&role=${role}`;
        return this._get(url);
    },

    async createUser(userData) {
        return this._post('api_admin.php?action=create-user', userData);
    },

    async deactivateUser(id) {
        return this._put(`api_admin.php?action=deactivate-user&id=${id}`, {});
    },

    async getAllDepartments() {
        return this._get('api_admin.php?action=departments');
    },

    async getAllCourses() {
        return this._get('api_admin.php?action=courses');
    },

    async getAllSubjects() {
        return this._get('api_admin.php?action=subjects');
    },

    async getAttendanceReports(type = 'overall', startDate = null, endDate = null) {
        let url = `api_admin.php?action=attendance-reports&type=${type}`;
        if (startDate) url += `&start_date=${startDate}`;
        if (endDate) url += `&end_date=${endDate}`;
        return this._get(url);
    },

    // ==================== HTTP METHODS ====================
    
    async _get(endpoint) {
        try {
            const response = await fetch(this.BASE_URL + endpoint);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    async _post(endpoint, body) {
        return this._request(endpoint, 'POST', body);
    },

    async _put(endpoint, body) {
        return this._request(endpoint, 'PUT', body);
    },

    async _delete(endpoint) {
        return this._request(endpoint, 'DELETE', null);
    },

    async _request(endpoint, method, body) {
        try {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json'
                }
            };

            if (body) {
                options.body = JSON.stringify(body);
            }

            const response = await fetch(this.BASE_URL + endpoint, options);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
};

/**
 * Database Helper Functions for UI Integration
 */

// Store user session data
async function syncUserSession() {
    try {
        const result = await API.getCurrentUser();
        if (result.status === 'success') {
            localStorage.setItem('currentUser', JSON.stringify(result.user));
            return result.user;
        }
    } catch (error) {
        console.error('Session sync error:', error);
        return null;
    }
}

// Get current user from session
function getCurrentUser() {
    const user = localStorage.getItem('currentUser');
    return user ? JSON.parse(user) : null;
}

// Check if user is authenticated
function isAuthenticated() {
    return localStorage.getItem('currentUser') !== null;
}

// Perform login
async function performLogin(username, password) {
    try {
        const result = await API.login(username, password);
        if (result.status === 'success') {
            localStorage.setItem('currentUser', JSON.stringify(result.user));
            return true;
        }
        return false;
    } catch (error) {
        console.error('Login error:', error);
        return false;
    }
}

// Perform logout
async function performLogout() {
    try {
        await API.logout();
        localStorage.removeItem('currentUser');
        return true;
    } catch (error) {
        console.error('Logout error:', error);
        return false;
    }
}

// Load and display students
async function loadAndDisplayStudents(containerId, section = null, semester = null) {
    try {
        let result;
        if (section) {
            result = await API.getStudentsBySection(section, semester);
        } else {
            result = await API.getAllStudents();
        }

        if (result.status === 'success') {
            displayStudents(result.data, containerId);
            return result.data;
        }
    } catch (error) {
        console.error('Error loading students:', error);
    }
}

// Display students in table
function displayStudents(students, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let html = '<table class="table table-striped"><thead><tr>';
    html += '<th>Roll No</th><th>Name</th><th>Section</th><th>Semester</th><th>Email</th></tr></thead><tbody>';

    students.forEach(student => {
        html += `<tr>
                    <td>${student.roll_no || ''}</td>
                    <td>${student.name || ''}</td>
                    <td>${student.section || ''}</td>
                    <td>${student.semester || ''}</td>
                    <td>${student.email || ''}</td>
                </tr>`;
    });

    html += '</tbody></table>';
    container.innerHTML = html;
}
