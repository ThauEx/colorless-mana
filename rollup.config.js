import { nodeResolve } from '@rollup/plugin-node-resolve';
import styles from 'rollup-plugin-styles';
import outputManifest from 'rollup-plugin-output-manifest';
import del from 'rollup-plugin-delete';

export default {
  input: { 'js/main': 'assets/js/main.js' },
  output: {
    dir: 'public',
    format: 'iife',
    inlineDynamicImports: true,
    assetFileNames: '[name]-[hash][extname]',
  },
  plugins: [
    nodeResolve(),
    styles({
      mode: ['extract', 'css/styles.css'],
      url: {
        publicPath: '../assets',
      },
    }),
    outputManifest({
      fileName: 'manifests/manifest.json',
      map: bundle => {
        // CSS files would end up with two extensions
        if (bundle.type === 'asset' && bundle.name?.endsWith('.css')) {
          bundle.name = bundle.name.replace('.css', '');
        }

        return bundle;
      },
    }),
    del({ targets: ['public/css/*', 'public/js/*'] })
  ],
};
