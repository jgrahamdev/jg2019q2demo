<?php

/**
 * @file
 * Lists available colors and color schemes for the OBIO theme.
 */

$info = array(
  // Available colors and color labels used in theme.
  'fields' => array(
    'base' => t('Body background (base)'),
    'logocolor' => t('Logo text'),
    'text' => t('Body foreground (text)'),
    'link' => t('Primary color (highlights and links)'),
    'linkrel' => t('Primary related color (button text)'),
    'linkhover' => t('Primary hover color'),
    'linkcolor' => t('General primary color'),
    'linkcolorhover' => t('General primary hover color'),
    'headerbg' => t('Header background (left side)'),
    'headerbgmeta' => t('Header background (right side)'),
    'headercolor' => t('Header foreground'),
    'headerlinkcolor' => t('Header link default color'),
    'headerlinkcolorhover' => t('Header link active color'),
    'footerbg' => t('Footer background'),
    'footercolor' => t('Footer foreground'),
    'footerlinkcolor' => t('Footer link default color'),
    'footerlinkcolorhover' => t('Footer link active color'),
    'cardbg' => t('Card background'),
    'cardcolor' => t('Card foreground'),
    'cardlinkcolor' => t('Card link color'),
    'cardlinkcolorhover' => t('Card link hover color'),
    'label' => t('Label (background color)'),
  ),
  // Define "color_placeholders" key if you would use color placeholders instead
  // of explicitly defined colors.
  //
  // If the calculated placeholders are fine for you, it's enought to set this
  // key to TRUE.
  //
  // In that case, field (and color) defined above as headerbg would have a
  // placeholder "#__headerbg__". That should be used in your template CSS file.
  //
  // If you want to customize placeholders, you have to define
  // "color_placeholders" as an array. Keys should match the field (color)
  // key, and it's value should set to the needed placeholder string.
  //
  // These custom placeholders also will get the needed prefix and suffix,
  // and will be processed by HTML::cleanCssIdentifier.
  //
  // Example:
  // @code
  //'headerbg' => 'header-bg', // Placeholder in the CSS: #__header-bg__
  // @endcode
  'color_placeholders' => [
    'label' => 'label-bg',
    'base' => 'body-bg',
    'logocolor' => 'logocolor',
    'text' => 'body-color',
    'link' => 'primary',
    'linkrel' => 'primary-rel',
    'linkhover' => 'primary-hover',
    'headerbg' => 'header-bg',
    'headerbgmeta' => 'header-bg-meta',
    'headercolor' => 'header-color',
    'headerlinkcolor' => 'header-link-color',
    'headerlinkcolorhover' => 'header-link-color-hover',
    'cardbg' => 'card-bg',
    'cardcolor' => 'card-color',
    'cardlinkcolor' => 'card-link-color',
    'cardlinkcolorhover' => 'card-link-color-hover',
    'footerbg' => 'footer-bg',
    'footercolor' => 'footer-color',
    'footerlinkcolor' => 'footer-link-color',
    'footerlinkcolorhover' => 'footer-link-color-hover',
  ],
  // Pre-defined color schemes.
  //
  // Color module will try to shift non-defined colors found in the CSS based on
  // the determined color type (base|text|link). These are detected partially
  // based on the selector (if seletor contains "a", those colors are "link"
  // colors) and based on the css property (preceeding "color:" property means
  // "text" type if the selector doesn't contain "a"). Base is the fallback
  // type.
  //
  // So if you don't define EVERY used CSS color, colors 'base', 'text' and
  // 'link' should be defined to keep the shifting to be functional.
  // These colors would act as the base of the unexpressed theme colors. If you
  // don't do that, Color module will throw errors doing the color shifting.
  //
  // Because of this behavior, color_placeholders used in the CSS file always
  // have to be defined.
  'schemes' => array(
    'default' => array(
      'title' => t('Obio (default)'),
      'colors' => array(
        'base' => '#ffffff',
        'text' => '#0a0a0a',
        'link' => '#3366cc',
        'logocolor' => '#3366cc',
        'linkrel' => '#ffffff',
        'linkhover' => '#2c58af',
        'linkcolor' => '#36c',
        'linkcolorhover' => '#2c58af',
        'headerbg' => '#f2f2fa',
        'headerbgmeta' => '#010101',
        'headercolor' => '#353535',
        'headerlinkcolor' => '#353535',
        'headerlinkcolorhover' => '#399bc9',
        'cardbg' => '#f2f2fa',
        'cardcolor' => '#0a0a0a',
        'cardlinkcolor' => '#3366cc',
        'cardlinkcolorhover' => '#2c58af',
        'footerbg' => '#010101',
        'footercolor' => '#ffffff',
        'footerlinkcolor' => '#ffffff',
        'footerlinkcolorhover' => '#399bc9',
        'label' => '#2199e8',
      ),
    ),
    'obio_brown' => array(
      'title' => t('Obio brown'),
      'colors' => array(
        'base' => '#f2f2fa',
        'text' => '#0a0a0a',
        'logocolor' => '#cb7f34',
        'link' => '#cb7f34',
        'linkrel' => '#ffffff',
        'linkhover' => '#945d26',
        'linkcolor' => '#cb7f34',
        'linkcolorhover' => '#945d26',
        'headerbg' => '#ffffff',
        'headerbgmeta' => '#cb7f34',
        'headercolor' => '#353535',
        'headerlinkcolor' => '#353535',
        'headerlinkcolorhover' => '#e2a971',
        'cardbg' => '#ffffff',
        'cardcolor' => '#0a0a0a',
        'cardlinkcolor' => '#cb7f34',
        'cardlinkcolorhover' => '#945d26',
        'footerbg' => '#010101',
        'footercolor' => '#ffffff',
        'footerlinkcolor' => '#ffffff',
        'footerlinkcolorhover' => '#e2a971',
        'label' => '#cb7f34',
      ),
    ),
    'obio_alt' => array(
      'title' => t('Obio alternate'),
      'colors' => array(
        'headerbg' => '#ffffff',
        'headerbgmeta' => '#3366cc',
        'headercolor' => '#009688',
        'headerlinkcolor' => '#3366cc',
        'headerlinkcolorhover' => '#00796B',
        'cardbg' => '#ffffff',
        'cardcolor' => '#0a0a0a',
        'cardlinkcolor' => '#3366cc',
        'cardlinkcolorhover' => '#254077',
        'footerbg' => '#010101',
        'footercolor' => '#ffffff',
        'footerlinkcolor' => '#ffffff',
        'footerlinkcolorhover' => '#79a5fe',
        'text' => '#0a0a0a',
        'link' => '#3366cc',
        'logocolor' => '#3366cc',
        'linkrel' => '#ffffff',
        'linkhover' => '#254077',
        'linkcolor' => '#3366cc',
        'linkcolorhover' => '#254077',
        'base' => '#f2f2fa',
        'label' => '#3366cc',
      ),
    ),
      'obio_showroom_bos' => array(
      'title' => t('Obio Boston Showroom'),
      'colors' => array(
        'headerbg' => '#f6fbfb',
        'headerbgmeta' => '#009688',
        'headercolor' => '#353535',
        'headerlinkcolor' => '#353535',
        'headerlinkcolorhover' => '#00796B',
        'cardbg' => '#f6fbfb',
        'cardcolor' => '#0a0a0a',
        'cardlinkcolor' => '#009688',
        'cardlinkcolorhover' => '#00796B',
        'footerbg' => '#010101',
        'footercolor' => '#ffffff',
        'footerlinkcolor' => '#ffffff',
        'footerlinkcolorhover' => '#00796B',
        'text' => '#0a0a0a',
        'link' => '#009688',
        'logocolor' => '#009688',
        'linkrel' => '#ffffff',
        'linkhover' => '#00796B',
        'linkcolor' => '#009688',
        'linkcolorhover' => '#00796B',
        'base' => '#ffffff',
        'label' => '#009688',
      ),
    ),
  ),

  // CSS files (excluding CSS @imports) to rewrite with new color scheme.
  'css' => array(
    'css/obio-colors.css',
  ),

  // Files to copy.
  'copy' => array(
    'logo.svg',
  ),

  // //Preview files.
  'preview_library' => 'obio/color.preview',
  'preview_html' => 'color/preview.html',

  // Attachments.
  '#attached' => [
    'drupalSettings' => [
      'color' => [
        // Put the logo path into JavaScript for the live preview.
        'logo' => theme_get_setting('logo.url', 'obio'),
      ],
    ],
  ],
);
