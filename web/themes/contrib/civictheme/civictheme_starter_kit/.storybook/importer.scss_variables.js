//
// Extract CSS variables as object from SCSS file.
//
import fs from 'fs';
import extractor from '@alexskrypnyk/scss-variables-extractor';

const files = [
  './components_combined/00-base/_variables.base.scss',
  './components_combined/variables.base.scss',
  './components_combined/00-base/_variables.components.scss',
  './components_combined/variables.components.scss',
];

function getVariables() {
  let variables = {};

  for (const i in files) {
    const content = fs.readFileSync(files[i], { encoding: 'utf8' });
    variables = { ...variables, ...extractor.extract(content) };
  }

  return variables;
}

export default {
  getVariables,
};
