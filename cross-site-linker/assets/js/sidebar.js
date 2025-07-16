const { registerPlugin } = wp.plugins;
const { PluginSidebar } = wp.editPost;
const { PanelBody, Button } = wp.components;
const { withSelect } = wp.data;
const { Component, Fragment } = wp.element;
const apiFetch = wp.apiFetch;

class CrossSiteLinkerSidebar extends Component {
    constructor() {
        super(...arguments);
        this.state = {
            suggestions: [],
            isLoading: false,
        };
    }

    componentDidMount() {
        this.fetchSuggestions();
    }

    fetchSuggestions = () => {
        const { postTitle, postContent } = this.props;
        const { sites, home_url } = crossSiteLinker;

        this.setState({ isLoading: true });

        const keywords = postTitle.split(' ').slice(0, 5).join(' ');
        const promises = sites
            .filter(site => site.url !== home_url)
            .map(site => {
                let path = `${site.url}/wp-json/crosslinker/v1/posts?q=${keywords}`;
                return apiFetch({
                    path,
                    headers: site.api_key ? { 'X-API-KEY': site.api_key } : {},
                }).then(posts => {
                    return posts.map(post => ({ ...post, siteName: site.name }));
                });
            });

        Promise.all(promises)
            .then(results => {
                const suggestions = [].concat(...results);
                this.setState({ suggestions, isLoading: false });
            })
            .catch(error => {
                console.error(error);
                this.setState({ isLoading: false, suggestions: [] });
                alert('An error occurred while fetching suggestions.');
            });
    };

    insertLink = (url, title) => {
        const { content, onChange } = this.props;
        const newContent = content + `\n<a href="${url}">${title}</a>`;
        onChange(newContent);
    };

    render() {
        const { suggestions, isLoading } = this.state;

        return (
            <Fragment>
                <PluginSidebar
                    name="cross-site-linker-sidebar"
                    title="Cross-Site Suggestions"
                >
                    <PanelBody>
                        <Button isPrimary onClick={this.fetchSuggestions} disabled={isLoading}>
                            {isLoading ? 'Loading...' : 'Refresh Suggestions'}
                        </Button>
                        <hr />
                        {suggestions.length > 0 ? (
                            <ul>
                                {suggestions.map(suggestion => (
                                    <li key={suggestion.url}>
                                        <a href={suggestion.url} target="_blank" rel="noopener noreferrer">
                                            {suggestion.title}
                                        </a>
                                        <p>{suggestion.excerpt}</p>
                                        <p>
                                            <em>{suggestion.siteName}</em>
                                        </p>
                                        <Button
                                            isSecondary
                                            onClick={() => this.insertLink(suggestion.url, suggestion.title)}
                                        >
                                            Insert
                                        </Button>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p>No suggestions found.</p>
                        )}
                    </PanelBody>
                </PluginSidebar>
            </Fragment>
        );
    }
}

const applyWithSelect = withSelect(select => {
    const { getEditedPostAttribute } = select('core/editor');
    const { editPost } = select('core/editor');

    return {
        postTitle: getEditedPostAttribute('title'),
        postContent: getEditedPostAttribute('content'),
        onChange: editPost,
    };
});

registerPlugin('cross-site-linker', {
    render: applyWithSelect(CrossSiteLinkerSidebar),
    icon: 'admin-links',
});
