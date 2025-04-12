document.addEventListener("DOMContentLoaded", function() {

    loadProfileData();

    const themeToggle = document.getElementById("theme-toggle");
    const themeIcon = themeToggle.querySelector("i");
    const htmlElement = document.documentElement;

    const storedTheme = localStorage.getItem("theme") || "dark";
    htmlElement.setAttribute("data-theme", storedTheme);
    updateThemeIcon(storedTheme);

    themeToggle.addEventListener("click", function() {
        const currentTheme = htmlElement.getAttribute("data-theme");
        const newTheme = currentTheme === "dark" ? "light" : "dark";

        htmlElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);
        updateThemeIcon(newTheme);

        showToast(`Theme changed to ${newTheme} mode`, "info");
    });

    function updateThemeIcon(theme) {
        themeIcon.className = theme === "dark" ? "fas fa-sun" : "fas fa-moon";
    }

    const hamburger = document.getElementById("hamburger-menu");
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.querySelector('.main-content');

    hamburger.addEventListener("click", function() {
        sidebar.classList.toggle("active");

        const spans = this.querySelectorAll("span");
        if (sidebar.classList.contains("active")) {
            spans[0].style.transform = "rotate(45deg) translate(5px, 5px)";
            spans[1].style.opacity = "0";
            spans[2].style.transform = "rotate(-45deg) translate(5px, -5px)";
        } else {
            spans[0].style.transform = "none";
            spans[1].style.opacity = "1";
            spans[2].style.transform = "none";
        }
    });

    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnHamburger = hamburger.contains(event.target);

        if (!isClickInsideSidebar && !isClickOnHamburger && sidebar.classList.contains('active') && window.innerWidth <= 768) {
            sidebar.classList.remove('active');

            const spans = hamburger.querySelectorAll('span');
            spans[0].style.transform = 'none';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'none';
        }
    });

    const modal = document.getElementById("profileModal");
    const openModalBtn = document.getElementById("open-profile-modal");
    const closeBtn = document.querySelector(".modal-close");

    openModalBtn.addEventListener("click", () => {
        modal.style.display = "block";
    });

    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

    const liveTime = document.getElementById("live-time");

    function updateClock() {
        const now = new Date();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        liveTime.textContent = `${hours}:${minutes}:${seconds}`;
    }

    updateClock();
    setInterval(updateClock, 1000);

    const imageUpload = document.getElementById("imageUpload");
    const profileImg = document.getElementById("profileImg");

    if (imageUpload && profileImg) {
        imageUpload.addEventListener("change", function() {
            const file = this.files[0];
            if (file) {

                if (!file.type.startsWith('image/')) {
                    showToast("Please select an image file", "error");
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    showToast("Image size should be less than 2MB", "error");
                    return;
                }

                const reader = new FileReader();

                reader.onload = function(e) {
                    try {
                        const imageData = e.target.result;

                        profileImg.src = imageData;

                        localStorage.setItem("profileImage", imageData);
                        showToast("Profile image updated", "success");
                    } catch (error) {
                        console.error("Error processing image:", error);
                        showToast("Failed to process image", "error");
                    }
                };

                reader.onerror = function() {
                    showToast("Error reading file", "error");
                };

                reader.readAsDataURL(file);
            }
        });
    }

    const profileForm = document.getElementById("profileForm");
    const usernameInput = document.getElementById("username");
    const emailInput = document.getElementById("email");
    const displayUsername = document.getElementById("display-username");

    if (profileForm && usernameInput && emailInput) {
        profileForm.addEventListener("submit", function(e) {
            e.preventDefault();

            try {
                if (!usernameInput.value.trim()) {
                    showToast("Username cannot be empty", "error");
                    return;
                }

                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailInput.value.trim())) {
                    showToast("Please enter a valid email", "error");
                    return;
                }

                // Create form data for the API request
                const formData = new FormData();
                formData.append('fullname', usernameInput.value.trim());
                formData.append('email', emailInput.value.trim());

                // Show loading toast
                showToast("Updating profile...", "info");

                // Make API request to update profile
                fetch('../backend/auth/profile/update_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI with the new data
                        if (displayUsername) {
                            displayUsername.textContent = data.user.fullname;
                        }

                        // Also save to localStorage for other purposes
                        const profileData = {
                            username: data.user.fullname,
                            email: data.user.email
                        };
                        localStorage.setItem("profileData", JSON.stringify(profileData));

                        showToast("Profile updated successfully", "success");
                        modal.style.display = "none";

                        // Reload the page if email was changed
                        if (emailInput.value.trim() !== emailInput.defaultValue) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    } else {
                        showToast(data.message || "Failed to update profile", "error");
                    }
                })
                .catch(error => {
                    console.error("Error updating profile:", error);
                    showToast("Failed to update profile. Please try again.", "error");
                });
            } catch (error) {
                console.error("Error preparing profile update:", error);
                showToast("Failed to save profile data", "error");
            }
        });
    }

    function loadProfileData() {
        try {
            const profileImg = document.getElementById("profileImg");
            const usernameInput = document.getElementById("username");
            const emailInput = document.getElementById("email");
            const displayUsername = document.getElementById("display-username");

            // Load profile image from localStorage
            if (profileImg) {
                const savedImage = localStorage.getItem("profileImage");
                if (savedImage) {
                    profileImg.src = savedImage;

                    profileImg.onload = function() {
                        console.log("Profile image loaded successfully");
                    };
                    profileImg.onerror = function() {
                        console.error("Failed to load profile image from localStorage");
                        localStorage.removeItem("profileImage"); 
                        profileImg.src = "https://via.placeholder.com/150"; 
                    };
                }
            }

            // First try to use the server-side data (which is already loaded in the HTML)
            // This is for fullname and email which are already rendered by PHP
            
            // Then load any saved data from localStorage for additional profile data
            const savedProfileData = localStorage.getItem("profileData");
            if (savedProfileData) {
                const profileData = JSON.parse(savedProfileData);
                
                // Don't override the server data for these fields unless they're empty
                if (usernameInput && !usernameInput.value && profileData.username) {
                    usernameInput.value = profileData.username;
                }

                if (emailInput && !emailInput.value && profileData.email) {
                    emailInput.value = profileData.email;
                }

                if (displayUsername && !displayUsername.textContent.trim() && profileData.username) {
                    displayUsername.textContent = profileData.username;
                }
            }
        } catch (error) {
            console.error("Error loading profile data:", error);
        }
    }

    const chartCanvas = document.getElementById('activityChart');
    if (chartCanvas) {
        const ctx = chartCanvas.getContext('2d');
        const activityChart = new Chart(ctx, {

            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                        label: 'System Activity',
                        data: [65, 59, 80, 81, 56, 55, 72],
                        fill: true,
                        backgroundColor: 'rgba(124, 58, 237, 0.2)',
                        borderColor: 'rgba(124, 58, 237, 1)',
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(124, 58, 237, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(124, 58, 237, 1)'
                    },
                    {
                        label: 'Security Score',
                        data: [28, 48, 40, 45, 76, 65, 50],
                        fill: true,
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(59, 130, 246, 1)'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            color: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-text')
                        }
                    },
                    tooltip: {
                        backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-surface'),
                        titleColor: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-primary'),
                        bodyColor: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-text'),
                        borderColor: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-border'),
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(107, 114, 128, 0.1)'
                        },
                        ticks: {
                            color: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-text-secondary')
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-text-secondary')
                        }
                    }
                }
            }
        });

        const timeRangeSelector = document.getElementById('time-range');
        if (timeRangeSelector) {
            timeRangeSelector.addEventListener('change', function(e) {
                const range = e.target.value;
                let labels, data1, data2;

                switch (range) {
                    case 'week':
                        labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                        data1 = [65, 59, 80, 81, 56, 55, 72];
                        data2 = [28, 48, 40, 45, 76, 65, 50];
                        break;
                    case 'month':
                        labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                        data1 = [70, 60, 90, 75];
                        data2 = [40, 60, 50, 80];
                        break;
                    case 'year':
                        labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        data1 = [65, 59, 80, 81, 56, 55, 40, 45, 60, 70, 85, 90];
                        data2 = [30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85];
                        break;
                }

                activityChart.data.labels = labels;
                activityChart.data.datasets[0].data = data1;
                activityChart.data.datasets[1].data = data2;
                activityChart.update();
            });
        }
    }

    const openNotes = document.getElementById('open-notes');
    const notesWidget = document.getElementById('notes-widget');

    if (openNotes && notesWidget) {
        const notesList = document.getElementById('notes-list');
        const noteForm = document.getElementById('note-form');
        const addNoteBtn = document.getElementById('add-note-btn');
        const cancelNoteBtn = document.getElementById('cancel-note');
        const saveNoteBtn = document.getElementById('save-note');
        const noteText = document.getElementById('note-text');

        function loadNotes() {
            if (!notesList) return;

            const savedNotes = JSON.parse(localStorage.getItem('userNotes') || '[]');
            notesList.innerHTML = '';

            if (savedNotes.length === 0) {
                notesList.innerHTML = '<div style="color: var(--dashboard-text-secondary); text-align: center; padding: 1rem;">No notes yet. Click "New Note" to add one.</div>';
                return;
            }

            savedNotes.forEach((note, index) => {
                const noteElement = document.createElement('div');
                noteElement.className = 'note-item';
                noteElement.innerHTML = `
            <div class="note-text">${note.text}</div>
            <div class="note-actions">
                <button class="delete-note" data-index="${index}"><i class="fas fa-trash-alt"></i></button>
            </div>
        `;
                notesList.appendChild(noteElement);
            });

            document.querySelectorAll('.delete-note').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    deleteNote(index);
                });
            });
        }

        function saveNote() {
            if (!noteText || !noteForm) return;

            const text = noteText.value.trim();
            if (!text) return;

            const savedNotes = JSON.parse(localStorage.getItem('userNotes') || '[]');
            savedNotes.push({
                text,
                createdAt: new Date().toISOString()
            });

            localStorage.setItem('userNotes', JSON.stringify(savedNotes));
            noteText.value = '';
            noteForm.style.display = 'none';
            loadNotes();
            showToast('Note added successfully', 'success');
        }

        function deleteNote(index) {
            const savedNotes = JSON.parse(localStorage.getItem('userNotes') || '[]');
            savedNotes.splice(index, 1);
            localStorage.setItem('userNotes', JSON.stringify(savedNotes));
            loadNotes();
            showToast('Note deleted', 'info');
        }

        openNotes.addEventListener('click', function() {
            notesWidget.style.display = notesWidget.style.display === 'none' ? 'block' : 'none';
            if (notesWidget.style.display === 'block') {
                loadNotes();
            }
        });

        if (addNoteBtn) {
            addNoteBtn.addEventListener('click', function() {
                if (noteForm) {
                    noteForm.style.display = 'block';
                    if (noteText) noteText.focus();
                }
            });
        }

        if (cancelNoteBtn) {
            cancelNoteBtn.addEventListener('click', function() {
                if (noteForm) {
                    noteForm.style.display = 'none';
                    if (noteText) noteText.value = '';
                }
            });
        }

        if (saveNoteBtn) {
            saveNoteBtn.addEventListener('click', saveNote);
        }
    }

    window.showToast = function(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            console.error("Toast container not found");
            return;
        }

        const existingToasts = toastContainer.querySelectorAll('.toast');
        for (let i = 0; i < existingToasts.length; i++) {
            const toastContent = existingToasts[i].querySelector('.toast-content');
            if (toastContent && toastContent.textContent.includes(message)) {
                return; 
            }
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        let icon;
        switch (type) {
            case 'success':
                icon = '<i class="fas fa-check-circle"></i>';
                break;
            case 'error':
                icon = '<i class="fas fa-exclamation-circle"></i>';
                break;
            case 'warning':
                icon = '<i class="fas fa-exclamation-triangle"></i>';
                break;
            case 'info':
            default:
                icon = '<i class="fas fa-info-circle"></i>';
        }

        toast.innerHTML = `
    <div class="toast-content">
        ${icon} ${message}
    </div>
    <button class="toast-close">&times;</button>
`;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);

        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', function() {
            toast.remove();
        });
    }

    const activityFilter = document.getElementById('activity-filter');
    const refreshActivitiesBtn = document.getElementById('refresh-activities');
    const activityItems = document.querySelectorAll('.activity-item');

    if (activityFilter) {
        activityFilter.addEventListener('change', function() {
            const filterValue = this.value;

            activityItems.forEach(item => {

                const activityIcon = item.querySelector('.activity-icon i');
                let activityType = 'all';

                if (activityIcon) {
                    if (activityIcon.classList.contains('fa-sign-in-alt')) {
                        activityType = 'login';
                    } else if (activityIcon.classList.contains('fa-shield-alt')) {
                        activityType = 'security';
                    } else if (activityIcon.classList.contains('fa-user-check')) {
                        activityType = 'verification';
                    }
                }

                if (filterValue === 'all' || filterValue === activityType) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    if (refreshActivitiesBtn) {
        refreshActivitiesBtn.addEventListener('click', function() {

            const icon = this.querySelector('i');
            icon.classList.add('fa-spin');

            setTimeout(() => {

                icon.classList.remove('fa-spin');

                showToast('Activities refreshed', 'success');
            }, 1000);
        });
    }
});