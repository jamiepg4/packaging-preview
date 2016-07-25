module.exports = function( grunt ) {

	'use strict';
	// Project configuration

	var remapify = require('remapify');

	grunt.initConfig( {

		pkg:    grunt.file.readJSON( 'package.json' ),

		// Compile SASS
		sass: {
			options: {
				sourceComments: false
			},
			compile: {
				files: {
					'assets/css/seo-preview.css' : 'assets/css/scss/seo-preview.scss'
				}
			}

		},

        // Autoprefixer with default browser data
		postcss: {
			options: {
				processors: [
					require('autoprefixer')(),
				]
			},
			dist: {
				src: ['assets/css/*.css']
			}
		},

		// Strip comments
		stripCssComments: {
			dist: {
				files: {
                    'assets/css/seo-preview.css': ['assets/css/seo-preview.css'],
				}
			}
		},

		// Watch for changes
		watch:  {
			styles: {
				files: ['assets/css/*/**/*.scss'],
				tasks: ['styles'],
				options: {
					debounceDelay: 500,
					livereload: false
				}
			},
			css: {
				files: ['assets/css/*.css'],
				options: {
					livereload: true
				}
			},
			scripts: {
				files: ['assets/js/*/**/*.js', 'js-tests/**/*.js', 'Gruntfile.js'],
				tasks: ['scripts'],
				options: {
					debounceDelay: 500
				}
			},
		},

		browserify : {
			options: {
				preBundleCB: function(b) {
					b.plugin(remapify, [
						{
							cwd: 'assets/js/src/admin/seo-preview/models',
							src: '*.js',
							expose: 'seo-models'
						},
						{
							cwd: 'assets/js/src/admin/seo-preview/views',
							src: '*.js',
							expose: 'seo-views'
						},
						{
							cwd: 'assets/js/src/admin/seo-preview/utils',
							src: '*.js',
							expose: 'seo-utils'
						},
						{
							cwd: 'assets/js/src/lib',
							src: '*.js',
							expose: 'lib'
						}

					]);

				}
			},
			dist: {
				files : {
					'assets/js/build/seo-preview.js' : ['assets/js/src/admin/seo-preview/**/*.js', 'assets/js/src/admin-seo-preview.js'],
				},
				options: {
					transform: ['browserify-shim']
				}
			},
		},

		jshint: {
			options: {
				curly: true,
				eqeqeq: true,
				browser: true,
				force: false,
			},
			uses_defaults: [
				'assets/js/src/**/*.js',
				'js-tests/specs/**/*.js',
			],
		},

		scsslint: {
    		allFiles: [
      			'assets/css/scss/**/*.scss'
    		],
    		options: {
				compact: true,
      			bundleExec: true,
		      	colorizeOutput: true,
    		},
  		},

	});

	grunt.loadNpmTasks('grunt-scss-lint');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-jasmine');
	grunt.loadNpmTasks('grunt-browserify');
	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-postcss');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-strip-css-comments');

	// Default task.
	grunt.registerTask( 'default', [ 'styles', 'scripts' ] );

	grunt.registerTask( 'styles', ['scsslint', 'sass', 'postcss', 'stripCssComments'] );

	grunt.registerTask( 'scripts', ['jshint', 'browserify'/*, 'jasmine'*/] );
	grunt.registerTask( 'js-tests', ['jasmine'] );
	grunt.registerTask( 'travis-lint', [ 'lint', 'js-tests' ] );

	grunt.util.linefeed = '\n';
};
