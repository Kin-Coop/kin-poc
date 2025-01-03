<?php

namespace Drupal\gutenberg\BlockProcessor;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\gutenberg\GutenbergLibraryManager;
use Drupal\gutenberg\Html\TagProcessor;
use Psr\Log\LoggerInterface;
use Drupal\gutenberg\BlocksLibraryManager;
use Drupal\gutenberg\StyleEngine;

/**
 * Processes Gutenberg blocks with layout support.
 */
class LayoutProcessor implements GutenbergBlockProcessorInterface
{

  /**
   * The Gutenberg logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * The Gutenberg library manager.
   *
   * @var \Drupal\gutenberg\GutenbergLibraryManager
   */
  protected GutenbergLibraryManager $libraryManager;

  /**
   * The Gutenberg blocks library manager.
   *
   * @var \Drupal\gutenberg\BlocksLibraryManager
   */
  protected BlocksLibraryManager $blocksLibraryManager;

  /**
   * Layout definitions, keyed by layout type.
   * 
   * @see https://github.com/WordPress/gutenberg/blob/trunk/lib/block-supports/layout.php
   */
  protected $definitions = array(
    'default'     => array(
      'name'          => 'default',
      'slug'          => 'flow',
      'className'     => 'is-layout-flow',
      'baseStyles'    => array(
        array(
          'selector' => ' > .alignleft',
          'rules'    => array(
            'float'               => 'left',
            'margin-inline-start' => '0',
            'margin-inline-end'   => '2em',
          ),
        ),
        array(
          'selector' => ' > .alignright',
          'rules'    => array(
            'float'               => 'right',
            'margin-inline-start' => '2em',
            'margin-inline-end'   => '0',
          ),
        ),
        array(
          'selector' => ' > .aligncenter',
          'rules'    => array(
            'margin-left'  => 'auto !important',
            'margin-right' => 'auto !important',
          ),
        ),
      ),
      'spacingStyles' => array(
        array(
          'selector' => ' > :first-child:first-child',
          'rules'    => array(
            'margin-block-start' => '0',
          ),
        ),
        array(
          'selector' => ' > :last-child:last-child',
          'rules'    => array(
            'margin-block-end' => '0',
          ),
        ),
        array(
          'selector' => ' > *',
          'rules'    => array(
            'margin-block-start' => null,
            'margin-block-end'   => '0',
          ),
        ),
      ),
    ),
    'constrained' => array(
      'name'          => 'constrained',
      'slug'          => 'constrained',
      'className'     => 'is-layout-constrained',
      'baseStyles'    => array(
        array(
          'selector' => ' > .alignleft',
          'rules'    => array(
            'float'               => 'left',
            'margin-inline-start' => '0',
            'margin-inline-end'   => '2em',
          ),
        ),
        array(
          'selector' => ' > .alignright',
          'rules'    => array(
            'float'               => 'right',
            'margin-inline-start' => '2em',
            'margin-inline-end'   => '0',
          ),
        ),
        array(
          'selector' => ' > .aligncenter',
          'rules'    => array(
            'margin-left'  => 'auto !important',
            'margin-right' => 'auto !important',
          ),
        ),
        array(
          'selector' => ' > :where(:not(.alignleft):not(.alignright):not(.alignfull))',
          'rules'    => array(
            'max-width'    => 'var(--wp--style--global--content-size)',
            'margin-left'  => 'auto !important',
            'margin-right' => 'auto !important',
          ),
        ),
        array(
          'selector' => ' > .alignwide',
          'rules'    => array(
            'max-width' => 'var(--wp--style--global--wide-size)',
          ),
        ),
      ),
      'spacingStyles' => array(
        array(
          'selector' => ' > :first-child:first-child',
          'rules'    => array(
            'margin-block-start' => '0',
          ),
        ),
        array(
          'selector' => ' > :last-child:last-child',
          'rules'    => array(
            'margin-block-end' => '0',
          ),
        ),
        array(
          'selector' => ' > *',
          'rules'    => array(
            'margin-block-start' => null,
            'margin-block-end'   => '0',
          ),
        ),
      ),
    ),
    'flex'        => array(
      'name'          => 'flex',
      'slug'          => 'flex',
      'className'     => 'is-layout-flex',
      'displayMode'   => 'flex',
      'baseStyles'    => array(
        array(
          'selector' => '',
          'rules'    => array(
            'flex-wrap'   => 'wrap',
            'align-items' => 'center',
          ),
        ),
        array(
          'selector' => ' > *',
          'rules'    => array(
            'margin' => '0',
          ),
        ),
      ),
      'spacingStyles' => array(
        array(
          'selector' => '',
          'rules'    => array(
            'gap' => null,
          ),
        ),
      ),
    ),
    'grid'        => array(
      'name'          => 'grid',
      'slug'          => 'grid',
      'className'     => 'is-layout-grid',
      'displayMode'   => 'grid',
      'baseStyles'    => array(
        array(
          'selector' => ' > *',
          'rules'    => array(
            'margin' => '0',
          ),
        ),
      ),
      'spacingStyles' => array(
        array(
          'selector' => '',
          'rules'    => array(
            'gap' => null,
          ),
        ),
      ),
    ),
  );

