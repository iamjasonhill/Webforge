# WordPress Scaffolding - Research Notes

> Parking this for later review. Want to be clear on approach before building.

---

## Philosophy

**Goal**: Minimize plugins, reduce bloat, prioritize performance and security.

### The Plugin Problem

- Each plugin adds DB queries, HTTP requests, PHP execution
- More plugins = larger attack surface
- Update dependencies can break sites
- Many plugins include unused features

### Approach: Code What You Can, Plugin Only What You Must

---

## What We Could Code in Theme

| Feature            | Implementation                                |
| ------------------ | --------------------------------------------- |
| SEO meta tags      | Custom `<head>` output in `functions.php`     |
| JSON-LD schemas    | PHP function in theme                         |
| Security headers   | `.htaccess` or `nginx.conf` + `wp-config.php` |
| Disable bloat      | Remove emojis, embeds, XML-RPC                |
| Image lazy loading | Native `loading="lazy"`                       |
| Custom post types  | `register_post_type()`                        |
| Analytics          | Direct GA4/GTM code                           |
| robots.txt         | Physical file                                 |

---

## Plugins Worth Using (Essential Only)

| Plugin                   | Reason                                     |
| ------------------------ | ------------------------------------------ |
| Yoast/RankMath           | Sitemap, canonicals - complex to code well |
| WP Super Cache/LiteSpeed | Caching is complex                         |
| Wordfence/Sucuri         | Security scanning, firewall                |
| UpdraftPlus              | Backups - critical                         |

---

## Plugins to Skip (Bloat)

- Social sharing buttons
- Contact form plugins (unless complex needs)
- Sliders
- Page builders (use Gutenberg instead)
- "All-in-one" plugins with 100 features

---

## What WebForge Could Scaffold

1. **Starter theme** - SEO functions, security hardening, performance
   optimizations
2. **wp-config.php** - Security constants, performance settings
3. **.htaccess** - Security headers, caching, WebP
4. **Audit checks** - Debug mode, permissions, plugin count

---

## Open Questions

- [ ] Theme approach: From scratch or modify Underscores (`_s`)?
- [ ] SEO: Code our own meta tags or assume Yoast/RankMath?
- [ ] Gutenberg: Embrace it or classic editor?
- [ ] Target hosting: Generic or optimize for specific hosts?

---

## Next Steps

When ready to proceed:

1. Answer open questions above
2. Create implementation plan
3. Build scaffolding
