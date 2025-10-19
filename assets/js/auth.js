document.addEventListener('DOMContentLoaded', () => {
    const getEl = (id) => document.getElementById(id);

    const panels = {
        role: getEl('role-selection-panel'),
        choice: getEl('auth-choice-panel'),
        forms: getEl('auth-forms-panel')
    };

    const forms = {
        login: getEl('login-form'),
        signup: getEl('signup-form')
    };

    const titles = {
        choice: getEl('auth-choice-title'),
        login: getEl('login-title'),
        signup: getEl('signup-title')
    };
    
    const errorDivs = {
        login: getEl('login-error'),
        signup: getEl('signup-error')
    };

    let currentPortalRole = 'Student';

    const showPanel = (panelToShow) => {
        Object.values(panels).forEach(panel => panel.classList.add('hidden'));
        panels[panelToShow].classList.remove('hidden');
    };

    const showForm = (formToShow) => {
        Object.values(forms).forEach(form => form.classList.add('hidden'));
        forms[formToShow].classList.remove('hidden');
        showPanel('forms');
    };
    
    const setTitles = (role) => {
        titles.choice.innerText = `${role} Portal`;
        titles.login.innerText = `Log In as ${role}`;
        titles.signup.innerText = `Sign Up as ${role}`;
    };

    const handleAuthResponse = async (form, action) => {
        const formData = new FormData(form);
        formData.append('action', action);
        formData.append('role', currentPortalRole);
        
        const errorDiv = errorDivs[action];
        errorDiv.classList.add('hidden');

        try {
            const response = await fetch('api/auth.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                window.location.href = 'dashboard.php';
            } else {
                errorDiv.innerText = result.message || 'An unknown error occurred.';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.innerText = 'Could not connect to the server.';
            errorDiv.classList.remove('hidden');
        }
    };

    getEl('role-student-btn').addEventListener('click', () => {
        currentPortalRole = 'Student';
        setTitles('Student');
        showPanel('choice');
    });

    getEl('role-faculty-btn').addEventListener('click', () => {
        currentPortalRole = 'Faculty';
        setTitles('Faculty');
        showPanel('choice');
    });

    getEl('show-login-btn').addEventListener('click', () => showForm('login'));
    getEl('show-signup-btn').addEventListener('click', () => showForm('signup'));

    getEl('back-to-role-btn').addEventListener('click', () => showPanel('role'));
    getEl('back-to-auth-choice-btn').addEventListener('click', () => showPanel('choice'));

    forms.login.addEventListener('submit', (e) => {
        e.preventDefault();
        handleAuthResponse(forms.login, 'login');
    });

    forms.signup.addEventListener('submit', (e) => {
        e.preventDefault();
        handleAuthResponse(forms.signup, 'signup');
    });

    showPanel('role');
});