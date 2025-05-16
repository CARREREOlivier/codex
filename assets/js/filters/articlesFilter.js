export function filterArticles(query) {
    const articles = document.querySelectorAll('#sommaire > div');
    const noResultsMessage = document.getElementById('noResultsMessage');
    let found = false;
    query = query.toLowerCase();

    articles.forEach(article => {
        const title = article.querySelector('a').textContent.toLowerCase();
        article.style.display = title.includes(query) ? '' : 'none';
    });
    if (noResultsMessage) {
        noResultsMessage.classList.toggle('hidden', found);
    }
}