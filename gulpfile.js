/**
 * Gulp task to run the web server
 *
 * @usage
 *   $ gulp webserver
 */

var gulp = require('gulp');
var webserver = require('gulp-webserver');

gulp.task('webserver', function() {
  gulp.src('app')
    .pipe(webserver({
      livereload: true,
      //directoryListing: true,
      open: true,
      port : 1337,
      fallback: 'index.html'
    }));
});