/**
 * Gulp task to run the web server for local dev
 *
 * @usage
 *   $ gulp webserver
 */

var gulp = require('gulp');
var webserver = require('gulp-webserver');

gulp.task('webserver', function() {
  gulp.src('./')
    .pipe(webserver({
      livereload: true,
      open: true,
      port : 1337,
      fallback: 'index.html'
    }));
});

gulp.task('default',['webserver']);