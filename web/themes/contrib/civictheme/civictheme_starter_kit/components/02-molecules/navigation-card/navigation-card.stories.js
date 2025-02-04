import Component from './navigation-card.twig';
import NavigationCardData from './navigation-card.stories.data';
import Constants from '../../../dist/constants.json'; // eslint-disable-line import/no-unresolved

const meta = {
  title: 'Molecules/List/Navigation Card',
  component: Component,
  argTypes: {
    theme: {
      control: { type: 'radio' },
      options: ['light', 'dark'],
    },
    title: {
      control: { type: 'text' },
    },
    summary: {
      control: { type: 'text' },
    },
    link: {
      control: { type: 'object' },
    },
    image: {
      control: { type: 'object' },
    },
    image_as_icon: {
      control: { type: 'boolean' },
    },
    // This is a new property added for this extended component.
    tags: {
      control: { type: 'number', min: 0, max: 10, step: 1 },
    },
    icon: {
      control: { type: 'select' },
      options: Constants.ICONS,
    },
    image_over: {
      control: { type: 'text' },
    },
    content_top: {
      control: { type: 'text' },
    },
    content_middle: {
      control: { type: 'text' },
    },
    content_bottom: {
      control: { type: 'text' },
    },
    modifier_class: {
      control: { type: 'text' },
    },
    attributes: {
      control: { type: 'text' },
    },
  },
};

export default meta;

export const NavigationCard = {
  parameters: {
    layout: 'centered',
  },
  args: {
    ...NavigationCardData.args('light'),
    tags: 2,
  }
};
