import { defineConfig } from 'vitepress';
import { fileURLToPath, URL } from 'node:url';
import { generateSidebar } from 'vitepress-sidebar';
import locales from './locales';

const sidebar = {
  documentRootPath: 'src',
  useTitleFromFileHeading: true,
  useTitleFromFrontmatter: true,
  useFolderTitleFromIndexFile: true,
  sortMenusByFrontmatterOrder: true,
  excludeFiles: ['changelog.md', 'examples.md'],
  manualSortFileNameByPriority: [
    'intro.md',
    'getting-started',
    'blocks',
    'pages',
    'plugins',
    'how-to',
  ],
};

const languages = Object.keys(locales).filter((locale) => locale !== 'root');

languages.forEach((lang) => {
  locales[lang].themeConfig = {
    ...locales[lang].themeConfig,
    sidebar: generateSidebar([
      {
        ...sidebar,
        documentRootPath: `src/${lang}`,
        resolvePath: `/${lang}/`,
      },
    ]),
  };
});

export default defineConfig({
  head: [
    ['link', { rel: 'icon', href: '/Light-Portal/favicon.ico' }],
    [
      'script',
      {},
      `(function (c, l, a, r, i, t, y) {
        c[a] =
          c[a] ||
          function () {
            (c[a].q = c[a].q || []).push(arguments);
          };
        t = l.createElement(r);
        t.async = 1;
        t.src = 'https://www.clarity.ms/tag/' + i;
        y = l.getElementsByTagName(r)[0];
        y.parentNode.insertBefore(t, y);
      })(window, document, 'clarity', 'script', 'ke5jb39203')`,
    ],
  ],
  base: '/Light-Portal/',
  srcDir: './src',
  cleanUrls: true,
  markdown: {
    image: {
      lazyLoading: true,
    },
  },
  themeConfig: {
    externalLinkIcon: true,
    search: {
      provider: 'local',
    },
    sidebar: generateSidebar({
      ...sidebar,
      excludeFolders: languages,
    }),
    socialLinks: [{ icon: 'github', link: 'https://github.com/dragomano/Light-Portal' }],
  },
  locales,
  sitemap: {
    hostname: 'https://dragomano.github.io/Light-Portal/',
  },
  vite: {
    resolve: {
      alias: [
        {
          find: /^.*\/ExampleSites\.vue$/,
          replacement: fileURLToPath(new URL('./components/ExampleSites.vue', import.meta.url)),
        },
      ],
    },
  },
});
