(function(wp){
    const { registerFormatType, toggleFormat } = wp.richText;
    const { createElement, Fragment, useState } = wp.element;
    const { RichTextToolbarButton } = wp.blockEditor;
    const { Popover, TextControl } = wp.components;
    const { __ } = wp.i18n;
    const apiFetch = wp.apiFetch;

    const name = 'csl/contextual-link';

    const EditButton = ({ isActive, value, onChange }) => {
        const [ show, setShow ] = useState( false );
        const [ results, setResults ] = useState([]);
        const [ query, setQuery ] = useState('');
        const [ rect, setRect ] = useState();

        const onToggle = () => {
            const selection = window.getSelection();
            if ( selection.rangeCount === 0 ) {
                alert( __('Please select text first.', 'csl') );
                return;
            }
            setRect( selection.getRangeAt(0).getBoundingClientRect() );
            setShow( ! show );
        };

        const search = (q) => {
            setQuery(q);
            if ( ! q ) return;
            apiFetch( { path: '/csl/v1/suggestions?title=' + encodeURIComponent(q) } ).then( res => setResults(res) );
        };

        const insert = ( url ) => {
            onChange( toggleFormat( value, { type: name, attributes: { href: url } } ) );
            setShow(false);
        };

        return createElement( Fragment, {},
            createElement( RichTextToolbarButton, { icon: 'admin-links', title: __('Cross-Site Link', 'csl'), onClick: onToggle, isActive } ),
            show && createElement( Popover, { position: 'bottom left', anchorRect: rect, onClose: ()=>setShow(false) },
                createElement( TextControl, { value: query, onChange: search, placeholder: __('Search...', 'csl') } ),
                createElement('div', {}, results.map( (r, i) =>
                    createElement('p', { key: i },
                        createElement('a', { href: '#', onClick:(e)=>{e.preventDefault();insert(r.url);} }, r.title + ' ('+r.site+')' )
                    )
                ))
            )
        );
    };

    registerFormatType( name, {
        title: __('Cross-Site Link', 'csl'),
        tagName: 'a',
        className: null,
        edit: EditButton,
        attributes: {
            href: 'href'
        }
    } );
})(window.wp);
