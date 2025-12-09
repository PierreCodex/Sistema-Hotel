// @ts-check

// This runs in Node.js - Don't use client-side code here (browser APIs, JSX...)

/** @type {import('@docusaurus/plugin-content-docs').SidebarsConfig} */
const sidebars = {
  tutorialSidebar: [
    'intro',
    {
      type: 'category',
      label: '🚀 Primeros Pasos',
      items: ['getting-started/installation'],
      collapsed: false,
    },
    {
      type: 'category',
      label: '🏗️ Arquitectura',
      items: ['architecture/overview'],
      collapsed: false,
    },
    {
      type: 'category',
      label: '📦 Módulos',
      items: [
        'modules/rooms',
        'modules/reception',
        'modules/billing',
        'modules/users',
        'modules/products',
      ],
      collapsed: false,
    },
  ],
};

export default sidebars;
