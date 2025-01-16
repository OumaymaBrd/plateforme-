document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.container');
    const backgroundElements = document.querySelectorAll('.background-elements > div');

    document.addEventListener('mousemove', (e) => {
        const x = e.clientX / window.innerWidth;
        const y = e.clientY / window.innerHeight;

        container.style.transform = `translate(${x * 20}px, ${y * 20}px)`;

        backgroundElements.forEach((element, index) => {
            const speed = (index + 1) * 0.05;
            element.style.transform = `translate(${x * 50 * speed}px, ${y * 50 * speed}px)`;
        });
    });
});

