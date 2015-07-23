<?php

function redefine_pagenow($template) {
    if ( preg_match('#([^/]+\.php)([?/].*?)?$#i', $template, $self_matches) ) {
        return $tpl = strtolower($self_matches[1]);
    } else {
        return $tpl = 'index.php';
    }
}
function xtreme_get_template() {
    global $wp;
    if ( defined('WP_USE_THEMES') && constant('WP_USE_THEMES') ) {
        if ( is_404() && $template = get_404_template() ) {
            return redefine_pagenow($template);
        } elseif ( is_search() && $template = get_search_template() ) {
            return redefine_pagenow($template);
        } elseif ( is_tax() && $template = get_taxonomy_template()) {
            return redefine_pagenow($template);
        } elseif ( is_front_page() && $template = get_front_page_template() ) {
            return redefine_pagenow($template);
        } elseif ( is_home() && $template = get_home_template() ) {
            return redefine_pagenow($template);
        } elseif ( is_attachment() && $template = get_attachment_template() ) {
            return redefine_pagenow($template);
        } elseif ( is_single() && $template = get_single_template() ) {
            return redefine_pagenow($template);
        } elseif ( is_page() && $template = get_page_template() ) {
            return redefine_pagenow($template);
        } elseif ( is_category() && $template = get_category_template()) {
            return redefine_pagenow($template);
        } elseif ( is_tag() && $template = get_tag_template()) {
            return redefine_pagenow($template);
        } elseif ( is_author() && $template = get_author_template() ) {
            return redefine_pagenow($template);
        } elseif ( is_date() && $template = get_date_template() ) {
            return redefine_pagenow($template);
        } elseif ( is_archive() && $template = get_archive_template() ) {
            return redefine_pagenow($template);
        } elseif ( is_comments_popup() && $template = get_comments_popup_template() ) {
            return redefine_pagenow($template);
        } elseif ( is_paged() && $template = get_paged_template() ) {
            return redefine_pagenow($template);
        } else {
            $template = get_index_template();
            return redefine_pagenow($template);
        }
    }
}

function xtreme_locate_file_from_dir($template_names) {
    if ( !is_array($template_names) )
        return '';

    $located = '';
    foreach ( $template_names as $template_name ) {
        if ( file_exists(XF_CHILD_THEME_DIR . '/' . $template_name)) {
            $located = XF_CHILD_THEME_DIR . '/' . $template_name;
            break;
        } elseif ( file_exists(XF_THEME_DIR . '/' . $template_name) ) {
            $located = XF_THEME_DIR . '/' . $template_name;
            break;
        }
    }

    if ( '' != $located ) {
        return $located;
    } else {
        return false;
    }
}

function xtreme_locate_file_from_uri($template_names) {
    if ( !is_array($template_names) )
        return '';

    $located = '';
    foreach ( $template_names as $template_name ) {
        if ( file_exists(XF_CHILD_THEME_DIR . '/' . $template_name)) {
            $located = XF_CHILD_THEME_URI . '/' . $template_name;
            break;
        } else if ( file_exists(XF_THEME_DIR . '/' . $template_name) ) {
            $located = XF_THEME_URI . '/' . $template_name;
            break;
        }
    }

    if ( '' != $located ) {
        return $located;
    } else {
        return false;
    }
}
