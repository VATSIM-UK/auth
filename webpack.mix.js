const mix = require('laravel-mix');

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
mix.js('resources/js/app.js', 'public/js')
    .extract(['vue', 'vue-router', 'apollo-boost', 'vue-apollo'])
    .sass('resources/sass/app.scss', 'public/css')
    .styles([
        'node_modules/nprogress/nprogress.css',
        'node_modules/@fortawesome/fontawesome-free/css/all.css'
    ], 'public/css/libraries.css')
    .scripts([
        'node_modules/nprogress/nprogress.js'
    ], 'public/js/libraries.js');

if (mix.inProduction()) {
    mix.version();
}

mix.copyDirectory('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/webfonts');


if (process.env.NODE_ENV === 'testing') {
    Mix.manifest.refresh = () => {
    };
}
