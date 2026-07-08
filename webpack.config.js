const Encore = require('@symfony/webpack-encore').default;

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
//  .setManifestKeyPrefix('build/')
  .addEntry('app', './assets/app.js')
  .enableStimulusBridge('./assets/controllers.json')
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabel((config) => {
    config.plugins.push(['polyfill-corejs3', {
      method: 'usage-global',
      version: require('core-js/package.json').version,
    }]);
  })
  .enableIntegrityHashes(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
