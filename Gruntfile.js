module.exports = function(grunt) {

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					potFileName: 'cpt-core.pot',
					mainFile: 'CPT_Core.php',
					type: 'wp-plugin'
				}
			}
	   },

		addtextdomain: {
			theme: {
				options: {
					textdomain: 'cpt-core'
				},
				target: {
					files: {
						src: [ '*.php' ]
					}
				}
			},
		}
	});

	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	grunt.registerTask('default', ['makepot']);

};
