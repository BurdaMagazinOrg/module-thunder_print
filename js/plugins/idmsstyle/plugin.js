(function (CKEDITOR, drupalSettings) {
  'use strict';

  CKEDITOR.plugins.add('thunder_print_idmsstyle', {
    requires: ['stylescombo'],

    /**
     * Initializes the plugin.
     *
     * @param {CKEDITOR.editor} editor
     */
    init: function (editor) {
      var fieldStyles = getStylesDefinitionForElement(editor.element, drupalSettings);

      // Do not alter the styles, if there are no IDMS style informations set.
      if (typeof fieldStyles == 'undefined') {
        return;
      }

      editor.on('stylesSet', function (evt) {
        // Do not initialize the styles data
        if (typeof evt.data.idms === 'undefined') {
          evt.cancel();

          // Reset styles combo styles.
          editor.ui.items.Styles.reset();

          editor.fire('stylesSet', {styles: fieldStyles, idms: true});
        }
      });

      editor.on('change', function (evt) {
        var ps = editor.document.$.getElementsByTagName('p');
        for (var i = 0; i < ps.length; i++) {
          if (!ps[i].className) {
            for (var j = 0; j < fieldStyles.length; j++) {
              var style = fieldStyles[j];
              if (style.element === 'p') {
                ps[i].className += style.attributes.class;
                break;
              }
            }
          }
        }
      });
    }
  });

  /**
   * Helper to recieve styles definition for editor element.
   *
   * @param {CKEDITOR.dom.element} element
   * @param {} drupalSettings
   * @returns {*}
   */
  function getStylesDefinitionForElement(element, drupalSettings) {
    // Do nothing in case we do not have any settings at all or there is no
    // setting for the given element.
    if (typeof drupalSettings.thunder_print === 'undefined'
      || typeof drupalSettings.thunder_print.idmsstyle === 'undefined'
      || typeof drupalSettings.thunder_print.idmsstyle[element.$.id] === 'undefined') {
      return;
    }

    return drupalSettings.thunder_print.idmsstyle[element.$.id];
  }

})(CKEDITOR, drupalSettings);
