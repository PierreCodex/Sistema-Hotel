// @ts-check
// `@type` JSDoc annotations allow editor autocompletion and type checking
// (when paired with `@ts-check`).
// There are various equivalent ways to declare your Docusaurus config.
// See: https://docusaurus.io/docs/api/docusaurus-config

import { themes as prismThemes } from 'prism-react-renderer';

// This runs in Node.js - Don't use client-side code here (browser APIs, JSX...)

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'Sistema Hotel',
  tagline: 'Sistema de Gestión Hotelera en PHP',
  favicon: 'img/favicon.ico',

  // Future flags, see https://docusaurus.io/docs/api/docusaurus-config#future
  future: {
    v4: true, // Improve compatibility with the upcoming Docusaurus v4
  },

  // Set the production url of your site here
  url: 'https://sistema-hotel-docs.vercel.app',
  // Set the /<baseUrl>/ pathname under which your site is served
  // For Vercel, use '/' - For GitHub Pages use '/<projectName>/'
  baseUrl: '/',

  // GitHub pages deployment config.
  organizationName: 'PierreCodex', // Usually your GitHub org/user name.
  projectName: 'Sistema-Hotel', // Usually your repo name.

  onBrokenLinks: 'throw',

  // Even if you don't use internationalization, you can use this field to set
  // useful metadata like html lang. For example, if your site is Chinese, you
  // may want to replace "en" with "zh-Hans".
  i18n: {
    defaultLocale: 'es',
    locales: ['es'],
  },

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: './sidebars.js',
          // Remove edit links since this is hosted locally
        },
        blog: {
          showReadingTime: true,
          feedOptions: {
            type: ['rss', 'atom'],
            xslt: true,
          },
          // Useful options to enforce blogging best practices
          onInlineTags: 'warn',
          onInlineAuthors: 'warn',
          onUntruncatedBlogPosts: 'warn',
        },
        theme: {
          customCss: './src/css/custom.css',
        },
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      // Replace with your project's social card
      image: 'img/docusaurus-social-card.jpg',
      colorMode: {
        respectPrefersColorScheme: true,
      },
      navbar: {
        title: 'Sistema Hotel',
        logo: {
          alt: 'Sistema Hotel Logo',
          src: 'img/logo.svg',
        },
        items: [
          {
            type: 'docSidebar',
            sidebarId: 'tutorialSidebar',
            position: 'left',
            label: 'Documentación',
          },
          {
            href: 'https://github.com/PierreCodex/Sistema-Hotel',
            label: 'GitHub',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Documentación',
            items: [
              {
                label: 'Introducción',
                to: '/docs/intro',
              },
              {
                label: 'Instalación',
                to: '/docs/getting-started/installation',
              },
              {
                label: 'Arquitectura',
                to: '/docs/architecture/overview',
              },
            ],
          },
          {
            title: 'Módulos',
            items: [
              {
                label: 'Habitaciones',
                to: '/docs/modules/rooms',
              },
              {
                label: 'Recepción',
                to: '/docs/modules/reception',
              },
              {
                label: 'Facturación',
                to: '/docs/modules/billing',
              },
            ],
          },
          {
            title: 'Más',
            items: [
              {
                label: 'Changelog',
                to: '/blog',
              },
              {
                label: 'GitHub',
                href: 'https://github.com/PierreCodex/Sistema-Hotel',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} Sistema Hotel. Construido con Docusaurus.`,
      },
      prism: {
        theme: prismThemes.github,
        darkTheme: prismThemes.dracula,
        additionalLanguages: ['php', 'sql', 'bash'],
      },
    }),
};

export default config;
