const mix = require('laravel-mix');

let tailwindcss = require('tailwindcss');
let postcssImport = require('postcss-import');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js');

// mix.sass('resources/sass/bootstrap.scss', 'public/css/bootstrap.css');

// mix.postCss('resources/css/tailwind.css', 'public/css/tailwind.css', [
//     postcssImport(),
//     tailwindcss('tailwind.config.js'),
// ]);

mix.sass('resources/sass/tailwind.scss', 'public/css/tailwind.css').options({
    processCssUrls: false,
    postCss: [
        tailwindcss('tailwind.config.js')
    ],
});
