<?php
$tag_name = is_array($args) ? ($args['name'] ?? '') : (is_string($args) ? $args : '');
if (empty($tag_name)) {
    return;
}
?>
<span class="bg-white text-secondary pill-tag"><?php echo esc_html($tag_name); ?></span>