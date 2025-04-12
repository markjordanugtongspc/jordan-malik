document.addEventListener('DOMContentLoaded', async () => {
    // Theme toggle functionality - copied from dashboard.js
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

    const video = document.getElementById('qr-video');
    const canvas = document.getElementById('qr-canvas');
    const startBtn = document.getElementById('start-camera');
    const scanStatus = document.getElementById('scan-status');
    const userIdElement = document.getElementById('user-id');
    const userNameElement = document.getElementById('user-name');
    const userEmailElement = document.getElementById('user-email');
    const qrImage = document.getElementById('qr-image');
    const downloadBtn = document.getElementById('download-qr');
    const copyLinkBtn = document.getElementById('copy-qr-link');

    let stream = null;
    let isScanning = false;
    let lastScannedData = null;
    let scanInterval = null;

    downloadBtn.addEventListener('click', async () => {
        try {
            const response = await fetch(qrImage.src);
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'qrcode.png';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showToast('QR code downloaded successfully', 'success');
        } catch (error) {
            console.error('Error downloading QR code:', error);
            showToast('Failed to download QR code', 'error');
        }
    });

    copyLinkBtn.addEventListener('click', () => {
        try {
            navigator.clipboard.writeText(qrImage.src).then(() => {
                showToast('QR code link copied to clipboard', 'success');
            });
        } catch (error) {
            console.error('Error copying QR code link:', error);
            showToast('Failed to copy QR code link', 'error');
        }
    });

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            });

            video.srcObject = stream;
            video.style.display = 'block';

            await new Promise(resolve => {
                video.onloadedmetadata = () => {
                    resolve();
                };
            });

            startScanning();

            startBtn.innerHTML = '<i class="fas fa-stop"></i> Stop Camera';
            scanStatus.style.display = 'block';
            isScanning = true;

            showToast('Camera started', 'success');
        } catch (error) {
            console.error('Error accessing camera:', error);
            scanStatus.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Camera access denied`;
            scanStatus.style.background = 'rgba(239, 68, 68, 0.7)';
            showToast('Camera access denied', 'error');
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.style.display = 'none';
        }

        const context = canvas.getContext('2d');
        context.clearRect(0, 0, canvas.width, canvas.height);

        if (scanInterval) {
            clearInterval(scanInterval);
            scanInterval = null;
        }

        startBtn.innerHTML = '<i class="fas fa-camera"></i> Start Camera';
        scanStatus.style.display = 'none';
        isScanning = false;

        if (!lastScannedData) {
            resetUserInfo();
        }

        showToast('Camera stopped', 'info');
    }

    function resetUserInfo() {
        userIdElement.textContent = 'N/A';
        userNameElement.textContent = 'N/A';
        userEmailElement.textContent = 'N/A';
        lastScannedData = null;
    }

    function startScanning() {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');

        if (scanInterval) {
            clearInterval(scanInterval);
        }

        scanInterval = setInterval(async () => {
            if (!isScanning) return;

            try {

                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                const imageData = canvas.toDataURL('image/jpeg', 0.5); 

                const response = await fetch(imageData);
                const blob = await response.blob();

                const formData = new FormData();
                formData.append('file', blob, 'qrcode.jpg');

                const apiResponse = await fetch('https://api.qrserver.com/v1/read-qr-code/', {
                    method: 'POST',
                    body: formData
                });

                const data = await apiResponse.json();

                if (data && data.length > 0 && data[0].symbol && data[0].symbol.length > 0) {
                    const qrData = data[0].symbol[0].data;

                    if (qrData && qrData !== lastScannedData) {

                        const success = parseQRCodeData(qrData);

                        if (success) {
                            lastScannedData = qrData;

                            scanStatus.innerHTML = `<i class="fas fa-check"></i> QR Code detected`;
                            scanStatus.style.background = 'rgba(16, 185, 129, 0.7)';

                            stopCamera();
                        }
                    }
                }
            } catch (error) {
                console.error('Error scanning QR code:', error);
            }
        }, 500); 
    }

    function parseQRCodeData(data) {
        try {

            const userData = JSON.parse(data);

            if (!userData.id || !userData.name || !userData.email) {
                throw new Error('Invalid QR code format');
            }

            userIdElement.textContent = userData.id;
            userNameElement.textContent = userData.name;
            userEmailElement.textContent = userData.email;

            showToast('User data loaded from QR code', 'success');
            return true;
        } catch (error) {
            console.log('QR Code data:', data);

            const idMatch = data.match(/ID:\s*([^\n]+)/);
            const nameMatch = data.match(/Name:\s*([^\n]+)/);
            const emailMatch = data.match(/Email:\s*([^\n]+)/);

            if (idMatch && nameMatch && emailMatch) {
                userIdElement.textContent = idMatch[1].trim();
                userNameElement.textContent = nameMatch[1].trim();
                userEmailElement.textContent = emailMatch[1].trim();

                showToast('User data loaded from QR code', 'success');
                return true;
            } else {
                showToast('Invalid QR code format', 'error');
                return false;
            }
        }
    }

    startBtn.addEventListener('click', function() {
        if (!isScanning) {
            startCamera();
        } else {
            stopCamera();
        }
    });

    function showToast(message, type) {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) return;

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

    resetUserInfo();

    // Load profile image from localStorage for consistent UI across pages
    function loadProfileImage() {
        const profileImageElements = document.querySelectorAll('.profile-image img');
        if (profileImageElements.length > 0) {
            const savedImage = localStorage.getItem("profileImage");
            if (savedImage) {
                profileImageElements.forEach(img => {
                    img.src = savedImage;
                });
            }
        }
    }
    
    // Call the function to load profile images
    loadProfileImage();
});