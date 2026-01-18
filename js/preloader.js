window.addEventListener('load', () => {
    const preloader = document.getElementById('preloader');
    const gif = document.getElementById('loader-gif');
    
    if (!preloader || !gif) return;

    gif.style.display = 'none';
    
    setTimeout(() => {
        gsap.to(preloader, {
            opacity: 0,
            duration: 1.0,
            ease: "power2.out",
            onComplete: () => preloader.remove()
        });
    }, 300); // задержка перед стартом
});