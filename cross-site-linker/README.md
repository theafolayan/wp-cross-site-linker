# Cross-Site Linker

A WordPress plugin to cross-link posts, pages, and glossary entries between WordPress sites.

## Why This Plugin?

I built this because I was tired of wasting time.

I have multiple WordPress sites. They're all part of the same ecosystem, but they were siloed. Linking between them was a manual, painful process. I'd have to open a new tab, search for the right post, copy the URL, go back to the original post, highlight the text, and then paste the link. It was a tedious, time-sucking task that I dreaded.

This plugin solves that problem.

It connects all your sites, so you can instantly search for and link to any post on any of your other sites, right from the WordPress editor. No more new tabs, no more copying and pasting, no more wasted time.

If you have more than one WordPress site, this plugin will save you hours of your life. It's that simple.

## Description

Cross-Site Linker is a powerful tool for website owners who manage multiple WordPress sites and want to improve their internal linking strategy. This plugin allows you to seamlessly link posts from one site to another, enhancing user engagement and SEO.

The plugin has two main roles:

*   **Provider:** Exposes a public REST API endpoint to allow other sites to search for posts.
*   **Consumer:** Provides a user-friendly interface in the Gutenberg editor to find and insert links from other sites.

A single site can act as both a Provider and a Consumer, allowing for a truly interconnected network of websites.

## Features

### Provider Role

*   **Public REST API Endpoint:** `GET /wp-json/crosslinker/v1/posts?q=keyword`
*   **JSON Response:** Returns a list of matching posts with title, URL, and excerpt.
*   **WP\_Query Powered:** Searches posts, pages, and glossary entries via `WP_Query`.
*   **API Key Protection:** Optionally protect the API with an API key.

### Consumer Role (Editor Features)

*   **Gutenberg Sidebar:** A "Cross-Site Suggestions" sidebar in the post editor.
*   **Automatic Keyword Extraction:** Automatically extracts keywords from the current post title to find relevant suggestions.
*   **Merge and Rank:** Merges and ranks suggestions from all configured external sites.
*   **Insert Links:** Easily insert links to external posts into the current post.
*   **Contextual Link Inserter:** A modal to search for and insert links when you highlight text in the editor.
*   **Classic Editor Integration:** Adds results from your other sites to the link dialog search when editing with the Classic Editor.

### Settings Page

*   **Manage Sites:** A user-friendly interface to manage a list of other WordPress sites for cross-linking.
*   **Test Connection:** A "Test Connection" button for each site to verify connectivity.

### Performance

*   **Caching:** Caches API responses using WordPress transients to improve performance.
*   **Async Fetches:** Uses asynchronous fetches to get suggestions from all sites without blocking the editor.

## Installation

1.  Download the plugin as a ZIP file.
2.  In your WordPress admin panel, go to **Plugins > Add New**.
3.  Click **Upload Plugin** and select the ZIP file.
4.  Activate the plugin.

## How to Use

### 1. Configure the Plugin

1.  Go to **Settings > Cross-Site Linker** in your WordPress admin panel.
2.  **API Key (Optional):** If you want to protect your site's API, enter an API key in the "API Key" field. This key will be required for other sites to access your posts.
3.  **Add Sites:** In the "Sites" section, add the other WordPress sites you want to cross-link with.
    *   **Site Name:** A friendly name for the site (e.g., "My Tech Blog").
    *   **Base URL:** The full URL of the site (e.g., `https://my-tech-blog.com`).
    *   **API Key (Optional):** If the other site has protected its API with an API key, enter it here.
4.  Click **Save Changes**.

### 2. Provider Role (Exposing Posts)

Once the plugin is activated, your site will automatically expose the `/wp-json/crosslinker/v1/posts` REST API endpoint. Other sites can use this endpoint to search for posts on your site.

If you have set an API key in the settings, other sites will need to provide this key in the `X-API-KEY` header of their requests.

### 3. Consumer Role (Linking to Other Sites)

When you're editing a post in the Gutenberg editor, you'll have two ways to add cross-site links:

#### Cross-Site Suggestions Sidebar

1.  Open the "Cross-Site Suggestions" sidebar on the right side of the editor.
2.  The sidebar will automatically fetch suggestions from the other sites you've configured based on the title of the post you're currently editing.
3.  To insert a link, click the "Insert" button next to the suggestion. This will add the link to the end of your post.

#### Contextual Link Inserter

1.  Highlight the text you want to link.
2.  Click the "Cross-Site Link" button in the toolbar that appears.
3.  A popover will appear with a search box. Enter a keyword to search for posts on the other sites.
4.  Click the title of the post you want to link to, and the link will be automatically inserted.

## Author

*   Theafolayan

## License

GPL-2.0-or-later
