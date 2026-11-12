// The app shares UI copy (resources/js/i18n/bs.ts), formatters and types with
// the web app, so Metro also watches that folder.
const path = require('path');
const { getDefaultConfig } = require('expo/metro-config');

const config = getDefaultConfig(__dirname);
config.watchFolders = [...(config.watchFolders ?? []), path.resolve(__dirname, '../resources/js')];

module.exports = config;
