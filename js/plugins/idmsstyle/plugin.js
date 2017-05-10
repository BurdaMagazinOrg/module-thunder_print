(function (CKEDITOR, drupalSettings, $) {
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

      // Also do nothing if we have no settings for the given field/element.
      if (typeof drupalSettings.thunder_print.idmsstyle[$el.id] === 'undefined') {
        return;
      }

      var fieldStyles = drupalSettings.thunder_print.idmsstyle[$el.id];
      console.log('thunder_print_idmsstyle init', [$el.id, editor, fieldStyles]);

      editor.ui.addRichCombo('thunder_print_idmsstyle',
        {
          label: "Dropdown", //label displayed in toolbar
          title: 'Zoom',//popup text when hovering over the dropdown
          multiSelect: false,

          stylesRaw: fieldStyles,
          styles: {},
          stylesList: [],

          //use the same style as the font/style dropdowns
          panel: {
            css: [CKEDITOR.skin.getPath('editor')].concat(config.contentsCss),
          },

          /**
           * Helper to initialize style list from the raw style definitions.
           */
          buildStylesList: function() {
            var stylesList = [];
            var styles = {};
            $.each(this.stylesRaw, function(name, definition) {
              definition.name = name;
              var style = new CKEDITOR.style(definition);
              styles[name] = style;
              stylesList.push(style);
            });

            this.styles = styles;

            // Sorts the Array, so the styles get grouped by type in proper order (#9029).
            stylesList.sort( function( styleA, styleB ) {
              if (styleA.type !== styleA.type) {
                return styleA.type < styleA.type ? -1: 1;
              }

              return styleA._.weight - styleB._.weight;
            } );

            this.stylesList = stylesList;
          },

          init: function (e) {
            console.log($el.id + ': Richcombo init', e);
            console.log('styles raw', this.stylesRaw);

            this.buildStylesList();
            console.log('styles list', this.stylesList);

            var combo = this;
            var prevType;

            $.each(this.stylesList, function(name, style) {
              if (prevType !== style.type) {
                combo.startGroup('Group ' + style.type);
                prevType = style.type
              }

              combo.add(name, style.type == CKEDITOR.STYLE_OBJECT ? name : style.buildPreview(), name);
            });


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
})(CKEDITOR, drupalSettings, jQuery);
