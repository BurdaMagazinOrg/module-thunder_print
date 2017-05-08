(function (CKEDITOR, drupalSettings) {
  'use strict';

  CKEDITOR.plugins.add('thunder_print_idmsstyle', {
    requires: ['richcombo'],

    init: function (editor) {

      // Do nothing in case we do not have idmsstyle settings at all.
      if (typeof drupalSettings.thunder_print === 'undefined' || typeof drupalSettings.thunder_print.idmsstyle === 'undefined') {
        return;
      }

      var config = editor.config;
      var $el = editor.element.$;

      // Do also nothing if we have no settings for the given field/element.
      if (typeof drupalSettings.thunder_print.idmsstyle[$el.id] === 'undefined') {
        return;
      }

      var fieldSettings = drupalSettings.thunder_print.idmsstyle[$el.id];
      console.log('thunder_print_idmsstyle init', [$el.id, editor, fieldSettings]);

      editor.ui.addRichCombo('thunder_print_idmsstyle',
        {
          label: "Dropdown", //label displayed in toolbar
          title: 'Zoom',//popup text when hovering over the dropdown
          multiSelect: false,

          //use the same style as the font/style dropdowns
          panel: {
            css: [CKEDITOR.skin.getPath('editor')].concat(config.contentsCss),
          },
          init: function (e) {
            console.log($el.id + ': Richcombo init', e);
            //start group in the dropdown
            this.startGroup('Group 1');
            //VALUE - The value we get when the row is clicked
            //HTML - html/plain text that should be displayed in the dropdown
            //TEXT - displayed in popup when hovered over the row.
            //this.add( VALUE, HTML, TEXT );
            //add row to the first group
            this.add(2, "<h1>Test</h1>", "333");

            //start another group in the dropdown
            this.startGroup('Group 2');
            //add row to the second group.
            this.add("444", "No HTML Here", "666");

            //we can also set the initial value that the dropdown takes
            //when it is clicked for the first time.
            // Default value on first click
            // this.setValue("444", "No HTML Here");
          },
          onClick: function( value, e ) {
            console.log($el.id + ': onClick', value, e);
          },

          onRender: function(e) {
            console.log($el.id + ': onRender', e);

            editor.on( 'selectionChange', function( ev ) {
              console.log($el.id + ': selectionChange', ev);
            }, this );
          },
          onOpen: function() {
            console.log($el.id + ': onOpen');
          },
          refresh: function() {
            console.log($el.id + ': refresh');
          },
          reset: function() {
            console.log($el.id + ': reset');
          }
        });
    }
  });
})(CKEDITOR, drupalSettings);
