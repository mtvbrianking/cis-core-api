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

mix.js('resources/js/app.js', 'public/js'); // .version();

mix.sass('resources/sass/bootstrap.scss', 'public/css/bootstrap.css');

// mix.postCss('resources/css/tailwind.css', 'public/css/tailwind.css', [
//     postcssImport(),
//     tailwindcss('tailwind.config.js'),
// ]);

// mix.sass('resources/sass/tailwind.scss', 'public/css/tailwind.css').options({
//     processCssUrls: false,
//     postCss: [
//         tailwindcss('tailwind.config.js')
//     ],
// });

// mix.copy('node_modules/datatables.net-buttons', 'public/vendor/datatables.net-buttons');
mix.copy(
    'node_modules/datatables.net-buttons/js/buttons.html5.min.js',
    'public/vendor/datatables.net-buttons/js/buttons.html5.min.js'
);

mix.copy(
    'node_modules/jszip/dist/jszip.min.js',
    'public/vendor/jszip/dist/jszip.min.js'
);

// --------------------------------------------------------------------
// Page specific assets
// --------------------------------------------------------------------

// mix.copyDirectory('resources/js/pages', 'public/js/pages'); <- Copy, No minify

mix.js('resources/js/pages/routes.js', 'public/js/pages/routes.js');

mix.js('resources/js/pages/auth-code-clients/index.js', 'public/js/pages/auth-code-clients/index.js');
mix.js('resources/js/pages/personal-clients/index.js', 'public/js/pages/personal-clients/index.js');
