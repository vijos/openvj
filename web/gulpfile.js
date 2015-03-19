var es = require('event-stream');
var path = require('path');

var gulp = require('gulp');
var gutil = require('gulp-util');
var gulpFilter = require('gulp-filter');
var gulpIgnore = require('gulp-ignore');
var logger = require('gulp-logger');
var plumber = require('gulp-plumber');
var watch = require('gulp-watch');

var autoprefixer = require('gulp-autoprefixer');
var coffee = require('gulp-coffee');
var concat = require('gulp-concat');
var concatCSS  = require('gulp-concat-css');
var less = require('gulp-less');
var minifyCSS = require('gulp-minify-css');
var replace = require('gulp-replace');
var sourcemaps = require('gulp-sourcemaps');
var stylus = require('gulp-stylus');
var uglify = require('gulp-uglify');

var components = [
    "reset",
    "site",

    "button",
    "divider",
    //"flag",
    "header",
    "icon",
    "image",
    "input",
    "label",
    "list",
    "loader",
    //"rail",
    //"reveal",
    "segment",
    //"step",

    "breadcrumb",
    "form",
    "grid",
    "menu",
    "message",
    "table",

    //"ad",
    //"card",
    "comment",
    "feed",
    "item",
    //"statistic",
    
    //"accordion",
    "checkbox",
    //"dimmer",
    "dropdown",
    "modal",
    "nag",
    "popup",
    "progress",
    //"rating",
    "search",
    //"shape",
    //"sidebar",
    "sticky",
    "tab",
    "transition",
    //"video",
    
    //"api",
    "form",
    "state",
    "visibility"
].join(',');

gulp.task('shared:ui', function() {
    return es.merge(
    // build scripts
    gulp.src('./bower_components/semantic-ui/src/definitions/**/{' + components + '}.js')
        .pipe(logger())
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(concat('ui.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./static/lib/')),
    // build styles: copy config file first
    gulp.src('./src/ui/theme.config')
        .pipe(logger())
        .pipe(plumber())
        .pipe(gulp.dest('./bower_components/semantic-ui/src/'))
        .on('end', function() {
            gulp.start('shared:ui:style')
        })
    );
});

gulp.task('shared:ui:style', function() {
    var lessFilter = gulpFilter('**/*.less')
    var varFilter = gulpFilter('site.variables')
    return es.merge(
    gulp.src([
        './bower_components/semantic-ui/src/definitions/**/{' + components + '}.less',
        './src/ui/globals/site.variables'
    ])
        .pipe(logger())
        .pipe(plumber())
        // compile LESS variable to Stylus variable
        .pipe(varFilter)
        .pipe(replace(/^(@[^:]*):/gm, '$1='))
        .pipe(replace(/@/g, '$$'))
        .pipe(concat('.site.styl'))
        .pipe(gulp.dest('./src/ui/globals/'))
        .pipe(varFilter.restore())
        .pipe(gulpIgnore.exclude('**/.site.styl'))
        // compile LESS
        .pipe(lessFilter)
        .pipe(less())
        .pipe(lessFilter.restore())
        // concat them
        .pipe(concatCSS('style.css'))
        .pipe(autoprefixer())
        .pipe(minifyCSS())
        .pipe(gulp.dest('./static/'))
        .on('end', function() {
            gulp.start('shared:ui:page')
        }),
    // copy assets
    gulp.src('./bower_components/semantic-ui/src/themes/default/assets/**/{' + components + '}?(s).*')
        .pipe(logger())
        .pipe(plumber())
        .pipe(gulp.dest('./static/'))
    );
});

gulp.task('shared:ui:page', function() {
    // build semantic-ui themes
    return gulp.src([
        './src/ui/**/*.styl',
        '!./src/ui/globals/site.styl'
    ])
        .pipe(logger())
        .pipe(plumber())
        // compile Stylus
        .pipe(stylus({ import: path.join(__dirname, 'src/ui/globals/.site.styl') }))
        .pipe(concatCSS('page.css'))
        .pipe(autoprefixer())
        .pipe(minifyCSS())
        .pipe(gulp.dest('./static/'))
})

gulp.task('shared:lib', function() {
    // jquery, requirejs, requirejs-config, underscore
    return gulp.src([
        './bower_components/jquery/dist/jquery.js',
        './bower_components/velocity/velocity.js',
        './bower_components/requirejs/require.js',
        './src/require-config.js',
        './bower_components/underscore/underscore.js'
    ])
        .pipe(logger())
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(concat('lib.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./static/lib/'));
});

gulp.task('shared:ext', function() {
    return es.merge(
    // google-code-prettify
    gulp.src('./bower_components/google-code-prettify/src/*.js')
        .pipe(logger())
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./static/lib/google-code-prettify/')),
    // marked
    gulp.src('./bower_components/marked/lib/marked.js')
        .pipe(logger())
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./static/lib/marked/')),
    // twig.js
    gulp.src('./bower_components/twig.js/twig.js')
        .pipe(logger())
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./static/lib/twig/'))
    );
});

gulp.task('shared:core', function() {
    return gulp.src('./src/shared/**/*.coffee')
        .pipe(logger())
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(coffee({bare: true}).on('error', gutil.log))
        .pipe(concat('openvj.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./static/lib/'));
});

gulp.task('page:scripts', function() {
    return gulp.src('./src/*.coffee')
        .pipe(logger())
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(coffee({bare: true}).on('error', gutil.log))
        .pipe(uglify())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./static/'));
});

gulp.task('watch', function() {
    return watch(['./src/**/*', '!./src/ui/globals/site.styl'], function(file) {
        if (file.path.match(/\/src\/shared\/[\s\S]*?\.coffee$/)) {
            console.log('modified: %s', file.path);
            gulp.start('shared:core');
            return;
        }
        if (file.path.match(/\/src\/[^\/]*?\.coffee$/)) {
            console.log('modified: %s', file.path);
            gulp.start('page:scripts');
            return;
        }
        if (file.path.indexOf('theme.config') !== -1) {
            console.log('modified: %s', file.path);
            gulp.start('shared:ui');
            return;
        }
        if (file.path.indexOf('require-config.js') !== -1) {
            console.log('modified: %s', file.path);
            gulp.start('shared:lib');
            return;
        }
        if (file.path.match(/(\.less|\.variables|\.overrides)$/)) {
            console.log('modified: %s', file.path);
            gulp.start('shared:ui:style');
            return;
        }
        if (file.path.match(/\.styl$/)) {
            console.log('modified: %s', file.path);
            gulp.start('shared:ui:page');
            return;
        }
    });
});