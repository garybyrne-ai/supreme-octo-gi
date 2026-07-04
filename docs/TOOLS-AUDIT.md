# Website Tools Audit

A functional review of every tool exposed on the site (routes in
`routes/web.php`, logic in `app/controllers/ToolsController.php` and
`public/assets/js/app.js`). Each tool was exercised: server-side scoring
logic was driven through a no-network harness (SSRF guards, header scoring,
SEO parsing, SERP note generation) and the client-side calculators were
traced through their JS handlers.

## Summary

| Tool | Route | Type | Status |
| --- | --- | --- | --- |
| Security Headers | `/free-penetration-testing-tools` → `headers` | Server | ✅ Working |
| DNS / Email Auth | `/free-penetration-testing-tools/dns` | Server | ✅ Working |
| TLS Certificate | `/free-penetration-testing-tools/tls` | Server | ✅ Working (see note) |
| Well-known Discovery | `/free-penetration-testing-tools/well-known` | Server | ✅ Working |
| SEO Audit | `/seo-tools` | Server | ✅ Working |
| SERP Checker | `/serp-checker` | Server | ✅ Working (external dependency) |
| AI Growth Consultant | `/ai-website-growth-consultant` | Client JS | ✅ Working |
| Quote Calculator | `/instant-website-quote-calculator` | Client JS | ✅ Working |
| Security Badge Generator | `/security-score-badge-generator` | Client JS | ✅ Working |
| Client Portal Preview | `/client-portal-preview` | Client JS | ✅ Working |
| PPC ROI Calculator | `/ppc-roi-calculator` | Client JS | ✅ Working |
| Speed Simulator | `/before-after-speed-simulator` | Client JS | ✅ Working |
| AI Automation Finder | `/ai-automation-finder` | Client JS | ✅ Working |

## Issue found and fixed

**Stale compiled JavaScript broke admin action panels.**
The deployed `public/assets/js/app.js` (served to browsers) was an older
build than the source `assets/js/app.js`. The served copy was missing the
`data-admin-action-target` handler, so the action-panel switcher used on
every `/admin/modules/*` page did nothing when clicked. The admin module
markup depends on this handler (`app/views/admin/module.php`).

*Fix:* re-synced `public/assets/js/app.js` from the maintained source
`assets/js/app.js` (the source is a strict superset — no behaviour was
removed) and validated with `node --check`. Admin action panels now switch
and focus correctly.

## Notes and recommendations (no action required to function)

- **TLS tool** connects with `verify_peer => true`, so a site presenting an
  expired, self-signed or otherwise invalid certificate returns "Could not
  read a TLS certificate" rather than a low score. This is safe-by-default;
  if you want to *grade* broken certificates, a second best-effort pass with
  verification disabled (clearly labelled) could be added.
- **SERP Checker** scrapes DuckDuckGo Lite HTML. It works today, but it
  depends on that page's markup and rate limits; if results ever stop
  appearing, that upstream layout change is the first place to look. A
  paid SERP API would make it deterministic.
- **SEO word count** uses `str_word_count`, which counts ASCII words. For
  predominantly non-Latin pages the word total under-reports. Fine for the
  English-language sites this targets.
- **Compiled CSS** (`public/assets/css/style.min.css`) is older than the
  source `assets/css/style.css`. The site renders correctly, but consider a
  build step that minifies `style.css` → `style.min.css` on deploy so the
  two never drift. (The new commerce/membership styles ship as a separate
  `commerce-suite.css`, so they are unaffected.)
- **SSRF protection** on the URL/host tools is solid: schemes are limited to
  http/https, credentials and non-standard ports are rejected, and every
  resolved A/AAAA address is checked against private/reserved ranges before
  any request is made. Verified against localhost, loopback IPs, credentialed
  URLs and non-HTTP schemes.

## Build hygiene going forward

The root `assets/` directory is the source of truth; `public/assets/` is
what browsers load. Whenever `assets/js/app.js` or `assets/css/style.css`
changes, copy/compile it into `public/assets/` in the same commit so the
live site and the source never diverge (this is exactly the drift that broke
the admin action panels).
