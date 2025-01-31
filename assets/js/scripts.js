// This file contains JavaScript for the Medium integration widget.
// It handles interactions and dynamic content loading for the widget.

document.addEventListener('DOMContentLoaded', function() {
    const mediumWidget = document.querySelector('.medium-widget');

    if (mediumWidget) {
        // Fetch the latest Medium posts when the widget is loaded
        fetch(mediumWidget.dataset.rssUrl)
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const xml = parser.parseFromString(data, 'text/xml');
                const items = xml.querySelectorAll('item');

                // Clear existing content
                mediumWidget.innerHTML = '';

                // Loop through the items and create HTML for each post
                items.forEach(item => {
                    const title = item.querySelector('title').textContent;
                    const link = item.querySelector('link').textContent;
                    const description = item.querySelector('description').textContent;

                    const postElement = document.createElement('div');
                    postElement.classList.add('medium-post');

                    postElement.innerHTML = `
                        <h3><a href="${link}" target="_blank">${title}</a></h3>
                        <p>${description}</p>
                    `;

                    mediumWidget.appendChild(postElement);
                });
            })
            .catch(error => {
                console.error('Error fetching Medium posts:', error);
                mediumWidget.innerHTML = '<p>Unable to load Medium posts at this time.</p>';
            });
    }
});