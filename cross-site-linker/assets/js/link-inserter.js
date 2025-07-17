const { registerFormatType, toggleFormat } = wp.richText;
const { RichTextToolbarButton } = wp.editor;
const { Popover, Button, TextControl, Spinner } = wp.components;
const { useState } = wp.element;
// Use the browser's fetch API for cross-site requests

const CrossSiteLinkInserter = ({ isActive, value, onChange }) => {
    const [isPopoverVisible, setIsPopoverVisible] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [suggestions, setSuggestions] = useState([]);
    const [isLoading, setIsLoading] = useState(false);

    const onToggle = () => {
        setIsPopoverVisible(previousState => !previousState);
    };

    const searchSuggestions = () => {
        setIsLoading(true);
        const { sites, home_url } = crossSiteLinker;
        const promises = sites
            .filter(site => site.url !== home_url)
            .map(site => {
                const url = `${site.url}/wp-json/crosslinker/v1/posts?q=${searchTerm}`;
                return fetch(url, {
                    headers: site.api_key ? { 'X-API-KEY': site.api_key } : {},
                })
                    .then(response => response.json())
                    .then(posts => {
                        return posts.map(post => ({ ...post, siteName: site.name }));
                    });
            });

        Promise.all(promises)
            .then(results => {
                const newSuggestions = [].concat(...results);
                setSuggestions(newSuggestions);
                setIsLoading(false);
            })
            .catch(error => {
                console.error(error);
                setIsLoading(false);
                setSuggestions([]);
                alert('An error occurred while searching for suggestions.');
            });
    };

    const insertLink = (url) => {
        onChange(
            toggleFormat(value, {
                type: 'cross-site-linker/link',
                attributes: { url },
            })
        );
        setIsPopoverVisible(false);
    };

    return (
        <>
            <RichTextToolbarButton
                icon="admin-links"
                title="Cross-Site Link"
                onClick={onToggle}
                isActive={isActive}
            />
            {isPopoverVisible && (
                <Popover
                    position="bottom center"
                    onClose={() => setIsPopoverVisible(false)}
                >
                    <div style={{ padding: '16px' }}>
                        <TextControl
                            label="Search for a post"
                            value={searchTerm}
                            onChange={setSearchTerm}
                        />
                        <Button isPrimary onClick={searchSuggestions} disabled={isLoading}>
                            {isLoading ? 'Searching...' : 'Search'}
                        </Button>
                        <hr />
                        {isLoading && <Spinner />}
                        {suggestions.length > 0 && (
                            <ul>
                                {suggestions.map(suggestion => (
                                    <li key={suggestion.url}>
                                        <Button
                                            isLink
                                            onClick={() => insertLink(suggestion.url)}
                                        >
                                            {suggestion.title}
                                        </Button>
                                        <p>{suggestion.excerpt}</p>
                                        <p>
                                            <em>{suggestion.siteName}</em>
                                        </p>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </Popover>
            )}
        </>
    );
};

registerFormatType('cross-site-linker/link', {
    title: 'Cross-Site Link',
    tagName: 'a',
    className: null,
    attributes: {
        url: 'href',
    },
    edit: CrossSiteLinkInserter,
});
