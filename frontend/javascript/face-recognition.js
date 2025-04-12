document.addEventListener('DOMContentLoaded', async () => {

    const video = document.getElementById('faceVideo');
    const canvas = document.getElementById('faceOverlay');
    const faceStatus = document.getElementById('faceStatus');
    const detectionStatus = document.getElementById('detectionStatus');
    const startBtn = document.getElementById('startFaceDetection');
    const stopBtn = document.getElementById('stopFaceDetection');
    const emotionDisplay = document.getElementById('emotionDisplay');
    const faceLoading = document.getElementById('faceLoading');

    const happyMetric = document.getElementById('happyMetric');
    const sadMetric = document.getElementById('sadMetric');
    const angryMetric = document.getElementById('angryMetric');
    const surprisedMetric = document.getElementById('surprisedMetric');

    let isModelLoaded = false;
    let stream = null;
    let detectionInterval = null;
    let emotionHistory = [];
    const historyLength = 5; 

    const faceDetectorOptions = new faceapi.TinyFaceDetectorOptions({ 
        inputSize: 512,
        scoreThreshold: 0.5
    });

    async function loadModels() {
        try {
            faceLoading.style.display = 'flex';
            detectionStatus.textContent = 'Loading...';
            detectionStatus.className = 'status-badge';

            const MODEL_URL = '../backend/facialrecog/models';

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL), 
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL) 
            ]);

            isModelLoaded = true;
            faceLoading.style.display = 'none';
            faceStatus.innerHTML = '<i class="fas fa-camera-slash"></i> Camera inactive';
            detectionStatus.textContent = 'Ready';
            startBtn.disabled = false;

            showToast('Emotion detection AI loaded', 'success');
        } catch (error) {
            console.error('Error loading face-api models:', error);
            faceLoading.style.display = 'none';
            faceStatus.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error loading models';
            faceStatus.classList.add('error');
            detectionStatus.textContent = 'Error';
            detectionStatus.className = 'status-badge error';
            showToast('Failed to load emotion detection models', 'error');
        }
    }

    async function startFaceDetection() {
        if (!isModelLoaded) {
            showToast('AI models are not loaded yet', 'error');
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
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

            const displaySize = { width: video.videoWidth, height: video.videoHeight };
            faceapi.matchDimensions(canvas, displaySize);

            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-flex';
            faceStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Initializing...';
            faceStatus.classList.add('active');
            detectionStatus.textContent = 'Active';
            detectionStatus.className = 'status-badge active';

            emotionHistory = [];

            detectionInterval = setInterval(async () => {
                await detectAndDisplayEmotions();
            }, 100);

            showToast('Emotion detection started', 'success');
        } catch (error) {
            console.error('Error accessing camera:', error);
            faceStatus.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Camera access denied';
            faceStatus.classList.add('error');
            detectionStatus.textContent = 'Error';
            detectionStatus.className = 'status-badge error';
            showToast('Camera access denied', 'error');
        }
    }

    async function detectAndDisplayEmotions() {
        try {
            const detections = await faceapi.detectAllFaces(
                video, 
                faceDetectorOptions
            )
            .withFaceLandmarks()
            .withFaceExpressions();

            const displaySize = { width: video.videoWidth, height: video.videoHeight };
            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (detections.length > 0) {

                drawStylizedDetection(ctx, resizedDetections[0].detection);

                drawFacialLandmarks(ctx, resizedDetections[0].landmarks);

                const rawEmotions = enhanceEmotions(detections[0].expressions);
                emotionHistory.push(rawEmotions);

                if (emotionHistory.length > historyLength) {
                    emotionHistory.shift();
                }

                const smoothedEmotions = smoothEmotions(emotionHistory);
                const dominantEmotion = getDominantEmotion(smoothedEmotions);

                updateEmotionDisplay(dominantEmotion, smoothedEmotions[dominantEmotion]);
                updateEmotionMetrics(smoothedEmotions);

                faceStatus.innerHTML = `<i class="fas fa-check-circle"></i> ${formatEmotion(dominantEmotion)}`;
            } else {
                emotionDisplay.innerHTML = `
                    <div class="emotion-placeholder">
                        <i class="fas fa-face-meh-blank"></i>
                        <span>No face detected</span>
                    </div>
                `;
                resetEmotionMetrics();
                faceStatus.innerHTML = '<i class="fas fa-search"></i> Looking for faces...';
            }
        } catch (error) {
            console.error('Error in emotion detection:', error);
        }
    }

    function enhanceEmotions(expressions) {

        const enhanced = {...expressions};

        if (enhanced.neutral > 0.5) {
            enhanced.neutral *= 0.8; 
        }

        for (const emotion in enhanced) {
            if (emotion !== 'neutral') {

                let factor = 1.0;

                switch (emotion) {
                    case 'sad': 
                        factor = 1.5;
                        break;
                    case 'happy':
                        factor = 1.2;
                        break;
                    case 'angry':
                        factor = 1.3;
                        break;
                    case 'surprised':
                        factor = 1.4;
                        break;
                    default:
                        factor = 1.2;
                }

                enhanced[emotion] = Math.min(1.0, enhanced[emotion] * factor);
            }
        }

        const sum = Object.values(enhanced).reduce((a, b) => a + b, 0);
        for (const emotion in enhanced) {
            enhanced[emotion] = enhanced[emotion] / sum;
        }

        return enhanced;
    }

    function drawFacialLandmarks(ctx, landmarks) {

        const colors = {
            jaw: 'rgba(124, 58, 237, 0.7)',         
            eyebrows: 'rgba(59, 130, 246, 0.8)',    
            eyes: 'rgba(16, 185, 129, 0.8)',        
            nose: 'rgba(245, 158, 11, 0.8)',        
            mouth: 'rgba(239, 68, 68, 0.8)'         
        };

        const positions = landmarks.positions;

        drawDottedPolyline(ctx, positions.slice(0, 17), colors.jaw, 2);

        drawDottedPolyline(ctx, positions.slice(17, 22), colors.eyebrows, 2);

        drawDottedPolyline(ctx, positions.slice(22, 27), colors.eyebrows, 2);

        drawDottedPolyline(ctx, positions.slice(27, 31), colors.nose, 2);

        drawDottedPolyline(ctx, positions.slice(31, 36), colors.nose, 2);

        drawDottedPolyline(ctx, [...positions.slice(36, 42), positions[36]], colors.eyes, 2);

        drawDottedPolyline(ctx, [...positions.slice(42, 48), positions[42]], colors.eyes, 2);

        drawDottedPolyline(ctx, [...positions.slice(48, 60), positions[48]], colors.mouth, 2);

        drawDottedPolyline(ctx, [...positions.slice(60, 68), positions[60]], colors.mouth, 2);
    }

    function drawDottedPolyline(ctx, points, color, width = 2, dashLength = 5, gapLength = 3) {
        if (!points || points.length < 2) return;

        ctx.strokeStyle = color;
        ctx.lineWidth = width;

        for (let i = 0; i < points.length - 1; i++) {
            drawDottedLine(
                ctx, 
                points[i].x, points[i].y, 
                points[i + 1].x, points[i + 1].y,
                dashLength, gapLength
            );
        }
    }

    function drawDottedLine(ctx, x1, y1, x2, y2, dashLength, gapLength) {
        ctx.beginPath();

        const dx = x2 - x1;
        const dy = y2 - y1;
        const distance = Math.sqrt(dx * dx + dy * dy);
        const dashCount = Math.floor(distance / (dashLength + gapLength));

        const dashX = dx / dashCount;
        const dashY = dy / dashCount;

        let x = x1;
        let y = y1;
        let drawn = true; 

        ctx.moveTo(x, y);

        for (let i = 0; i < dashCount; i++) {

            x += dashX * (drawn ? dashLength : gapLength) / (dashLength + gapLength);
            y += dashY * (drawn ? dashLength : gapLength) / (dashLength + gapLength);

            if (drawn) {
                ctx.lineTo(x, y);
            } else {
                ctx.moveTo(x, y);
            }

            drawn = !drawn; 
        }

        if (drawn) {
            ctx.lineTo(x2, y2);
        }

        ctx.stroke();
    }

    function drawStylizedDetection(ctx, detection) {
        const box = detection.box;
        const drawBox = true;
        const lineWidth = 2;
        const boxColor = 'rgba(124, 58, 237, 0.8)';

        if (drawBox) {

            const radius = 4;
            ctx.strokeStyle = boxColor;
            ctx.lineWidth = lineWidth;

            ctx.beginPath();
            ctx.moveTo(box.x + radius, box.y);
            ctx.lineTo(box.x + box.width - radius, box.y);
            ctx.quadraticCurveTo(box.x + box.width, box.y, box.x + box.width, box.y + radius);
            ctx.lineTo(box.x + box.width, box.y + box.height - radius);
            ctx.quadraticCurveTo(box.x + box.width, box.y + box.height, box.x + box.width - radius, box.y + box.height);
            ctx.lineTo(box.x + radius, box.y + box.height);
            ctx.quadraticCurveTo(box.x, box.y + box.height, box.x, box.y + box.height - radius);
            ctx.lineTo(box.x, box.y + radius);
            ctx.quadraticCurveTo(box.x, box.y, box.x + radius, box.y);
            ctx.closePath();
            ctx.stroke();

            const gradient = ctx.createLinearGradient(box.x, box.y, box.x + box.width, box.y + box.height);
            gradient.addColorStop(0, 'rgba(124, 58, 237, 0.3)');
            gradient.addColorStop(1, 'rgba(124, 58, 237, 0)');
            ctx.strokeStyle = gradient;
            ctx.stroke();
        }
    }

    function smoothEmotions(history) {
        if (history.length === 0) return {};

        const result = {};
        const emotions = Object.keys(history[0]);

        emotions.forEach(emotion => {
            let totalWeight = 0;
            let weightedSum = 0;

            for (let i = 0; i < history.length; i++) {

                const weight = (i + 1);
                weightedSum += history[i][emotion] * weight;
                totalWeight += weight;
            }

            result[emotion] = weightedSum / totalWeight;
        });

        return result;
    }

    function getDominantEmotion(expressions) {
        return Object.keys(expressions).reduce((a, b) => 
            expressions[a] > expressions[b] ? a : b
        );
    }

    function formatEmotion(emotion) {
        const map = {
            'neutral': 'Neutral',
            'happy': 'Happy',
            'sad': 'Sad',
            'angry': 'Angry',
            'fearful': 'Fearful',
            'disgusted': 'Disgusted',
            'surprised': 'Surprised'
        };
        return map[emotion] || emotion;
    }

    function updateEmotionDisplay(emotion, confidence) {

        const emotions = {
            'happy': { emoji: '😊', class: 'happy-color' },
            'sad': { emoji: '😢', class: 'sad-color' },
            'angry': { emoji: '😠', class: 'angry-color' },
            'fearful': { emoji: '😨', class: 'fearful-color' },
            'disgusted': { emoji: '🤢', class: 'disgusted-color' },
            'surprised': { emoji: '😲', class: 'surprised-color' },
            'neutral': { emoji: '😐', class: 'neutral-color' }
        };

        const emotionInfo = emotions[emotion] || { emoji: '❓', class: 'neutral-color' };
        const confidencePercent = Math.round(confidence * 100);

        emotionDisplay.innerHTML = `
            <div class="emotion-result ${emotionInfo.class}">
                <div class="emotion-emoji">${emotionInfo.emoji}</div>
                <div class="emotion-label">${formatEmotion(emotion)}</div>
                <div class="emotion-confidence">
                    <div class="confidence-bar">
                        <div class="confidence-fill ${emotion}-bg" style="width: ${confidencePercent}%"></div>
                    </div>
                    <div class="confidence-text">${confidencePercent}% confidence</div>
                </div>
            </div>
        `;
    }

    function updateEmotionMetrics(emotions) {
        updateMetric(happyMetric, emotions.happy, 'happy-bg');
        updateMetric(sadMetric, emotions.sad, 'sad-bg');
        updateMetric(angryMetric, emotions.angry, 'angry-bg');
        updateMetric(surprisedMetric, emotions.surprised, 'surprised-bg');
    }

    function updateMetric(element, value, colorClass) {
        if (!element) return;

        const percent = Math.round(value * 100);
        const fill = element.querySelector('.metric-fill');
        const valueEl = element.querySelector('.metric-value');

        fill.style.width = `${percent}%`;
        fill.className = `metric-fill ${colorClass}`;
        valueEl.textContent = `${percent}%`;
    }

    function resetEmotionMetrics() {
        updateMetric(happyMetric, 0, 'happy-bg');
        updateMetric(sadMetric, 0, 'sad-bg');
        updateMetric(angryMetric, 0, 'angry-bg');
        updateMetric(surprisedMetric, 0, 'surprised-bg');
    }

    function stopFaceDetection() {
        if (detectionInterval) {
            clearInterval(detectionInterval);
            detectionInterval = null;
        }

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
            video.style.display = 'none';

            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);

            startBtn.style.display = 'inline-flex';
            stopBtn.style.display = 'none';
            faceStatus.innerHTML = '<i class="fas fa-camera-slash"></i> Camera inactive';
            faceStatus.classList.remove('active', 'error');
            detectionStatus.textContent = 'Inactive';
            detectionStatus.className = 'status-badge';

            emotionDisplay.innerHTML = `
                <div class="emotion-placeholder">
                    <i class="fas fa-face-meh-blank"></i>
                    <span>Start camera to detect emotions</span>
                </div>
            `;

            resetEmotionMetrics();

            showToast('Emotion detection stopped', 'info');
        }
    }

    startBtn.addEventListener('click', startFaceDetection);
    stopBtn.addEventListener('click', stopFaceDetection);

    loadModels();

    function showToast(message, type) {

        if (window.showToast) {
            window.showToast(message, type);
            return;
        }

        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) return;

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
});