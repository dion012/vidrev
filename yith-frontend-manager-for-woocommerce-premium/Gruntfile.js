const potInfo = {
    languageFolderPath: './languages/',
    filename          : 'yith-frontend-manager-for-woocommerce.pot',
    headers           : {
        poedit                 : true, // Includes common Poedit headers.
        'x-poedit-keywordslist': true, // Include a list of all possible gettext functions.
        'report-msgid-bugs-to' : 'YITH <plugins@yithemes.com>',
        'language-team'        : 'YITH <info@yithemes.com>'
    }
};

module.exports = function ( grunt ) {
    'use strict';

    grunt.initConfig( {
                          dirs: {
                              css: 'assets/css',
                              js : 'assets/js'
                          },

                          uglify: {
                              options: {
                                  ie8   : true,
                                  parse : {
                                      strict: false
                                  },
                                  output: {
                                      comments: /@license|@preserve|^!/
                                  }
                              },
                              common: {
                                  files: [{
                                      expand: true,
                                      cwd: '<%= dirs.js %>/',
                                      src: [
                                          '*.js',
                                          '!*.min.js'
                                      ],
                                      dest: '<%= dirs.js %>/',
                                      ext: '.min.js'
                                  }]
                              },
                          },

                          makepot: {
                              options: {
                                  type         : 'wp-plugin',
                                  domainPath   : 'languages',
                                  headers      : potInfo.headers,
                                  updatePoFiles: false,
                                  processPot   : function ( pot ) {
                                      // Exclude plugin meta
                                      var translation,
                                          excluded_meta = [
                                              'Plugin Name of the plugin/theme',
                                              'Plugin URI of the plugin/theme',
                                              'Author of the plugin/theme',
                                              'Author URI of the plugin/theme'
                                          ];

                                      for ( translation in pot.translations[ '' ] ) {
                                          if ( 'undefined' !== typeof pot.translations[ '' ][ translation ].comments.extracted ) {
                                              if ( excluded_meta.indexOf( pot.translations[ '' ][ translation ].comments.extracted ) >= 0 ) {
                                                  console.log( 'Excluded meta: ' + pot.translations[ '' ][ translation ].comments.extracted );
                                                  delete pot.translations[ '' ][ translation ];
                                              }
                                          }
                                      }

                                      return pot;
                                  }
                              },
                              dist   : {
                                  options: {
                                      filename: potInfo.filename,
                                      exclude : [
                                          'bin/.*',
                                          'plugin-fw/.*',
                                          'plugin-upgrade/.*',
                                          'node_modules/.*',
                                          'tmp/.*',
                                          'vendor/.*'
                                      ]
                                  }
                              }
                          },

                          update_po: {
                              options: {
                                  template: potInfo.languageFolderPath + potInfo.filename
                              },
                              build  : {
                                  src: potInfo.languageFolderPath + '*.po'
                              }
                          }

                      } );

    grunt.registerMultiTask( 'update_po', 'This task update .po strings by .pot', function () {
        grunt.log.writeln( 'Updating .po files.' );

        var done     = this.async(),
            options  = this.options(),
            template = options.template;
        this.files.forEach( function ( file ) {
            if ( file.src.length ) {
                var counter = file.src.length;

                grunt.log.writeln( 'Processing ' + file.src.length + ' files.' );

                file.src.forEach( function ( fileSrc ) {
                    grunt.util.spawn( {
                                          cmd : 'msgmerge',
                                          args: ['-U', fileSrc, template]
                                      }, function ( error, result, code ) {
                        const output = fileSrc.replace( '.po', '.mo' );
                        grunt.log.writeln( 'Updating: ' + fileSrc + ' ...' );

                        if ( error ) {
                            grunt.verbose.error();
                        } else {
                            grunt.verbose.ok();
                        }

                        // Updating also the .mo files
                        grunt.util.spawn( {
                                              cmd : 'msgfmt',
                                              args: [fileSrc, '-o', output]
                                          }, function ( moError, moResult, moCode ) {
                            grunt.log.writeln( 'Updating MO for: ' + fileSrc + ' ...' );
                            counter--;
                            if ( moError || counter === 0 ) {
                                done( moError );
                            }
                        } );
                        if ( error ) {
                            done( error );
                        }
                    } );
                } );
            } else {
                grunt.log.writeln( 'No file to process.' );
            }
        } );
    } );

    // Load NPM tasks to be used here.
    grunt.loadNpmTasks( 'grunt-wp-i18n' );
    grunt.loadNpmTasks( 'grunt-contrib-uglify' );

    // Register tasks.
    grunt.registerTask( 'js', ['uglify'] );
};
