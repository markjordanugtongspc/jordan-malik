document.addEventListener('DOMContentLoaded', function() {

    const inputs = document.querySelectorAll('.auth-form-content input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.transform = 'scale(1.02)';
        });
        input.addEventListener('blur', function() {
            this.style.transform = 'scale(1)';
        });
    });

    const animationContainer = document.querySelector('.auth-animation');
    for (let i = 0; i < 5; i++) {
        const particle = document.createElement('div');
        particle.className = 'floating-particle';
        particle.style.cssText = `
            --delay: ${Math.random() * 5}s;
            --size: ${Math.random() * 60 + 40}px;
            --left: ${Math.random() * 100}%;
            --duration: ${Math.random() * 15 + 10}s;
        `;
        animationContainer.appendChild(particle);
    }
});