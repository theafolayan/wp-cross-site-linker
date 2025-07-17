jQuery(document).ready(function($) {
    const { sites, home_url, post_title } = crossSiteLinker;
    const apiFetch = wp.apiFetch;
    let isLoading = false;

    function fetchSuggestions() {
        if (isLoading) {
            return;
        }

        isLoading = true;
        console.log('Fetching suggestions...');
        $('#refresh-suggestions').text('Loading...').prop('disabled', true);

        const keywords = post_title.split(' ').slice(0, 5).join(' ');
        const promises = sites
            .filter(site => site.url !== home_url)
            .map(site => {
                let url = `${site.url}/wp-json/crosslinker/v1/posts?q=${keywords}`;
                console.log(`Fetching from: ${url}`);
                return apiFetch({
                    url,
                    headers: site.api_key ? { 'X-API-KEY': site.api_key } : {},
                }).then(posts => {
                    console.log(`Received ${posts.length} posts from ${site.name}`);
                    return posts.map(post => ({ ...post, siteName: site.name }));
                });
            });

        Promise.all(promises)
            .then(results => {
                const suggestions = [].concat(...results);
                console.log(`Total suggestions: ${suggestions.length}`);
                renderSuggestions(suggestions);
                isLoading = false;
                $('#refresh-suggestions').text('Refresh Suggestions').prop('disabled', false);
            })
            .catch(error => {
                console.error('An error occurred while fetching suggestions:', error);
                $('#suggestions-list').html('<p>An error occurred while fetching suggestions.</p>');
                isLoading = false;
                $('#refresh-suggestions').text('Refresh Suggestions').prop('disabled', false);
            });
    }

    function renderSuggestions(suggestions) {
        const list = $('#suggestions-list');
        list.empty();

        if (suggestions.length === 0) {
            list.html('<p>No suggestions found.</p>');
            return;
        }

        const ul = $('<ul></ul>');
        suggestions.forEach(suggestion => {
            const li = $(`
                <li>
                    <a href="${suggestion.url}" target="_blank" rel="noopener noreferrer">${suggestion.title}</a>
                    <p>${suggestion.excerpt}</p>
                    <p><em>${suggestion.siteName}</em></p>
                    <button type="button" class="button button-secondary insert-link" data-url="${suggestion.url}" data-title="${suggestion.title}">Insert</button>
                </li>
            `);
            ul.append(li);
        });
        list.append(ul);
    }

    function insertLink(url, title) {
        const editor = tinymce.get('content');
        if (editor) {
            editor.execCommand('mceInsertContent', false, `<a href="${url}">${title}</a>`);
        } else {
            const content = $('#content').val();
            const newContent = content + `\n<a href="${url}">${title}</a>`;
            $('#content').val(newContent);
        }
        console.log(`Inserted link: ${title} (${url})`);
    }

    $('#refresh-suggestions').on('click', fetchSuggestions);

    $('#suggestions-list').on('click', '.insert-link', function() {
        const url = $(this).data('url');
        const title = $(this).data('title');
        insertLink(url, title);
    });

    fetchSuggestions();
});
