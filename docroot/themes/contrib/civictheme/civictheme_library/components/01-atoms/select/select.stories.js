// phpcs:ignoreFile
import { boolean, radios, text } from '@storybook/addon-knobs';
import CivicThemeSelect from './select.twig';
import { randomOptions, getRandomInt } from '../../00-base/base.stories';

export default {
  title: 'Atoms/Form/Select',
  parameters: {
    layout: 'centered',
  },
};

export const Select = (knobTab) => {
  const generalKnobTab = typeof knobTab === 'string' ? knobTab : 'General';
  const numOfOptions = getRandomInt(3, 5);

  const generalKnobs = {
    theme: radios(
      'Theme',
      {
        Light: 'light',
        Dark: 'dark',
      },
      'light',
      generalKnobTab,
    ),
    options: boolean('With options', true, generalKnobTab) ? randomOptions(numOfOptions, (boolean('Options have groups', false, generalKnobTab) ? 'optgroup' : 'option')) : [],
    is_multiple: boolean('Is multiple', false, generalKnobTab),
    is_invalid: boolean('Invalid', false, generalKnobTab),
    is_disabled: boolean('Disabled', false, generalKnobTab),
    name: text('Name', 'element-name', generalKnobTab),
    id: text('ID', 'element-id', generalKnobTab),
    attributes: text('Additional attributes', '', generalKnobTab),
    modifier_class: text('Additional classes', '', generalKnobTab),
  };

  return CivicThemeSelect({
    ...generalKnobs,
  });
};
