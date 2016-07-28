// Include gulp
var gulp = require('gulp');

// Include Our Pluginsvar sass = require('gulp-sass');
var concat = require('gulp-concat');
var coffee = require('gulp-coffee');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');

// Compile Our Sass
gulp.task('sass', function() {
    return gulp.src('scss/*.scss')
        .pipe(sass())
        .pipe(gulp.dest('public/css'));
});

// Concatenate & Minify JS
gulp.task('coffee', function() {
  gulp.src('./public/coffee/*.coffee')
    .pipe(coffee({bare: true}))
    .pipe(gulp.dest('./public/js'))
    .pipe(rename('wp-timeline.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('./public/js'));
});

// Watch Files For Changes
gulp.task('watch', function() {
    gulp.watch('./public/coffee/*.coffee', ['coffee']);
    gulp.watch('./public/scss/**/*.scss', ['sass']);
});

// Default Task
gulp.task('default', ['watch']);