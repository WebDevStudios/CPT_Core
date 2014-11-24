module.exports = function(grunt) {

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		makepot: {
			cptcore: {
				options: {
					cwd: 'plugins/wds-must-use-plugins/cpt_core',
					domainPath: '/languages/',
					potFileName: 'cpt-core.pot',
					type: 'wp-plugin'
				}
			},
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
