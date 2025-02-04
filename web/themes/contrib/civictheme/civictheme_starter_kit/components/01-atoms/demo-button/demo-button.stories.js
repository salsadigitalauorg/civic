// phpcs:ignoreFile
/**
 * @file
 * Demo Button stories.
 *
 * This file provides explanations on what makes up a Storybook story file
 * and how this displays in Storybook.
 *
 * We use the HTML version of Storybook to generate stories - an introduction to
 * this type of Storybook can be found here:
 * https://storybook.js.org/docs/html/get-started/whats-a-story
 */

// Importing the template file for use in generating the HTML.
import Component from './demo-button.twig';

//
// Story metadata.
//
// @code
// {
//   title: '(Atoms|Molecules|Organisms|Templates|Pages)/<Component Name>'
//   parameters: {
//     layout: '(centered|fullscreen)'
//   }
// }
// @endcode
//
const meta = {
  title: 'Atoms/Demo Button',
  component: Component,
  parameters: {
    layout: 'centered',
  },
  // Argument Types - all configurable component properties are listed here.
  //
  // All components should have the 'theme' key specified below with
  // 'light' and 'dark' options to control Light and Dark theme of a component
  // visual presentation.
  //
  // Components should also provide 'modifier_class' property so that custom
  // additional classes could be passed to the component, if required.
  argTypes: {
    theme: {
      control: { type: 'radio' },
      options: ['light', 'dark'],
    },
    text: {
      control: { type: 'text' },
    },
    disabled: {
      control: { type: 'boolean' },
    },
    modifier_class: {
      control: { type: 'text' },
    },
  },
};

export default meta;

export const DemoButton = {
  // Values returned by args are used as properties (twig variables) when a
  // component is rendered in Storybook.
  args: {
    theme: 'light',
    text: 'Button Text',
    disabled: false,
    modifier_class: ''
  },
};
