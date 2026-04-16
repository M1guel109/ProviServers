document.addEventListener('mousemove', (e) => {
    const mainIllustration = document.querySelector('.main-illustration'); 
    const big404 = document.querySelector('.big-404'); 

    const centerX = window.innerWidth / 2;
    const centerY = window.innerHeight / 2;
    
    const x = e.clientX - centerX;
    const y = e.clientY - centerY;
    
    const factorIllustration = 0.03;
    const factor404 = 0.05;

    // Mueve la imagen (basado en la posición inicial de -20px vertical del CSS)
    if (mainIllustration) {
        mainIllustration.style.transform = `translate(calc(-50% + ${x * factorIllustration}px), calc(-50% - 20px + ${y * factorIllustration}px))`;
    }
    
    // Mueve el 404 (basado en la posición inicial de 0px vertical)
    if (big404) {
        big404.style.transform = `translate(calc(-50% + ${-x * factor404 * 0.5}px), calc(-50% + ${-y * factor404 * 0.5}px))`;
    }
});