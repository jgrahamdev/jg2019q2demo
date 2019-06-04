var $ = require('gulp-load-plugins')();
var inky = require('inky');
var gulp = require('gulp');
var lazypipe = require('lazypipe');
var argv = require('minimist')(process.argv.slice(2));

// Adapted from https://github.com/zurb/foundation-emails/blob/develop/gulpfile.js#L205
function inliner() {
  var pipe = lazypipe()
    .pipe($.inlineCss, {
      applyStyleTags: true,
      removeStyleTags: true,
      removeLinkTags: true
    });

  return pipe();
}

gulp.task('default', function() {
  var base_dir = process.cwd();
  if (argv.directory) {
    process.chdir(argv.directory);
  }
  return gulp.src(['inky_templates/*.html.twig', '!inky_templates/_*.html.twig'])
    .pipe($.wrap({ src: 'inky_templates/_wrap.html.twig' }, { base_dir: base_dir }))
    .pipe(inky())
    .pipe(inliner())
    .pipe($.prettify({ indent_size: 2 }))
    .pipe(gulp.dest('inky_templates/compiled'));
});
