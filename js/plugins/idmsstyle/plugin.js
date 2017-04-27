(function (CKEDITOR) {
  'use strict';

  CKEDITOR.plugins.add('thunder_print_idmsstyle', {
    requires: ['richcombo'],

    init: function (editor) {

      var config = editor.config;
      console.log('thunder_print_idmsstyle init');

      var $el = editor.element.$;
      console.log($el.id);

      editor.ui.addRichCombo('thunder_print_idmsstyle',
        {
          label: "Dropdown", //label displayed in toolbar
          title: 'Zoom',//popup text when hovering over the dropdown
          multiSelect: false,

          //use the same style as the font/style dropdowns
          panel: {
            css: [CKEDITOR.skin.getPath('editor')].concat(config.contentsCss),
          },
          init: function () {
            console.log('Richcombo init');
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
          onClick: function( value ) {
            console.log('onClick', value);
          },

          onRender: function() {
            console.log('onRender');

            editor.on( 'selectionChange', function( ev ) {
              console.log('selectionChange');
            }, this );
          },
          onOpen: function() {
            console.log('onOpen');
          },
          refresh: function() {
            console.log('refresh');
          },
          reset: function() {
            console.log('reset');
          }
        });
    }
  });
})(CKEDITOR);
