const Encore = require('@symfony/webpack-encore');
const dotenv = require('dotenv');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled image will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]'
    })
    .configureDefinePlugin(options => {
        let env = {};
        if (Encore.isProduction()) {
            env = dotenv.config();
        } else {
            env = dotenv.config({
                path: './.env.local'
            });
        }

        if (env.error) {
            throw env.error;
        }

        options['process.env'].GOOGLE_STREET_VIEW_API = JSON.stringify(env.parsed.GOOGLE_STREET_VIEW_API);
        options['process.env'].IGN_API_KEY = JSON.stringify(env.parsed.IGN_API_KEY);
    })

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')
    .addEntry('auth', './assets/auth.js')
    .addEntry('main', './assets/js/main.js')
    .addEntry('addressSearchBar', './assets/js/search/addressSearchBar.js')
    .addEntry('contactSearchBar', './assets/js/search/contactSearchBar.js')
    .addEntry('streetViewMap', './assets/js/map/streetViewMap.js')
    .addEntry('aerialViewMap', './assets/js/map/aerialViewMap.js')
    .addEntry('choices', './assets/js/choices.js')

    // enables the Symfony UX Stimulus bridge (used in image/bootstrap.js)
    // .enableStimulusBridge('./image/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
