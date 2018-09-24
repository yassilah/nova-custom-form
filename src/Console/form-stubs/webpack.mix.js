let mix = require('laravel-mix')

mix.setPublicPath('dist')
    .js('resources/js/form.js', 'js')
    .sass('resources/sass/form.scss', 'css')
    .webpackConfig({
        resolve: {
            alias: {
                '@nova': path.resolve(__dirname, '../../vendor/laravel/nova/resources/js/')
            }
        }
    })