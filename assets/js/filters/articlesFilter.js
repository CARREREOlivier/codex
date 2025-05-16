export function filterArticles(query) {
    const articles = document.querySelectorAll('#sommaire > div');
    const noResultsMessage = document.getElementById('noResultsMessage');
    let found = false;
    query = query.toLowerCase();

    articles.forEach(article => {
        const title = article.querySelector('a').textContent.toLowerCase();
        const match = title.includes(query);
        article.style.display = match ? '' : 'none';
        if (match) found = true; // Met à jour la variable found si au moins un résultat est trouvé
    });

    if (noResultsMessage) {
        noResultsMessage.classList.toggle('hidden', found); // Affiche uniquement si aucun résultat
    }
}