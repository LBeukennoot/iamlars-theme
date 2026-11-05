| Function | Description |
| --------- | ---------------|
|`get_header()`	| Loads your `header.php` template. This prints `<!DOCTYPE html>`, `<head>`, `<?php wp_head(); ?>`, and the opening `<body>` tag. Tailwind classes on the `<body>` tag (like background colors and default fonts) are defined there. |
|`have_posts()`	| Checks whether the current database query has any posts or pages to display (returns true or false). |
|`the_post()`	|Advances WordPress's internal post counter and sets up the global post data so that functions like `the_title()` know which specific post they are referencing. |
|`the_permalink()`	|Echoes the direct URL link to the individual post. |
|`the_title()`	|Echoes the title of the current post inside the loop. |
|`the_excerpt()`	|Outputs an automated short summary (approx. 55 words) of the post content wrapped in a `<p>` tag. (If you want the full body text instead, you replace this with `the_content()`). |
|`get_footer()`	|Loads `footer.php`, which contains footer markup, `<?php wp_footer(); ?>` (where WordPress enqueues scripts/Tailwind output), and closing tags. |