  /**
   * ReusableBlockProcessor constructor.
   *
   * @param \Drupal\gutenberg\GutenbergLibraryManager $library_manager
   * @param \Drupal\gutenberg\BlocksLibraryManager $blocks_library_manager
   * @param \Psr\Log\LoggerInterface $logger
   *   Gutenberg logger interface.
   */
  public function __construct(
    GutenbergLibraryManager $library_manager,
    BlocksLibraryManager $blocks_library_manager,
    LoggerInterface $logger
  ) {
    $this->libraryManager = $library_manager;
    $this->blocksLibraryManager = $blocks_library_manager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function processBlock(array &$block, &$block_content, RefinableCacheableDependencyInterface $bubbleable_metadata)
  {
    // Get first element of definitions array.
    $active_theme_definitions = $this->libraryManager->getActiveThemeDefinitions();
    $theme_definitions     = isset(reset($active_theme_definitions)['theme-support']) ? reset($active_theme_definitions)['theme-support'] : null;
    $block_type            = $this->blocksLibraryManager->getBlockDefinition($block['blockName']);
    // If not found, return early.
    if (!$block_type) {
      return $block_content;
    }

    // Check if $block_content is a MarkupInterface object.
    if ($block_content instanceof MarkupInterface) {
      $block_content = $block_content->__toString();
    }

    $block_supports_layout = $this->blocksLibraryManager->blockHasSupport($block_type, 'layout');
    $layout_from_parent    = isset($block['attrs']['style']['layout']['selfStretch']) ? $block['attrs']['style']['layout']['selfStretch'] : null;

    if (!$block_supports_layout && !$layout_from_parent) {
      return $block_content;
    }

    $outer_class_names = array();

    if ('fixed' === $layout_from_parent || 'fill' === $layout_from_parent) {
      $container_content_class = $this->getUniqueId('wp-container-content-');

      $child_layout_styles = array();

      if ('fixed' === $layout_from_parent && isset($block['attrs']['style']['layout']['flexSize'])) {
        $child_layout_styles[] = array(
          'selector'     => ".$container_content_class",
          'declarations' => array(
            'flex-basis' => $block['attrs']['style']['layout']['flexSize'],
            'box-sizing' => 'border-box',
          ),
        );
      } elseif ('fill' === $layout_from_parent) {
        $child_layout_styles[] = array(
          'selector'     => ".$container_content_class",
          'declarations' => array(
            'flex-grow' => '1',
          ),
        );
      }

      StyleEngine::gutenberg_style_engine_get_stylesheet_from_css_rules(
        $child_layout_styles,
        array(
          'context'  => 'block-supports',
          'prettify' => false,
        )
      );

      $outer_class_names[] = $container_content_class;
    }

    // Prep the processor for modifying the block output.
    $tagProcessor = new TagProcessor($block_content);
    // $tagProcessor = $tagProcessor($block_content);
    // $tagProcessor->setHtml($block_content);

    // Having no tags implies there are no tags onto which to add class names.
    if (!$tagProcessor->next_tag()) {
      return $block_content;
    }

    /*
     * A block may not support layout but still be affected by a parent block's layout.
     *
     * In these cases add the appropriate class names and then return early; there's
     * no need to investigate on this block whether additional layout constraints apply.
     */
    if (!$block_supports_layout && !empty($outer_class_names)) {
      foreach ($outer_class_names as $class_name) {
        $tagProcessor->add_class($class_name);
      }
      return $tagProcessor->get_updated_html();
    }

    $global_settings = $theme_definitions; // wp_get_global_settings();
    $fallback_layout = isset($block_type['supports']['layout']['default'])
      ? $block_type['supports']['layout']['default']
      : array();
    if (empty($fallback_layout)) {
      $fallback_layout = isset($block_type['supports']['__experimentalLayout']['default'])
        ? $block_type['supports']['__experimentalLayout']['default']
        : array();
    }
    $used_layout = isset($block['attrs']['layout']) ? $block['attrs']['layout'] : $fallback_layout;

    $class_names        = array();
    // $layout_definitions = wp_get_layout_definitions();

    /*
     * Uses an incremental ID that is independent per prefix to make sure that
     * rendering different numbers of blocks doesn't affect the IDs of other
     * blocks. Makes the CSS class names stable across paginations
     * for features like the enhanced pagination of the Query block.
     */
    $container_class = $this->getUniquePrefixedId(
      'wp-container-' . Html::cleanCssIdentifier($block['blockName']) . '-layout-'
    );

    // Set the correct layout type for blocks using legacy content width.
    if (isset($used_layout['inherit']) && $used_layout['inherit'] || isset($used_layout['contentSize']) && $used_layout['contentSize']) {
      $used_layout['type'] = 'constrained';
    }

    $root_padding_aware_alignments = isset($global_settings['useRootPaddingAwareAlignments'])
      ? $global_settings['useRootPaddingAwareAlignments']
      : false;

    if (
      $root_padding_aware_alignments &&
      isset($used_layout['type']) &&
      'constrained' === $used_layout['type']
    ) {
      $class_names[] = 'has-global-padding';
    }

    /*
     * The following section was added to reintroduce a small set of layout classnames that were
     * removed in the 5.9 release (https://github.com/WordPress/gutenberg/issues/38719). It is
     * not intended to provide an extended set of classes to match all block layout attributes
     * here.
     */
    if (!empty($block['attrs']['layout']['orientation'])) {
      $class_names[] = 'is-' . Html::cleanCssIdentifier($block['attrs']['layout']['orientation']);
    }

    if (!empty($block['attrs']['layout']['justifyContent'])) {
      $class_names[] = 'is-content-justification-' . Html::cleanCssIdentifier($block['attrs']['layout']['justifyContent']);
    }

    if (!empty($block['attrs']['layout']['flexWrap']) && 'nowrap' === $block['attrs']['layout']['flexWrap']) {
      $class_names[] = 'is-nowrap';
    }

    // Get classname for layout type.
    if (isset($used_layout['type'])) {
      $layout_classname = isset($this->definitions[$used_layout['type']]['className'])
        ? $this->definitions[$used_layout['type']]['className']
        : '';
    } else {
      $layout_classname = isset($this->definitions['default']['className'])
        ? $this->definitions['default']['className']
        : '';
    }

    if ($layout_classname && is_string($layout_classname)) {
      $class_names[] = Html::cleanCssIdentifier($layout_classname);
    }

    /*
     * Only generate Layout styles if the theme has not opted-out.
     * Attribute-based Layout classnames are output in all cases.
     */
    // if (!current_theme_supports('disable-layout-styles')) {

    $gap_value = isset($block['attrs']['style']['spacing']['blockGap'])
      ? $block['attrs']['style']['spacing']['blockGap']
      : null;
    /*
      * Skip if gap value contains unsupported characters.
      * Regex for CSS value borrowed from `safecss_filter_attr`, and used here
      * to only match against the value, not the CSS attribute.
      */
    if (is_array($gap_value)) {
      foreach ($gap_value as $key => $value) {
        $gap_value[$key] = $value && preg_match('%[\\\(&=}]|/\*%', $value) ? null : $value;
      }
    } else {
      $gap_value = $gap_value && preg_match('%[\\\(&=}]|/\*%', $gap_value) ? null : $gap_value;
    }

    $fallback_gap_value = isset($block_type['supports']['spacing']['blockGap']['__experimentalDefault'])
      ? $block_type['supports']['spacing']['blockGap']['__experimentalDefault']
      : '0.5em';
    $block_spacing      = isset($block['attrs']['style']['spacing'])
      ? $block['attrs']['style']['spacing']
      : null;

    /*
       * If a block's block.json skips serialization for spacing or spacing.blockGap,
       * don't apply the user-defined value to the styles.
       */
    $should_skip_gap_serialization = FALSE; // wp_should_skip_block_supports_serialization($block_type, 'spacing', 'blockGap');

    $block_gap             = isset($global_settings['__experimentalFeatures']['spacing']['blockGap'])
      ? $global_settings['__experimentalFeatures']['spacing']['blockGap']
      : null;
    $has_block_gap_support = isset($block_gap);

    $style = $this->getLayoutStyle(
      ".$container_class", // .$container_class
      $used_layout,
      $has_block_gap_support,
      $gap_value,
      $should_skip_gap_serialization,
      $fallback_gap_value,
      $block_spacing
    );

    // Only add container class and enqueue block support styles if unique styles were generated.
    if (!empty($style)) {
      $class_names[] = $container_class;
    }
    //}

    // Add combined layout and block classname for global styles to hook onto.
    $block_name    = explode('/', $block['blockName']);
    $class_names[] = 'wp-block-' . end($block_name) . '-' . $layout_classname;

    // Add classes to the outermost HTML tag if necessary.
    if (!empty($outer_class_names)) {
      foreach ($outer_class_names as $outer_class_name) {
        $tagProcessor->add_class($outer_class_name);
      }
    }

    /**
     * Attempts to refer to the inner-block wrapping element by its class attribute.
     *
     * When examining a block's inner content, if a block has inner blocks, then
     * the first content item will likely be a text (HTML) chunk immediately
     * preceding the inner blocks. The last HTML tag in that chunk would then be
     * an opening tag for an element that wraps the inner blocks.
     *
     * There's no reliable way to associate this wrapper in $block_content because
     * it may have changed during the rendering pipeline (as inner contents is
     * provided before rendering) and through previous filters. In many cases,
     * however, the `class` attribute will be a good-enough identifier, so this
     * code finds the last tag in that chunk and stores the `class` attribute
     * so that it can be used later when working through the rendered block output
     * to identify the wrapping element and add the remaining class names to it.
     *
     * It's also possible that no inner block wrapper even exists. If that's the
     * case this code could apply the class names to an invalid element.
     *
     * Example:
     *
     *     $block['innerBlocks']  = array( $list_item );
     *     $block['innerContent'] = array( '<ul class="list-wrapper is-unordered">', null, '</ul>' );
     *
     *     // After rendering, the initial contents may have been modified by other renderers or filters.
     *     $block_content = <<<HTML
     *         <figure>
     *             <ul class="annotated-list list-wrapper is-unordered">
     *                 <li>Code</li>
     *             </ul><figcaption>It's a list!</figcaption>
     *         </figure>
     *     HTML;
     *
     * Although it is possible that the original block-wrapper classes are changed in $block_content
     * from how they appear in $block['innerContent'], it's likely that the original class attributes
     * are still present in the wrapper as they are in this example. Frequently, additional classes
     * will also be present; rarely should classes be removed.
     *
     * @TODO: Find a better way to match the first inner block. If it's possible to identify where the
     *        first inner block starts, then it will be possible to find the last tag before it starts
     *        and then that tag, if an opening tag, can be solidly identified as a wrapping element.
     *        Can some unique value or class or ID be added to the inner blocks when they process
     *        so that they can be extracted here safely without guessing? Can the block rendering function
     *        return information about where the rendered inner blocks start?
     *
     * @var string|null
     */
    $inner_block_wrapper_classes = null;
    $first_chunk                 = isset($block['innerContent'][0]) ? $block['innerContent'][0] : null;
    if (is_string($first_chunk) && count($block['innerContent']) > 1) {
      $first_chunk_processor = new TagProcessor($first_chunk);
      while ($first_chunk_processor->next_tag()) {
        $class_attribute = $first_chunk_processor->get_attribute('class');
        if (is_string($class_attribute) && !empty($class_attribute)) {
          $inner_block_wrapper_classes = $class_attribute;
        }
      }
    }

    /*
     * If necessary, advance to what is likely to be an inner block wrapper tag.
     *
     * This advances until it finds the first tag containing the original class
     * attribute from above. If none is found it will scan to the end of the block
     * and fail to add any class names.
     *
     * If there is no block wrapper it won't advance at all, in which case the
     * class names will be added to the first and outermost tag of the block.
     * For cases where this outermost tag is the only tag surrounding inner
     * blocks then the outer wrapper and inner wrapper are the same.
     */
    do {
      if (!$inner_block_wrapper_classes) {
        break;
      }
      if(null !==($tagProcessor->get_attribute('class'))){
        break;
      }
      if (false !== strpos($tagProcessor->get_attribute('class'), $inner_block_wrapper_classes)) {
        break;
      }
    } while ($tagProcessor->next_tag());

    // Add the remaining class names.
    foreach ($class_names as $class_name) {
      $tagProcessor->add_class($class_name);
    }

    $block_content = $tagProcessor->get_updated_html();

    $block_content = $this->restoreGroupInnerContainer($block_content, $block);

    return $block_content;
  }

  /**
   * Gets unique ID.
   *
   * This is a PHP implementation of Underscore's uniqueId method. A static variable
   * contains an integer that is incremented with each call. This number is returned
   * with the optional prefix. As such the returned value is not universally unique,
   * but it is unique across the life of the PHP process.
   *
   * @param string $prefix Prefix for the returned ID.
   * @return string Unique ID.
   */
  public function getUniqueId($prefix = '')
  {
    static $id_counter = 0;
    return $prefix . (string) ++$id_counter;
  }

  /**
   * Generates an incremental ID that is independent per each different prefix.
   *
   * It is similar to `wp_unique_id`, but each prefix has its own internal ID
   * counter to make each prefix independent from each other. The ID starts at 1
   * and increments on each call. The returned value is not universally unique,
   * but it is unique across the life of the PHP process and it's stable per
   * prefix.
   *
   * @since 6.4.0
   *
   * @param string $prefix Optional. Prefix for the returned ID. Default empty string.
   * @return string Incremental ID per prefix.
   */
  function getUniquePrefixedId($prefix = '')
  {
    static $id_counters = array();

    if (!is_string($prefix)) {
      throw new \InvalidArgumentException(
        sprintf('The prefix must be a string. "%s" data type given.', gettype($prefix))
      );
      $prefix = '';
    }

    if (!isset($id_counters[$prefix])) {
      $id_counters[$prefix] = 0;
    }

    $id = ++$id_counters[$prefix];

    return $prefix . (string) $id;
  }

  /**
   * Generates the CSS corresponding to the provided layout.
   *
   * @since 5.9.0
   * @since 6.1.0 Added `$block_spacing` param, use style engine to enqueue styles.
   * @since 6.3.0 Added grid layout type.
   * @access private
   *
   * @param string               $selector                      CSS selector.
   * @param array                $layout                        Layout object. The one that is passed has already checked
   *                                                            the existence of default block layout.
   * @param bool                 $has_block_gap_support         Optional. Whether the theme has support for the block gap. Default false.
   * @param string|string[]|null $gap_value                     Optional. The block gap value to apply. Default null.
   * @param bool                 $should_skip_gap_serialization Optional. Whether to skip applying the user-defined value set in the editor. Default false.
   * @param string               $fallback_gap_value            Optional. The block gap value to apply. Default '0.5em'.
   * @param array|null           $block_spacing                 Optional. Custom spacing set on the block. Default null.
   * @return string CSS styles on success. Else, empty string.
   */
  protected function getLayoutStyle($selector, $layout, $has_block_gap_support = false, $gap_value = null, $should_skip_gap_serialization = false, $fallback_gap_value = '0.5em', $block_spacing = null)
  {
    $layout_type   = isset($layout['type']) ? $layout['type'] : 'default';
    $layout_styles = array();

    if ('default' === $layout_type) {
      if ($has_block_gap_support) {
        if (is_array($gap_value)) {
          $gap_value = isset($gap_value['top']) ? $gap_value['top'] : null;
        }
        if (null !== $gap_value && !$should_skip_gap_serialization) {
          // Get spacing CSS variable from preset value if provided.
          if (is_string($gap_value) && str_contains($gap_value, 'var:preset|spacing|')) {
            $index_to_splice = strrpos($gap_value, '|') + 1;
            $slug            = substr($gap_value, $index_to_splice); // Html::cleanCssIdentifier(substr($gap_value, $index_to_splice));
            $gap_value       = "var(--wp--preset--spacing--$slug)";
          }

          array_push(
            $layout_styles,
            array(
              'selector'     => "$selector > *",
              'declarations' => array(
                'margin-block-start' => '0',
                'margin-block-end'   => '0',
              ),
            ),
            array(
              'selector'     => "$selector$selector > * + *",
              'declarations' => array(
                'margin-block-start' => $gap_value,
                'margin-block-end'   => '0',
              ),
            )
          );
        }
      }
    } elseif ('constrained' === $layout_type) {
      $content_size    = isset($layout['contentSize']) ? $layout['contentSize'] : '';
      $wide_size       = isset($layout['wideSize']) ? $layout['wideSize'] : '';
      $justify_content = isset($layout['justifyContent']) ? $layout['justifyContent'] : 'center';

      $all_max_width_value  = $content_size ? $content_size : $wide_size;
      $wide_max_width_value = $wide_size ? $wide_size : $content_size;

      // Make sure there is a single CSS rule, and all tags are stripped for security.
      $all_max_width_value  = Xss::filter(explode(';', $all_max_width_value)[0]);
      $wide_max_width_value = Xss::filter(explode(';', $wide_max_width_value)[0]);

      $margin_left  = 'left' === $justify_content ? '0 !important' : 'auto !important';
      $margin_right = 'right' === $justify_content ? '0 !important' : 'auto !important';

      if ($content_size || $wide_size) {
        array_push(
          $layout_styles,
          array(
            'selector'     => "$selector > :where(:not(.alignleft):not(.alignright):not(.alignfull))",
            'declarations' => array(
              'max-width'    => $all_max_width_value,
              'margin-left'  => $margin_left,
              'margin-right' => $margin_right,
            ),
          ),
          array(
            'selector'     => "$selector > .alignwide",
            'declarations' => array('max-width' => $wide_max_width_value),
          ),
          array(
            'selector'     => "$selector .alignfull",
            'declarations' => array('max-width' => 'none'),
          )
        );

        if (isset($block_spacing)) {
          $block_spacing_values = StyleEngine::gutenberg_style_engine_get_styles(
            array(
              'spacing' => $block_spacing,
            )
          );

          /**
           * Handle negative margins for alignfull children of blocks with custom padding set.
           * They're added separately because padding might only be set on one side.
           */
          if (isset($block_spacing_values['declarations']['padding-right'])) {
            $padding_right   = $block_spacing_values['declarations']['padding-right'];
            $layout_styles[] = array(
              'selector'     => "$selector > .alignfull",
              'declarations' => array('margin-right' => "calc($padding_right * -1)"),
            );
          }
          if (isset($block_spacing_values['declarations']['padding-left'])) {
            $padding_left    = $block_spacing_values['declarations']['padding-left'];
            $layout_styles[] = array(
              'selector'     => "$selector > .alignfull",
              'declarations' => array('margin-left' => "calc($padding_left * -1)"),
            );
          }
        }
      }

      if ('left' === $justify_content) {
        $layout_styles[] = array(
          'selector'     => "$selector > :where(:not(.alignleft):not(.alignright):not(.alignfull))",
          'declarations' => array('margin-left' => '0 !important'),
        );
      }

      if ('right' === $justify_content) {
        $layout_styles[] = array(
          'selector'     => "$selector > :where(:not(.alignleft):not(.alignright):not(.alignfull))",
          'declarations' => array('margin-right' => '0 !important'),
        );
      }

      if ($has_block_gap_support) {
        if (is_array($gap_value)) {
          $gap_value = isset($gap_value['top']) ? $gap_value['top'] : null;
        }
        if (null !== $gap_value && !$should_skip_gap_serialization) {
          // Get spacing CSS variable from preset value if provided.
          if (is_string($gap_value) && str_contains($gap_value, 'var:preset|spacing|')) {
            $index_to_splice = strrpos($gap_value, '|') + 1;
            $slug            = substr($gap_value, $index_to_splice); // Html::cleanCssIdentifier(substr($gap_value, $index_to_splice));
            $gap_value       = "var(--wp--preset--spacing--$slug)";
          }

          array_push(
            $layout_styles,
            array(
              'selector'     => "$selector > *",
              'declarations' => array(
                'margin-block-start' => '0',
                'margin-block-end'   => '0',
              ),
            ),
            array(
              'selector'     => "$selector$selector > * + *",
              'declarations' => array(
                'margin-block-start' => $gap_value,
                'margin-block-end'   => '0',
              ),
            )
          );
        }
      }
    } elseif ('flex' === $layout_type) {
      $layout_orientation = isset($layout['orientation']) ? $layout['orientation'] : 'horizontal';

      $justify_content_options = array(
        'left'   => 'flex-start',
        'right'  => 'flex-end',
        'center' => 'center',
      );

      $vertical_alignment_options = array(
        'top'    => 'flex-start',
        'center' => 'center',
        'bottom' => 'flex-end',
      );

      if ('horizontal' === $layout_orientation) {
        $justify_content_options    += array('space-between' => 'space-between');
        $vertical_alignment_options += array('stretch' => 'stretch');
      } else {
        $justify_content_options    += array('stretch' => 'stretch');
        $vertical_alignment_options += array('space-between' => 'space-between');
      }

      if (!empty($layout['flexWrap']) && 'nowrap' === $layout['flexWrap']) {
        $layout_styles[] = array(
          'selector'     => $selector,
          'declarations' => array('flex-wrap' => 'nowrap'),
        );
      }

      if ($has_block_gap_support && isset($gap_value)) {
        $combined_gap_value = '';
        $gap_sides          = is_array($gap_value) ? array('top', 'left') : array('top');

        foreach ($gap_sides as $gap_side) {
          $process_value = $gap_value;
          if (is_array($gap_value)) {
            $process_value = isset($gap_value[$gap_side]) ? $gap_value[$gap_side] : $fallback_gap_value;
          }
          // Get spacing CSS variable from preset value if provided.
          if (is_string($process_value) && str_contains($process_value, 'var:preset|spacing|')) {
            $index_to_splice = strrpos($process_value, '|') + 1;
            $slug            = substr($process_value, $index_to_splice); // Html::cleanCssIdentifier(substr($process_value, $index_to_splice));
            $process_value   = "var(--wp--preset--spacing--$slug)";
          }
          $combined_gap_value .= "$process_value ";
        }
        $gap_value = trim($combined_gap_value);

        if (null !== $gap_value && !$should_skip_gap_serialization) {
          $layout_styles[] = array(
            'selector'     => $selector,
            'declarations' => array('gap' => $gap_value),
          );
        }
      }

      if ('horizontal' === $layout_orientation) {
        /*
			 * Add this style only if is not empty for backwards compatibility,
			 * since we intend to convert blocks that had flex layout implemented
			 * by custom css.
			 */
        if (!empty($layout['justifyContent']) && array_key_exists($layout['justifyContent'], $justify_content_options)) {
          $layout_styles[] = array(
            'selector'     => $selector,
            'declarations' => array('justify-content' => $justify_content_options[$layout['justifyContent']]),
          );
        }

        if (!empty($layout['verticalAlignment']) && array_key_exists($layout['verticalAlignment'], $vertical_alignment_options)) {
          $layout_styles[] = array(
            'selector'     => $selector,
            'declarations' => array('align-items' => $vertical_alignment_options[$layout['verticalAlignment']]),
          );
        }
      } else {
        $layout_styles[] = array(
          'selector'     => $selector,
          'declarations' => array('flex-direction' => 'column'),
        );
        if (!empty($layout['justifyContent']) && array_key_exists($layout['justifyContent'], $justify_content_options)) {
          $layout_styles[] = array(
            'selector'     => $selector,
            'declarations' => array('align-items' => $justify_content_options[$layout['justifyContent']]),
          );
        } else {
          $layout_styles[] = array(
            'selector'     => $selector,
            'declarations' => array('align-items' => 'flex-start'),
          );
        }
        if (!empty($layout['verticalAlignment']) && array_key_exists($layout['verticalAlignment'], $vertical_alignment_options)) {
          $layout_styles[] = array(
            'selector'     => $selector,
            'declarations' => array('justify-content' => $vertical_alignment_options[$layout['verticalAlignment']]),
          );
        }
      }
    } elseif ('grid' === $layout_type) {
      if (!empty($layout['columnCount'])) {
        $layout_styles[] = array(
          'selector'     => $selector,
          'declarations' => array('grid-template-columns' => 'repeat(' . $layout['columnCount'] . ', minmax(0, 1fr))'),
        );
      } else {
        $minimum_column_width = !empty($layout['minimumColumnWidth']) ? $layout['minimumColumnWidth'] : '12rem';

        $layout_styles[] = array(
          'selector'     => $selector,
          'declarations' => array('grid-template-columns' => 'repeat(auto-fill, minmax(min(' . $minimum_column_width . ', 100%), 1fr))'),
        );
      }

      if ($has_block_gap_support && isset($gap_value)) {
        $combined_gap_value = '';
        $gap_sides          = is_array($gap_value) ? array('top', 'left') : array('top');

        foreach ($gap_sides as $gap_side) {
          $process_value = $gap_value;
          if (is_array($gap_value)) {
            $process_value = isset($gap_value[$gap_side]) ? $gap_value[$gap_side] : $fallback_gap_value;
          }
          // Get spacing CSS variable from preset value if provided.
          if (is_string($process_value) && str_contains($process_value, 'var:preset|spacing|')) {
            $index_to_splice = strrpos($process_value, '|') + 1;
            $slug            = substr($process_value, $index_to_splice); // Html::cleanCssIdentifier(substr($process_value, $index_to_splice));
            $process_value   = "var(--wp--preset--spacing--$slug)";
          }
          $combined_gap_value .= "$process_value ";
        }
        $gap_value = trim($combined_gap_value);

        if (null !== $gap_value && !$should_skip_gap_serialization) {
          $layout_styles[] = array(
            'selector'     => $selector,
            'declarations' => array('gap' => $gap_value),
          );
        }
      }
    }

    if (!empty($layout_styles)) {

      /**
       * @todo:
       * Add to the style engine store to enqueue and render layout styles.
       * Return compiled layout styles to retain backwards compatibility.
       * Since https://github.com/WordPress/gutenberg/pull/42452,
       * wp_enqueue_block_support_styles is no longer called in this block supports file.
       */
      return StyleEngine::gutenberg_style_engine_get_stylesheet_from_css_rules(
        $layout_styles,
        array(
          'context'  => 'block-supports',
          'prettify' => false,
        )
      );
    }

    return '';
  }

  /**
   * For themes without theme.json file, make sure
   * to restore the inner div for the group block
   * to avoid breaking styles relying on that div.
   *
   * @param  string $block_content Rendered block content.
   * @param  array  $block         Block object.
   * @return string                Filtered block content.
   */
  function restoreGroupInnerContainer($block_content, $block)
  {
    $tag_name                         = isset($block['attrs']['tagName']) ? $block['attrs']['tagName'] : 'div';
    $group_with_inner_container_regex = sprintf(
      '/(^\s*<%1$s\b[^>]*wp-block-group(\s|")[^>]*>)(\s*<div\b[^>]*wp-block-group__inner-container(\s|")[^>]*>)((.|\S|\s)*)/U',
      preg_quote($tag_name, '/')
    );
    if (
      // wp_theme_has_theme_json() ||
      1 === preg_match($group_with_inner_container_regex, $block_content) ||
      (isset($block['attrs']['layout']['type']) && ('flex' === $block['attrs']['layout']['type'] || 'grid' === $block['attrs']['layout']['type']))
    ) {
      return $block_content;
    }

    $replace_regex   = sprintf(
      '/(^\s*<%1$s\b[^>]*wp-block-group[^>]*>)(.*)(<\/%1$s>\s*$)/ms',
      preg_quote($tag_name, '/')
    );
    $updated_content = preg_replace_callback(
      $replace_regex,
      static function ($matches) {
        return $matches[1] . '<div class="wp-block-group__inner-container">' . $matches[2] . '</div>' . $matches[3];
      },
      $block_content
    );
    return $updated_content;
  }

  /**
   * {@inheritdoc}
   */
  public function isSupported(array $block, $block_content = '')
  {
    return isset($block['attrs']['layout']) || isset($block['attrs']['style']);
  }
}
