export function filterArticles(query) {
    const articles = document.querySelectorAll('#sommaire > div');
    query = query.toLowerCase();

    articles.forEach(article => {
        const title = article.querySelector('a').textContent.toLowerCase();
        article.style.display = title.includes(query) ? '' : 'none';
    });
}