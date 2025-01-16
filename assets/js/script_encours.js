document.addEventListener('DOMContentLoaded', () => {
    const statusElement = document.getElementById('status');
    const progressBar = document.querySelector('.progress');
    let progress = 0;

    const messages = [
        "Initialisation du traitement...",
        "Vérification des informations...",
        "Analyse des données en cours...",
        "Mise à jour du dossier...",
        "Traitement en cours..."
    ];

    function updateStatus() {
        progress += Math.floor(Math.random() * 5) + 1;
        if (progress >= 100) {
            progress = 0; // Reset progress to create a loop effect
        }
        progressBar.style.width = `${progress}%`;

        const messageIndex = Math.floor((progress / 100) * messages.length);
        statusElement.textContent = messages[messageIndex];

        setTimeout(updateStatus, Math.random() * 1000 + 500);
    }

    updateStatus();
});

