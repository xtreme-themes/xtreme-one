# README Xtreme Theme

/xtreme-one beinhaltet das Framework
/xtreme-blank beinhaltet das Standard-Childtheme

## Installation

Nach Aktivierung des Childthemes (das Framework selbst wird nicht als Theme aktiviert!) muss unter **Design > Xtreme One** das Theme erst generiert werden. 

## Wiki
Bitte alle Informationen im Wiki ablegen, die die Entwicklung und Anwendung des Xtreme One betreffen.
https://github.com/xtreme-themes/xtreme-one/wiki


# Changelog


## 1.7.1
### Fixes
* #3 Documentation - Fixed German link

## 1.7
### Enhancements
* #188 Xtreme Settings - move "generate Theme"-Button to top of page
* #194 Xtreme Tabs - Added "autoAnchor"-Feature to Tabs

### Fixes
* #213 Xtreme Gallery - Fixed duplicated Image sizes in Gallery-Modal
* #212 Xtreme Carousel-Post-Slider - Fixed broken Anchor-Tag
* #211 Xtreme Overlay - Fixed broken z-index in backend
* #210 Xtreme Slider - Fixed incorrect CSS-Selector to "ym-hideme"
* #209 author.php - Fixed broken `$docmode` for descriptions in template
* #206 Xtreme Gallery - Fixed broken "Link to: none" target for galleries
* #205 Xtreme Grids - Fixed inconsistent grid columns in Column-Posts, Equal-Height-Pages and Equal-Height-Posts
* #203 Xtreme Article-List - Fixed recursion in Article-List


## 1.6.3
### Enhancements
* #199 Xtreme Excerpt - Strip Tags is now optional
* #197 Xtreme-Layout - Templates are now filterable
* #192 Color-Styles - added support for own labels with translations
* #191 Comment-Label is now editable via backend "Xtreme One" -> "Settings"
* #184 Xtreme Post Widget - the current post_id is now excluded from query
* #179 html5.js and xtreme_patch.css now loaded via Conditional Comments in wp_head

### Fixes
* #196 added missing inner-Container in Grid-Widgets
* #193 fixed broken assignment filter 'xtreme-include-core-widgets'
* #190 TinyMCE - smaller bugfix to avoid collisions with other Plugins
* #197 fixed class-name on comment field
* #168 fixed FancyBox display bug
* #183 styling fixes on responsive Forms
* #182 styling fixes for non-html5 installations
* #171 fixed markup for headings on page.php-Template
* #195 fixed xtreme-post-carousel variable assignment

## 1.6.2
### Enhancements
* #176 Added Notice for outdated production-mins.css
* #175 Added Notice for last Theme-generation
* #172 Added Option to show complete Content in Post-Tabber-Widget
* #171 Added `xtreme_post_titles()`-Function to `page.php`-Template
* #166 Added new Value `false` to SyncHeights for Content-Tabs

### Fixes
* #177 Fixed Sub-Title Bug when saving "Draft"-Post
* #174 Sidebar sync heights fix
* #173 Xtreme-Main-Class fixed missing Whitespace on CSS-Class
* #169 Added Fix for Fancybox 1.3.4 Bug
* #167 Added fix for Equal Heights Post-Widget with Sticky Posts

## 1.6.1
* #163 WordPress min-Support is now 3.8
* #161 new XML-Checksum for install-config.xml
* #160 TinyMCE4-Support in WP 3.9
* #159 Widget: Xtreme Grid Text - CSS-fix
* #158 Widget: Equal Heights Posts fix
* #157 JavaScript-fixes for Widget-Customizer
* #156 PHP Notice: `Undefined index: responsive` fix
* #155 accessible-tabs aktualisieren + fixes
* #154 superfish update
* #101 xtreme-carousel-post-slider optimized


## 1.6

* Added WooCommerce-Support
* Yaml4 4.1.2 support
* Added support for responsive 
* Title-Order for Xtreme Post/Page Grid Widget
* Own Color-Style-Settings for Child-Themes
* Optimized Performance
* Removed CSS and JS Performance-Settings and implemented Best Practice
* Subtitle and Title Position
* New Xtreme_Sidebar ID's
* 45 new and optimized Hooks. See more Informations in [Documentation](https://github.com/xtreme-themes/xtreme-one/wik)
* Over 40 fixed Bugs and more than 1000 Commits

__Replaced YAML4 grid classes #104__

* c80l to ym-g80 ym-gl
* c80r to ym-g80 ym-gr
* c75l to ym-g75 ym-gl
* c75r to ym-g75 ym-gr
* c66l to ym-g66 ym-gl
* c66r to ym-g66 ym-gr
* c62l to ym-g62 ym-gl
* c62r to ym-g62 ym-gr
* c60l to ym-g60 ym-gl
* c60r to ym-g60 ym-gr
* c50l to ym-g50 ym-gl
* c50r to ym-g50 ym-gr
* c40l to ym-g40 ym-gl
* c40r to ym-g40 ym-gr
* c38l to ym-g38 ym-gl
* c38r to ym-g38 ym-gr
* c33l to ym-g33 ym-gl
* c33r to ym-g33 ym-gr
* c25l to ym-g25 ym-gl
* c25r to ym-g25 ym-gr
* c20l to ym-g20 ym-gl
* c20r to ym-g20 ym-gr
  
__replaced classes__

* skip with ym-skip
* hlist with ym-hlist
* ie_clearing with ym-ie-clearing
* col3_content with ym-cbox
* col2_content with ym-cbox-right
* col1_content with ym-cbox-left
* col3 with ym-col3
* col2 with ym-col2
* col1 with ym-col1
* subcolumns with ym-grid
* yamlpage with ym-wbox
* grid classes in Xtreme Grid Pages Widget
* grid-classes in Xtreme Grid Posts Widget
 
__new css-classes__

* add class to copyright p tag
  
__other changes__

* added html5shiv.js for IE Support 
* Merge Master und Bugfixrelease Branch
* [Yaml Changelog](https://github.com/yamlcss/yaml/blob/master/changelog.md)
* Fix PHP Scrict Standard Notes
 

## 1.5.5
* Merge Master und Bugfixrelease Branch
* [Yaml Changelog](https://github.com/yamlcss/yaml/blob/master/changelog.md)
* Update auf YAML 4.1.2
* Fix PHP Scrict Standard Notes


## 1.5.4
 * Advanced Gallery Shortcode didn´t work
 * Accessible Post Tabber produced a too big gap
 * Xtreme Media Carousel – gap to footer was to big
 * Twitter widget by changing of the defect API
 * Xtreme jqFancy Slider didn´t show images, if only excerpt was selected
 * Xtreme didn´t find htaccess for browser caching, when WordPress is installed in subfolder

## 1.5.3
 * Codebasis auf WordPres 3.5 angepasst
 * Autoupdater integriert
 * Eigener Menüpunkt im Dashboard integriert
 * Dynamische Dokumentation integriert

## 1.5.2
 * Übernahme des Framework von der dynamic internet GmbH
