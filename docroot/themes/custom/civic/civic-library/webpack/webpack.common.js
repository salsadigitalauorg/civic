const path = require('path');
const glob = require('glob');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const globImporter = require('node-sass-glob-importer');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  entry: function (pattern) {
    // Scan for all JS.
    let entries = glob.sync(pattern);
    // Add explicitly imported (S)CSS entries from css.js.
    entries.push(path.resolve(__dirname, 'css.js'));
    // Add explicitly imported SVG entries from svg.js.
    entries.push(path.resolve(__dirname, 'svg.js'));
    return entries;
  }('../components/**/!(*.stories|*.component|*.min|*.test).js'),
  output: {
    filename: 'civic.js',
    path: path.resolve(__dirname, '../dist'),
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '../dist/civic.css',
    }),
    new CleanWebpackPlugin(),
  ],
  module: {
    rules: [
      // JS Loader.
      {
        test: /^(?!.*\.(stories|component)\.js$).*\.js$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
      },
      // CSS Loader.
      {
        test: /\.s[ac]ss$/i,
        exclude: /node_modules/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              sourceMap: true,
              url: false,
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true,
              sassOptions: {
                importer: globImporter(),
              },
            },
          },
        ],
      },
      // Twig loader.
      {
        test: /\.twig$/,
        use: [{
          loader: 'twigjs-loader'
        }]
      },
    ],
  },
  resolve: {
    alias: {
      '@base': path.resolve(__dirname, '../components/00-base'),
      '@atoms': path.resolve(__dirname, '../components/01-atoms'),
      '@molecules': path.resolve(__dirname, '../components/02-molecules'),
      '@organisms': path.resolve(__dirname, '../components/03-organisms'),
      '@templates': path.resolve(__dirname, '../components/04-templates'),
      '@pages': path.resolve(__dirname, '../components/05-pages'),
    }
  },
  stats: {
    errorDetails: true,
  },
};
