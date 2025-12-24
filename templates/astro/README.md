# {{ name }}

This is a new Astro project scaffolded with
[Webforge](https://github.com/iamjasonhill/Webforge).

## Project Structure

Inside of your Astro project, you'll see the following folders and files:

```
/
├── public/
│   ├── manifest.json   # PWA Manifest
│   └── favicon.svg     # Favicon
├── src/
│   ├── components/
│   │   ├── Analytics.astro  # GA4 / GTM integration
│   │   ├── SEO.astro        # Meta tags & OpenGraph
│   │   └── Schema.astro     # JSON-LD Structured Data
│   ├── layouts/
│   │   └── Layout.astro     # Main layout
│   └── pages/
│       ├── index.astro      # Homepage
│       └── robots.txt.ts    # Dynamic Robots.txt
└── package.json
```

## Commands

All commands are run from the root of the project, from a terminal:

| Command           | Action                                       |
| :---------------- | :------------------------------------------- |
| `npm run dev`     | Starts local dev server at `localhost:4321`  |
| `npm run build`   | Build your production site to `./dist/`      |
| `npm run preview` | Preview your build locally, before deploying |
| `npm run lint`    | Run ESLint checks                            |
| `npm run format`  | Format code with Prettier                    |

## Design Tokens

This project includes a pre-configured design system in `tailwind.config.mjs`.

**Brand Colors:**

- `bg-brand-red` / `text-brand-red`
- `bg-brand-yellow` / `text-brand-yellow`
- `bg-brand-dark` / `text-brand-dark`

You can customize these in `tailwind.config.mjs` to match your brand identity.

## SEO & Best Practices

- **Dynamic Sitemap**: Generated automatically at `/sitemap-index.xml`.
- **Robots.txt**: Dynamically generated to point to your sitemap.
- **PWA Ready**: `manifest.json` included.
- **Structured Data**: Use the `<Schema />` component for JSON-LD.
