(function(wp){
    const { registerPlugin } = wp.plugins;
    const { PluginSidebar } = wp.editPost;
    const { PanelBody, Button } = wp.components;
    const { useSelect } = wp.data;
    const { Fragment, useState, useEffect } = wp.element;
    const { __ } = wp.i18n;
    const apiFetch = wp.apiFetch;

    const CrossSiteSuggestions = () => {
        const post = useSelect( select => select('core/editor').getCurrentPost(), [] );
        const [ suggestions, setSuggestions ] = useState([]);

        useEffect( () => {
            if ( ! post ) return;
            const keyword = post.title.rendered || '';
            if ( ! keyword ) return;
            apiFetch( { path: '/csl/v1/suggestions?title=' + encodeURIComponent( keyword ) } ).then( res => {
                setSuggestions( res );
            });
        }, [ post ] );

        const insertLink = ( url, title ) => {
            const content = post.content.raw + '\n<a href="'+url+'">'+title+'</a>';
            wp.data.dispatch('core/editor').editPost({ content: content });
        };

        return wp.element.createElement( PanelBody, { title: __('Cross-Site Suggestions', 'csl'), initialOpen: true },
            suggestions.map( (s, i) => wp.element.createElement( Fragment, { key: i },
                wp.element.createElement('p', {},
                    wp.element.createElement('a', { href: s.url, target: '_blank' }, s.title),
                    ' (' + s.site + ')'
                ),
                wp.element.createElement(Button, { isSecondary: true, onClick: () => insertLink(s.url, s.title) }, __('Insert', 'csl') )
            ))
        );
    };

    const PluginSidebarCrossSite = () => {
        return wp.element.createElement( PluginSidebar, { name: 'csl-sidebar', title: __('Cross-Site Suggestions', 'csl') },
            wp.element.createElement( CrossSiteSuggestions, null )
        );
    };

    registerPlugin( 'csl-sidebar', { render: PluginSidebarCrossSite } );
})(window.wp);
