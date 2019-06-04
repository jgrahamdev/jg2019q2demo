var gulp     = require('gulp');
var $        = require('gulp-load-plugins')();
var gulpCopy = require('gulp-copy');
var concat   = require('gulp-concat');
var replace  = require('gulp-replace');

//standard DF build profile location from this theme
//var pathToDF = '../../../../df/';

//symlink DF build (use this if you built DF using ln function for multiple github repo suppport)
 var pathToDF = '../../../docroot/profiles/df/';


var sassPaths = [
  'node_modules/foundation-sites/scss',
  'node_modules/motion-ui/src',
  pathToDF + 'themes'
];

gulp.task('sass', function() {
  return gulp.src(['scss/obio-main.scss', 'scss/obio-colors.scss', 'scss/replacements/*.scss'])
    // If you have to provide placeholder for some of your color vars,
    // define them as var "$color--[placeholder-defined-in-color.inc-file]--var"
    // and keep the following line uncommented.
    // For more info check obio/color/color.inc.
    .pipe(replace(/\$color--(.*)--var/g, "#__$1__"))
    .pipe($.sass({
      sourceComments: 'map',
      sourceMap: 'sass',
      includePaths: sassPaths,
      outputStyle: 'nested'
    })
      .on('error', $.sass.logError))
    .pipe($.autoprefixer({
      browsers: ['last 2 versions', 'ie >= 9']
    }))
    .pipe(gulp.dest('css'));
});

// move vendor js files from bower_components into /js/ folder for deployment purposes
gulp.task('copy', function() {
return gulp.src(['node_modules/foundation-sites/dist/js/*min.js', 'node_modules/motion-ui/dist/*min.js','node_modules/what-input/dist/*min.js'])
  .pipe(gulpCopy('js/vendor',{prefix: 3}));
});

gulp.task('javascript', function() {
  return gulp.src(PATHS.javascript)
    .pipe($.sourcemaps.init())
    .pipe($.babel()) // <-- There it is!
    .pipe($.concat('app.js'))
    .pipe(uglify)
    .pipe($.if(!isProduction, $.sourcemaps.write()))
    .pipe(gulp.dest('dist/assets/js'))
    .on('finish', browser.reload);
});

// concatanate all vendor scripts into a single js file. specific order is defined
gulp.task('concat', function() {
  return gulp.src(['./js/vendor/what-input.min.js','./js/vendor/motion-ui.min.js','./js/vendor/js/foundation.min.js'])
    .pipe(concat('vendor.all.js'))
    .pipe(gulp.dest('./js/'));
});

gulp.task('default', ['sass'], function() {
  gulp.watch(['scss/**/*.scss'], ['sass']);
});
