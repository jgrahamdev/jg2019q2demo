/**
 * @file df_tools_editor.js
 *
 */

(function ($, CKEDITOR) {

  "use strict";

  CKEDITOR.plugins.add('df_tools_editor', {

    beforeInit: function (editor) {
      // Fix a bug where empty <i> tags would be stripped.
      CKEDITOR.dtd.$removeEmpty['i'] = false;
      CKEDITOR.dtd.$removeEmpty['span'] = false;
    }

  });

})(jQuery, CKEDITOR);
