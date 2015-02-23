module.exports = (grunt) ->
  require('load-grunt-tasks')(grunt)
  grunt.util.linefeed = '\n'
  grunt.initConfig
    pkg: grunt.file.readJSON 'package.json'
    bowerRequirejs:
      target:
        options:
          baseUrl: './'
        rjsConfig: 'js/require-config.js'
    clean:
      release: ['js/dest/', 'css/dest/']
    copy:
      foundation:
        files: [
          expand: true
          cwd: 'bower_components/foundation/css/'
          src: ['*']
          dest: 'css/dest/'
          filter: 'isFile'
        ]
    coffee:
      compile:
        files: [
          expand: true
          cwd: 'js/src/'
          src: ['**/*.coffee']
          dest: 'js/dest/'
          ext: '.js'
          extDot: 'last'
        ]
    watch:
      coffee:
        files: ['js/src/**/*.coffee']
        tasks: ['coffee']

  grunt.registerTask 'default', ['clean', 'copy:foundation', 'coffee']
  grunt.registerTask 'debug', ['coffee', 'watch']
  grunt.registerTask 'bower', ['copy:foundation', 'bowerRequirejs']