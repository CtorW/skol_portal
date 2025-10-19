document.addEventListener('DOMContentLoaded', () => {
    const mainContentArea = document.getElementById('main-content-area');
    const navLinksContainer = document.getElementById('nav-links');
    const userModal = document.getElementById('user-modal');
    const userModalForm = userModal.querySelector('form');
    const userModalTitle = document.getElementById('modal-title');
    const logoutButton = document.getElementById('logout-btn');

    let activeView = 'dashboard';
    let allUsers = []; 
    let editingUserId = null;
    let clockInterval = null;

    const getEl = (id) => document.getElementById(id);

    const api = {
        get: (endpoint) => fetch(endpoint).then(res => res.json()),
        post: (endpoint, data) => fetch(endpoint, {
            method: 'POST',
            body: data
        }).then(res => res.json())
    };

    const renderNavLinks = () => {
        const navConfig = {
            Student: [{ id: 'dashboard', icon: 'dashboard', text: 'Dashboard' }],
            Faculty: [
                { id: 'dashboard', icon: 'dashboard', text: 'Dashboard' },
                { id: 'roster', icon: 'groups', text: 'Class Roster' },
                { id: 'usermanagement', icon: 'manage_accounts', text: 'User Management' },
            ],
        };
        const links = navConfig[USER_ROLE] || [];
        navLinksContainer.innerHTML = links.map(link => `
            <li>
                <a href="#" data-view="${link.id}" class="nav-link flex items-center gap-3 p-3 rounded-full text-gray-600 hover:bg-purple-100 hover:text-purple-600 transition-colors">
                    <span class="material-symbols-outlined">${link.icon}</span>
                    <span class="font-medium">${link.text}</span>
                </a>
            </li>
        `).join('');
    };

    const switchView = async (viewId) => {
        activeView = viewId;
        mainContentArea.innerHTML = `<div class="text-center p-8">Loading...</div>`;
        
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.toggle('active', link.dataset.view === viewId);
        });

        let data;
        switch (viewId) {
            case 'dashboard':
                data = await api.get('api/data.php?fetch=dashboard');
                if (data.success) {
                    USER_ROLE === 'Student' ? renderStudentDashboard(data) : renderFacultyDashboard(data);
                }
                break;
            case 'roster':
                data = await api.get('api/data.php?fetch=roster');
                if (data.success) renderFacultyRoster(data.roster);
                break;
            case 'usermanagement':
                data = await api.get('api/data.php?fetch=users');
                if (data.success) {
                    allUsers = data.users;
                    renderUserManagement();
                }
                break;
        }
    };

    const startClock = () => {
        if (clockInterval) clearInterval(clockInterval);
        const clockEl = getEl('live-clock');
        if(clockEl) {
             clockInterval = setInterval(() => {
                clockEl.innerText = new Date().toLocaleTimeString();
            }, 1000);
        }
    };

    const renderStudentDashboard = (data) => {
        const studentDashboardHTML = `
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-4xl font-bold">Today's Schedule</h1>
                <div class="text-xl font-mono p-3 bg-white rounded-lg shadow-md" id="live-clock">${new Date().toLocaleTimeString()}</div>
            </div>
            <div class="space-y-4" id="student-schedule-container"></div>`;
        mainContentArea.innerHTML = `<div class="view-container">${studentDashboardHTML}</div>`;
        
        const scheduleContainer = getEl('student-schedule-container');
        scheduleContainer.innerHTML = data.schedule.map(subject => {
            const time = `${subject.start_time.substring(0, 5)} - ${subject.end_time.substring(0, 5)}`;
            let statusHTML;
            if (subject.status) {
                const color = subject.status === 'Present' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800';
                statusHTML = `<span class="px-3 py-1 font-semibold rounded-full ${color}">${subject.status}</span>`;
            } else {
                statusHTML = `<button data-subject-id="${subject.id}" class="mark-attendance-btn px-6 py-2 bg-gradient-to-r from-purple-500 to-indigo-500 text-white font-semibold rounded-full btn-ripple shadow-md">[ In ]</button>`;
            }
            return `
                <div class="p-4 bg-white rounded-2xl flex justify-between items-center shadow-sm">
                    <div>
                        <p class="font-bold text-lg">${subject.name}</p>
                        <p class="text-sm text-gray-600">${time}</p>
                    </div>
                    <div>${statusHTML}</div>
                </div>`;
        }).join('');
        startClock();
    };

    const renderFacultyDashboard = (data) => {
        const { stats, notifications } = data;
        const facultyDashboardHTML = `
            <h1 class="text-4xl font-bold mb-8">Attendance Dashboard</h1>
            <div id="faculty-stats-container" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="p-4 rounded-xl bg-white shadow-md"><h4>Total Students</h4><p class="text-3xl font-bold">${stats.total}</p></div>
                <div class="p-4 rounded-xl bg-green-100 shadow-md"><h4>Present</h4><p class="text-3xl font-bold">${stats.present}</p></div>
                <div class="p-4 rounded-xl bg-yellow-100 shadow-md"><h4>Late</h4><p class="text-3xl font-bold">${stats.late}</p></div>
                <div class="p-4 rounded-xl bg-red-100 shadow-md"><h4>Absent</h4><p class="text-3xl font-bold">${stats.absent}</p></div>
            </div>
            <h2 class="text-2xl font-bold mt-8 mb-4">Notifications</h2>
            <div id="notifications-container" class="space-y-3">
                ${notifications.length > 0 ? notifications.map(n => `
                    <div class="p-3 bg-red-100 text-red-800 rounded-lg flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined">campaign</span> ${n.message}
                    </div>`).join('') : `<p class="text-gray-500 italic">No new notifications.</p>`}
            </div>`;
        mainContentArea.innerHTML = `<div class="view-container">${facultyDashboardHTML}</div>`;
    };

    const renderFacultyRoster = (roster) => {
        const rosterHTML = `
            <h1 class="text-4xl font-bold mb-8">Class Roster: Web Development</h1>
            <div class="bg-white rounded-2xl p-4 overflow-x-auto shadow-md">
                <table class="w-full text-left">
                    <thead><tr class="border-b-2"><th class="p-4">Student Name</th><th class="p-4">Email</th><th class="p-4">Attendance Status</th></tr></thead>
                    <tbody>
                        ${roster.map(student => {
                            const status = student.status || 'Upcoming';
                            const colorMap = { Present: 'text-green-600', Late: 'text-yellow-600', Absent: 'text-red-600', Upcoming: 'text-gray-500' };
                            return `<tr>
                                <td class="p-4">${student.name}</td>
                                <td class="p-4">${student.email}</td>
                                <td class="p-4 font-bold ${colorMap[status]}">${status}</td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            </div>`;
        mainContentArea.innerHTML = `<div class="view-container">${rosterHTML}</div>`;
    };

    const renderUserManagement = (tab = 'Students') => {
        const userManagementHTML = `
            <h1 class="text-4xl font-bold mb-8">User Management</h1>
            <div class="flex border-b mb-4">
                <button id="tab-students" data-tab="Students" class="tab-button py-2 px-4 border-b-2">Students</button>
                <button id="tab-faculty" data-tab="Faculty" class="tab-button py-2 px-4 border-b-2">Faculty</button>
            </div>
            <div id="user-management-content"></div>`;
        mainContentArea.innerHTML = `<div class="view-container">${userManagementHTML}</div>`;
        renderUserTable(tab);
    };

    const renderUserTable = (roleToShow) => {
        getEl('tab-students').classList.toggle('active', roleToShow === 'Students');
        getEl('tab-faculty').classList.toggle('active', roleToShow === 'Faculty');

        const filteredUsers = allUsers.filter(user => user.role === roleToShow);
        const tableContent = filteredUsers.map(u => `
            <tr data-user-id="${u.id}">
                <td class="p-4">${u.name}</td>
                <td class="p-4">${u.email}</td>
                <td class="p-4 text-center">
                    <button data-action="edit" class="p-2 rounded-full hover:bg-gray-200"><span class="material-symbols-outlined">edit</span></button>
                    <button data-action="delete" class="p-2 rounded-full hover:bg-gray-200"><span class="material-symbols-outlined">delete</span></button>
                </td>
            </tr>`
        ).join('');

        getEl('user-management-content').innerHTML = `
            <div class="bg-white rounded-2xl p-4 overflow-x-auto shadow-md">
                <table class="w-full text-left">
                    <thead><tr class="border-b-2"><th class="p-4">Name</th><th class="p-4">Email</th><th class="p-4 text-center">Actions</th></tr></thead>
                    <tbody>${tableContent}</tbody>
                </table>
            </div>`;
    };

    const openUserModal = (userId = null) => {
        editingUserId = userId;
        userModalForm.reset();
        if (userId) {
            const user = allUsers.find(u => u.id == userId);
            userModalTitle.innerText = "Edit User";
            getEl('modal-name').value = user.name;
            getEl('modal-email').value = user.email;
            getEl('modal-role').value = user.role;
        } else {
            userModalTitle.innerText = "Add New User";
        }
        userModal.classList.add('active');
    };

    const closeUserModal = () => userModal.classList.remove('active');

    const handleSaveUser = async (event) => {
        event.preventDefault();
        const formData = new FormData(userModalForm);
        formData.append('action', 'update_user');
        if (editingUserId) {
            formData.append('userId', editingUserId);
        }
        
        const result = await api.post('api/action.php', formData);
        if (result.success) {
            closeUserModal();
            switchView('usermanagement');
        } else {
            alert(`Error: ${result.message}`);
        }
    };
    
    const handleDeleteUser = async (userId, userName) => {
        if (!confirm(`Are you sure you want to delete ${userName}? This action cannot be undone.`)) return;

        const formData = new FormData();
        formData.append('action', 'delete_user');
        formData.append('userId', userId);
        
        const result = await api.post('api/action.php', formData);
        if (result.success) {
            switchView('usermanagement');
        } else {
            alert(`Error: ${result.message}`);
        }
    };

    const handleMarkAttendance = async (subjectId) => {
        const formData = new FormData();
        formData.append('action', 'mark_attendance');
        formData.append('subjectId', subjectId);
        
        const result = await api.post('api/action.php', formData);
        alert(result.message);
        if(result.success) {
            switchView('dashboard');
        }
    };

    const handleLogout = async () => {
        const result = await api.post('api/auth.php', new FormData(document.getElementById('logout-form')));
        if(result.success) {
            window.location.href = 'index.php';
        }
    };

    mainContentArea.addEventListener('click', (e) => {
        const button = e.target.closest('button');
        if (!button) return;

        if (button.classList.contains('mark-attendance-btn')) {
            handleMarkAttendance(button.dataset.subjectId);
            return;
        }
        
        if (button.classList.contains('tab-button')) {
            renderUserTable(button.dataset.tab);
            return;
        }

        if (button.dataset.action === 'edit' || button.dataset.action === 'delete') {
            const row = e.target.closest('tr');
            const userId = row.dataset.userId;
            const userName = row.cells[0].innerText;
            if(button.dataset.action === 'edit') {
                openUserModal(userId);
            } else {
                handleDeleteUser(userId, userName);
            }
        }
    });

    navLinksContainer.addEventListener('click', (e) => {
        e.preventDefault();
        const link = e.target.closest('.nav-link');
        if (link && link.dataset.view) {
            switchView(link.dataset.view);
        }
    });

    logoutButton.addEventListener('click', handleLogout);
    userModalForm.addEventListener('submit', handleSaveUser);
    userModal.addEventListener('click', (e) => {
        if (e.target === userModal || e.target.closest('button[type="button"]')) {
            closeUserModal();
        }
    });

    const init = () => {
        renderNavLinks();
        switchView('dashboard');
    };

    init();
});