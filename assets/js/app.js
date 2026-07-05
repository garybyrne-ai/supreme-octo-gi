const navToggle = document.querySelector('.nav-toggle');
const nav = document.querySelector('#siteNav');

if (navToggle && nav) {
    const icon = navToggle.querySelector('i');
    const collapseGroups = () => {
        nav.querySelectorAll('.nav-item.is-expanded').forEach((item) => item.classList.remove('is-expanded'));
    };

    const closeNav = () => {
        nav.classList.remove('is-open');
        document.body.classList.remove('nav-open');
        navToggle.setAttribute('aria-expanded', 'false');
        if (icon) icon.className = 'fa-solid fa-bars';
        collapseGroups();
    };

    navToggle.addEventListener('click', () => {
        const open = nav.classList.toggle('is-open');
        document.body.classList.toggle('nav-open', open);
        navToggle.setAttribute('aria-expanded', String(open));
        if (icon) icon.className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        if (!open) collapseGroups();
    });

    nav.querySelectorAll('a').forEach((link) => {
        // Group headers (Services/Tools/Store/Company) toggle a mobile accordion
        // instead of closing the menu; only real destination links close it.
        if (link.parentElement && link.parentElement.classList.contains('nav-item')) {
            return;
        }
        link.addEventListener('click', closeNav);
    });

    // Mobile accordion: tap a group header to reveal its sub-links.
    nav.querySelectorAll('.has-mega > a').forEach((headerLink) => {
        headerLink.addEventListener('click', (event) => {
            if (!window.matchMedia('(max-width: 900px)').matches) {
                return; // desktop uses hover; let the link navigate
            }
            event.preventDefault();
            event.stopPropagation();
            const item = headerLink.closest('.nav-item');
            if (!item) return;
            const willOpen = !item.classList.contains('is-expanded');
            nav.querySelectorAll('.nav-item.is-expanded').forEach((other) => {
                if (other !== item) other.classList.remove('is-expanded');
            });
            item.classList.toggle('is-expanded', willOpen);
        });
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeNav();
    });
}

const glow = document.querySelector('.cursor-glow');
const progress = document.querySelector('.scroll-progress');
if (glow) {
    window.addEventListener('pointermove', (event) => {
        glow.style.left = `${event.clientX}px`;
        glow.style.top = `${event.clientY}px`;
    }, { passive: true });
}

function updateProgress() {
    if (!progress) return;
    const max = document.documentElement.scrollHeight - window.innerHeight;
    const amount = max > 0 ? (window.scrollY / max) * 100 : 0;
    progress.style.width = `${amount}%`;
}

window.addEventListener('scroll', updateProgress, { passive: true });
updateProgress();

const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.12 });

document.querySelectorAll('.reveal').forEach((element, index) => {
    element.style.transitionDelay = `${Math.min(index * 45, 220)}ms`;
    revealObserver.observe(element);
});

const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const target = entry.target;
        const raw = Number(target.dataset.count || 0);
        if (!raw || target.dataset.counted) return;
        target.dataset.counted = 'true';
        const suffix = target.dataset.suffix || '';
        const decimals = String(target.dataset.count).includes('.') ? 1 : 0;
        const start = performance.now();
        const duration = 900;
        function tick(now) {
            const progressAmount = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progressAmount, 3);
            const value = raw * eased;
            target.textContent = `${decimals ? value.toFixed(decimals) : Math.round(value)}${suffix}`;
            if (progressAmount < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
        counterObserver.unobserve(target);
    });
}, { threshold: 0.5 });

document.querySelectorAll('[data-count]').forEach((element) => counterObserver.observe(element));

if (window.matchMedia('(pointer: fine)').matches) {
    document.querySelectorAll('.pill-button, .icon-button, .cyber-card, .portfolio-card').forEach((element) => {
        element.addEventListener('pointermove', (event) => {
            const rect = element.getBoundingClientRect();
            const x = event.clientX - rect.left - rect.width / 2;
            const y = event.clientY - rect.top - rect.height / 2;
            element.style.transform = `translate(${x * 0.015}px, ${y * 0.015}px)`;
        });
        element.addEventListener('pointerleave', () => {
            element.style.transform = '';
        });
    });
}

const heroPanels = document.querySelector('.hero-panels');
if (heroPanels) {
    window.addEventListener('pointermove', (event) => {
        const x = (event.clientX / window.innerWidth - 0.5) * 18;
        const y = (event.clientY / window.innerHeight - 0.5) * 18;
        heroPanels.style.transform = `translate3d(${x}px, ${y}px, 0)`;
    }, { passive: true });
}

document.querySelectorAll('[data-account-widget]').forEach((widget) => {
    const trigger = widget.querySelector('.account-trigger');
    const popover = widget.querySelector('.account-popover');
    const message = widget.querySelector('.account-message');
    const endpoint = widget.dataset.formsEndpoint || '/account/forms';
    const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    let loaded = false;
    let hoverTimer = null;

    const setMessage = (text, isError = false) => {
        if (!message) return;
        message.textContent = text;
        message.classList.toggle('is-error', isError);
    };

    const loadForms = async (force = false) => {
        if (loaded && !force) return;
        try {
            const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
            const payload = await response.json();
            widget.querySelectorAll('input[name="_csrf"]').forEach((input) => {
                input.value = payload.csrf || '';
            });
            const loginCaptcha = widget.querySelector('[data-captcha="login"]');
            const registerCaptcha = widget.querySelector('[data-captcha="register"]');
            if (loginCaptcha) loginCaptcha.textContent = `${payload.loginCaptcha || ''} = ?`;
            if (registerCaptcha) registerCaptcha.textContent = `${payload.registerCaptcha || ''} = ?`;
            loaded = true;
            setMessage('');
        } catch (error) {
            setMessage('Could not load captcha. Please try again.', true);
        }
    };

    const openAccount = async () => {
        if (!popover) return;
        popover.hidden = false;
        widget.classList.add('is-open');
        document.body.classList.add('account-open');
        trigger?.setAttribute('aria-expanded', 'true');
        await loadForms();
        if (canHover) {
            widget.querySelector('.account-form.is-active input:not([type="hidden"])')?.focus();
        }
    };

    const closeAccount = () => {
        widget.classList.remove('is-open');
        document.body.classList.remove('account-open');
        if (popover) popover.hidden = true;
        trigger?.setAttribute('aria-expanded', 'false');
    };

    widget.addEventListener('account:open', () => {
        openAccount();
    });

    trigger?.addEventListener('click', (event) => {
        event.stopPropagation();
        if (widget.classList.contains('is-open')) {
            closeAccount();
            return;
        }
        openAccount();
    });

    if (canHover) {
        widget.addEventListener('mouseenter', () => {
            window.clearTimeout(hoverTimer);
            hoverTimer = window.setTimeout(openAccount, 120);
        });
        widget.addEventListener('mouseleave', () => {
            window.clearTimeout(hoverTimer);
            hoverTimer = window.setTimeout(closeAccount, 220);
        });
    }

    widget.querySelectorAll('[data-account-tab]').forEach((button) => {
        button.addEventListener('click', () => {
            const tab = button.dataset.accountTab;
            widget.querySelectorAll('[data-account-tab]').forEach((item) => item.classList.toggle('is-active', item === button));
            widget.querySelectorAll('[data-account-form]').forEach((form) => form.classList.toggle('is-active', form.dataset.accountForm === tab));
            setMessage('');
            loadForms();
        });
    });

    widget.querySelectorAll('[data-account-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            setMessage('Checking secure form...');
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { Accept: 'application/json' },
                    body: new FormData(form),
                });
                const payload = await response.json();
                setMessage(payload.message || (response.ok ? 'Done.' : 'Something went wrong.'), !response.ok || payload.ok === false);
                if (payload.ok) {
                    form.reset();
                    document.querySelectorAll('[data-tools-locked="true"]').forEach((element) => {
                        element.dataset.toolsLocked = 'false';
                    });
                    if (payload.redirect) {
                        window.setTimeout(() => { window.location.href = payload.redirect; }, 600);
                    }
                } else {
                    loaded = false;
                    await loadForms(true);
                }
            } catch (error) {
                setMessage('Request failed. Please try again.', true);
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!widget.contains(event.target)) closeAccount();
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeAccount();
    });
});

document.querySelectorAll('[data-mobile-account-trigger]').forEach((button) => {
    button.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        if (nav && navToggle) {
            nav.classList.remove('is-open');
            document.body.classList.remove('nav-open');
            navToggle.setAttribute('aria-expanded', 'false');
            const icon = navToggle.querySelector('i');
            if (icon) icon.className = 'fa-solid fa-bars';
        }
        const widget = document.querySelector('[data-account-widget]');
        if (!widget) return;
        window.setTimeout(() => {
            widget.dispatchEvent(new CustomEvent('account:open', { bubbles: false }));
            if (!widget.classList.contains('is-open')) {
                widget.querySelector('.account-trigger')?.click();
            }
        }, 0);
        const firstTab = widget.querySelector('[data-account-tab="login"]');
        if (firstTab && !firstTab.classList.contains('is-active')) {
            firstTab.click();
        }
    });
});

document.querySelectorAll('[data-copy-value]').forEach((button) => {
    button.addEventListener('click', async () => {
        const value = button.dataset.copyValue || '';
        try {
            await navigator.clipboard.writeText(value);
            button.classList.add('is-copied');
            button.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
            window.setTimeout(() => {
                button.classList.remove('is-copied');
                button.innerHTML = '<i class="fa-solid fa-copy"></i> Copy URL';
            }, 1600);
        } catch (error) {
            const input = button.parentElement?.querySelector('input');
            input?.select();
        }
    });
});

document.querySelectorAll('[data-admin-action-target]').forEach((button) => {
    button.addEventListener('click', () => {
        const panel = document.querySelector(button.dataset.adminActionTarget || '');
        if (!panel) return;

        document.querySelectorAll('[data-admin-action-target]').forEach((item) => {
            item.classList.toggle('is-active', item === button);
        });
        document.querySelectorAll('.admin-action-panel').forEach((item) => {
            item.classList.toggle('is-active', item === panel);
        });

        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.setTimeout(() => {
            panel.querySelector('input:not([type="hidden"]), textarea, select')?.focus({ preventScroll: true });
        }, 260);
    });
});

function updateLocalTime() {
    const target = document.querySelector('#localTime');
    if (!target) return;

    target.textContent = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
        timeZone: 'Asia/Kolkata',
    }).format(new Date());
}

updateLocalTime();
setInterval(updateLocalTime, 30000);

const passwordTool = document.querySelector('[data-password-tool]');
if (passwordTool) {
    const input = passwordTool.querySelector('input');
    const meter = passwordTool.querySelector('.strength-meter span');
    const output = passwordTool.querySelector('.tool-output');

    input?.addEventListener('input', () => {
        if (!toolsUnlocked()) {
            meter.style.width = '0';
            output.textContent = 'Register and verify your email to reveal tool results.';
            return;
        }
        const value = input.value;
        let score = 0;
        if (value.length >= 12) score += 30;
        if (value.length >= 16) score += 15;
        if (/[a-z]/.test(value)) score += 10;
        if (/[A-Z]/.test(value)) score += 10;
        if (/\d/.test(value)) score += 15;
        if (/[^a-zA-Z0-9]/.test(value)) score += 20;
        if (/(.)\1{2,}/.test(value) || /password|admin|qwerty|1234/i.test(value)) score -= 25;

        const finalScore = Math.max(0, Math.min(100, score));
        meter.style.width = `${finalScore}%`;
        output.textContent = finalScore >= 80
            ? 'Strong: good length and character variety.'
            : finalScore >= 55
                ? 'Medium: add length and symbols for stronger protection.'
                : 'Weak: use a longer unique passphrase.';
    });
}

const hashTool = document.querySelector('[data-hash-tool]');
function toolsUnlocked() {
    return !document.querySelector('[data-tools-locked="true"]');
}

if (hashTool && window.crypto?.subtle) {
    const textarea = hashTool.querySelector('textarea');
    const button = hashTool.querySelector('button');
    const output = hashTool.querySelector('output');

    button?.addEventListener('click', async () => {
        if (!toolsUnlocked()) {
            output.textContent = 'Register and verify your email to reveal tool results.';
            return;
        }
        const data = new TextEncoder().encode(textarea?.value || '');
        const digest = await crypto.subtle.digest('SHA-256', data);
        output.textContent = Array.from(new Uint8Array(digest))
            .map((byte) => byte.toString(16).padStart(2, '0'))
            .join('');
    });
}

function decodeBase64Url(value) {
    const padded = value.replace(/-/g, '+').replace(/_/g, '/').padEnd(Math.ceil(value.length / 4) * 4, '=');
    return decodeURIComponent(Array.from(atob(padded), (char) => `%${char.charCodeAt(0).toString(16).padStart(2, '0')}`).join(''));
}

const jwtTool = document.querySelector('[data-jwt-tool]');
if (jwtTool) {
    const textarea = jwtTool.querySelector('textarea');
    const button = jwtTool.querySelector('button');
    const output = jwtTool.querySelector('output');

    button?.addEventListener('click', () => {
        if (!toolsUnlocked()) {
            output.textContent = 'Register and verify your email to reveal tool results.';
            return;
        }
        try {
            const parts = (textarea?.value || '').trim().split('.');
            if (parts.length < 2) throw new Error('JWT must contain header and payload.');
            const decoded = {
                header: JSON.parse(decodeBase64Url(parts[0])),
                payload: JSON.parse(decodeBase64Url(parts[1])),
                signaturePresent: Boolean(parts[2]),
            };
            output.textContent = JSON.stringify(decoded, null, 2);
        } catch (error) {
            output.textContent = `Could not decode token: ${error.message}`;
        }
    });
}

const codecTool = document.querySelector('[data-codec-tool]');
if (codecTool) {
    const textarea = codecTool.querySelector('textarea');
    const output = codecTool.querySelector('output');

    codecTool.querySelectorAll('[data-codec]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!toolsUnlocked()) {
                output.textContent = 'Register and verify your email to reveal tool results.';
                return;
            }
            const value = textarea?.value || '';
            try {
                const mode = button.dataset.codec;
                if (mode === 'url-encode') output.textContent = encodeURIComponent(value);
                if (mode === 'url-decode') output.textContent = decodeURIComponent(value);
                if (mode === 'base64-encode') output.textContent = btoa(unescape(encodeURIComponent(value)));
                if (mode === 'base64-decode') output.textContent = decodeURIComponent(escape(atob(value)));
            } catch (error) {
                output.textContent = `Could not process input: ${error.message}`;
            }
        });
    });
}

const cspBuilder = document.querySelector('[data-csp-builder]');
if (cspBuilder) {
    const input = cspBuilder.querySelector('input');
    const output = cspBuilder.querySelector('output');
    cspBuilder.querySelectorAll('[data-csp-mode]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!toolsUnlocked()) {
                output.textContent = 'Register and verify your email to reveal tool results.';
                return;
            }
            const domains = (input?.value || '')
                .split(',')
                .map((item) => item.trim())
                .filter(Boolean)
                .map((item) => item.toLowerCase() === 'self' ? "'self'" : item.replace(/^https?:\/\//, 'https://'));
            const allowed = Array.from(new Set(["'self'", ...domains])).join(' ');
            const strict = button.dataset.cspMode === 'strict';
            output.textContent = [
                `default-src 'self'`,
                `base-uri 'self'`,
                `object-src 'none'`,
                `frame-ancestors 'self'`,
                `img-src ${allowed} data: blob:`,
                `script-src ${allowed}${strict ? '' : " 'unsafe-inline'"}`,
                `style-src ${allowed}${strict ? '' : " 'unsafe-inline'"}`,
                `connect-src ${allowed}`,
                `form-action 'self'`,
                `upgrade-insecure-requests`,
            ].join('; ');
        });
    });
}

const sriTool = document.querySelector('[data-sri-tool]');
if (sriTool && window.crypto?.subtle) {
    const textarea = sriTool.querySelector('textarea');
    const button = sriTool.querySelector('button');
    const output = sriTool.querySelector('output');
    button?.addEventListener('click', async () => {
        if (!toolsUnlocked()) {
            output.textContent = 'Register and verify your email to reveal tool results.';
            return;
        }
        const data = new TextEncoder().encode(textarea?.value || '');
        const digest = await crypto.subtle.digest('SHA-384', data);
        const binary = String.fromCharCode(...new Uint8Array(digest));
        output.textContent = `sha384-${btoa(binary)}`;
    });
}

const cookieAuditor = document.querySelector('[data-cookie-auditor]');
if (cookieAuditor) {
    const textarea = cookieAuditor.querySelector('textarea');
    const button = cookieAuditor.querySelector('button');
    const output = cookieAuditor.querySelector('output');
    button?.addEventListener('click', () => {
        if (!toolsUnlocked()) {
            output.textContent = 'Register and verify your email to reveal tool results.';
            return;
        }
        const value = (textarea?.value || '').toLowerCase();
        const checks = [
            ['Secure', value.includes('secure')],
            ['HttpOnly', value.includes('httponly')],
            ['SameSite', value.includes('samesite=lax') || value.includes('samesite=strict')],
            ['Path scoped', value.includes('path=')],
            ['No public domain wildcard', !/domain=\.[^;\s]+/.test(value)],
        ];
        const score = Math.round((checks.filter(([, ok]) => ok).length / checks.length) * 100);
        output.textContent = `${score}/100\n${checks.map(([label, ok]) => `${ok ? 'PASS' : 'FIX'} - ${label}`).join('\n')}`;
    });
}

const serpPreviewBuilder = document.querySelector('[data-serp-preview-builder]');
if (serpPreviewBuilder) {
    const button = serpPreviewBuilder.querySelector('button');
    const output = serpPreviewBuilder.querySelector('output');
    button?.addEventListener('click', () => {
        if (!toolsUnlocked()) {
            output.textContent = 'Register and verify your email to reveal tool results.';
            return;
        }
        const title = serpPreviewBuilder.querySelector('[name="title"]')?.value || 'Missing page title';
        const url = serpPreviewBuilder.querySelector('[name="url"]')?.value || 'https://example.com/page';
        const description = serpPreviewBuilder.querySelector('[name="description"]')?.value || 'Missing meta description.';
        const titleStatus = title.length <= 60 ? 'good' : 'trim title';
        const descStatus = description.length >= 120 && description.length <= 160 ? 'good' : 'tune description';
        output.innerHTML = `<div class="serp-preview"><span>${escapeHtml(url)}</span><strong>${escapeHtml(title)}</strong><p>${escapeHtml(description)}</p></div><p>Title: ${title.length} chars (${titleStatus}). Description: ${description.length} chars (${descStatus}).</p>`;
    });
}

const keywordDensity = document.querySelector('[data-keyword-density]');
if (keywordDensity) {
    const button = keywordDensity.querySelector('button');
    const output = keywordDensity.querySelector('output');
    button?.addEventListener('click', () => {
        if (!toolsUnlocked()) {
            output.textContent = 'Register and verify your email to reveal tool results.';
            return;
        }
        const keyword = (keywordDensity.querySelector('[name="keyword"]')?.value || '').trim().toLowerCase();
        const copy = keywordDensity.querySelector('[name="copy"]')?.value || '';
        const words = copy.toLowerCase().match(/[a-z0-9]+/g) || [];
        const phrase = keyword.split(/\s+/).filter(Boolean);
        let hits = 0;
        if (phrase.length) {
            for (let index = 0; index <= words.length - phrase.length; index += 1) {
                if (phrase.every((word, offset) => words[index + offset] === word)) hits += 1;
            }
        }
        const density = words.length ? ((hits * Math.max(phrase.length, 1)) / words.length) * 100 : 0;
        const verdict = density > 3
            ? { label: 'Over-optimised', tone: 'bad', note: 'Reduce repetition and use related terms.' }
            : density > 0.4
                ? { label: 'Healthy', tone: 'good', note: 'Good range for focused copy.' }
                : { label: 'Too thin', tone: 'warn', note: 'Add the keyword naturally in headings and body.' };
        const barWidth = Math.min(100, Math.round((density / 4) * 100));
        output.innerHTML = `
            <div class="seo-metric-grid">
                <div class="seo-metric"><b>${words.length}</b><span>Total words</span></div>
                <div class="seo-metric"><b>${hits}</b><span>Exact matches</span></div>
                <div class="seo-metric"><b>${density.toFixed(2)}%</b><span>Keyword density</span></div>
            </div>
            <div class="seo-density-bar"><i style="width:${barWidth}%"></i></div>
            <p class="seo-verdict is-${verdict.tone}"><b>${verdict.label}.</b> ${verdict.note}</p>`;
    });
}

const schemaValidator = document.querySelector('[data-schema-validator]');
if (schemaValidator) {
    const textarea = schemaValidator.querySelector('textarea');
    const button = schemaValidator.querySelector('button');
    const output = schemaValidator.querySelector('output');
    button?.addEventListener('click', () => {
        if (!toolsUnlocked()) {
            output.textContent = 'Register and verify your email to reveal tool results.';
            return;
        }
        try {
            const schema = JSON.parse(textarea?.value || '{}');
            const warnings = [];
            if (!schema['@context']) warnings.push('Add @context.');
            if (!schema['@type']) warnings.push('Add @type.');
            if (!schema.name && !schema.headline) warnings.push('Add name or headline.');
            const type = escapeHtml(String(schema['@type'] || 'Unknown'));
            if (warnings.length) {
                output.innerHTML = `<p class="seo-verdict is-warn"><b>Valid JSON — schema needs work.</b> Type: <code>${type}</code></p><ul class="seo-check-list">${warnings.map((w) => `<li class="is-warn"><i class="fa-solid fa-triangle-exclamation"></i>${escapeHtml(w)}</li>`).join('')}</ul>`;
            } else {
                output.innerHTML = `<p class="seo-verdict is-good"><b>Valid JSON-LD.</b> Core fields present for <code>${type}</code>.</p><ul class="seo-check-list"><li class="is-good"><i class="fa-solid fa-circle-check"></i>@context present</li><li class="is-good"><i class="fa-solid fa-circle-check"></i>@type present</li><li class="is-good"><i class="fa-solid fa-circle-check"></i>Name / headline present</li></ul>`;
            }
        } catch (error) {
            output.innerHTML = `<p class="seo-verdict is-bad"><b>Invalid JSON.</b> ${escapeHtml(error.message)}</p>`;
        }
    });
}

const robotsBuilder = document.querySelector('[data-robots-builder]');
if (robotsBuilder) {
    const button = robotsBuilder.querySelector('button');
    const output = robotsBuilder.querySelector('output');
    button?.addEventListener('click', () => {
        if (!toolsUnlocked()) {
            output.textContent = 'Register and verify your email to reveal tool results.';
            return;
        }
        const checked = (name) => robotsBuilder.querySelector(`[name="${name}"]`)?.checked;
        const directives = [
            checked('index') ? 'index' : 'noindex',
            checked('follow') ? 'follow' : 'nofollow',
        ];
        if (checked('archive')) directives.push('noarchive');
        if (checked('snippet')) directives.push('nosnippet');
        const tag = `<meta name="robots" content="${directives.join(', ')}">`;
        output.innerHTML = `<pre class="seo-code"><code>${escapeHtml(tag)}</code></pre><button class="pill-button ghost seo-copy-btn" type="button">Copy tag <i class="fa-solid fa-copy"></i></button>`;
        const copyBtn = output.querySelector('.seo-copy-btn');
        copyBtn?.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(tag);
                copyBtn.innerHTML = 'Copied <i class="fa-solid fa-check"></i>';
                setTimeout(() => { copyBtn.innerHTML = 'Copy tag <i class="fa-solid fa-copy"></i>'; }, 1600);
            } catch (error) { /* clipboard unavailable */ }
        });
    });
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));
}

const refreshTechTicker = () => {
    const ticker = document.querySelector('.tech-news-ticker .ticker-lane');
    if (!ticker) return;

    fetch('/tech-news-feed', { headers: { Accept: 'application/json' } })
        .then((response) => response.ok ? response.json() : null)
        .then((payload) => {
            const items = Array.isArray(payload?.items) ? payload.items : [];
            if (!items.length) return;

            ticker.innerHTML = [...items, ...items].map((item) => {
                const url = String(item.url || '/blog');
                const external = !url.startsWith('/');
                const attrs = external ? ' target="_blank" rel="noopener"' : '';
                return `<a href="${escapeHtml(url)}"${attrs}><span>${escapeHtml(item.source || 'Tech')}</span><strong>${escapeHtml(item.title || 'Latest technology update')}</strong><em>${escapeHtml(item.date || '')}</em></a>`;
            }).join('');
        })
        .catch(() => {});
};

if ('requestIdleCallback' in window) {
    window.requestIdleCallback(refreshTechTicker, { timeout: 3500 });
} else {
    window.addEventListener('load', () => window.setTimeout(refreshTechTicker, 1200), { once: true });
}

document.querySelectorAll('[data-interactive-tool]').forEach((tool) => {
    const form = tool.querySelector('.interactive-form');
    const output = tool.querySelector('.result-output');
    const resultCard = tool.querySelector('.interactive-result');
    const mode = tool.dataset.interactiveTool;
    const locked = tool.dataset.toolsLocked === 'true';

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!output || !resultCard) return;
        if (locked) {
            output.innerHTML = '<div class="notice error">Register and verify your email above to reveal the full result.</div>';
            return;
        }

        const data = Object.fromEntries(new FormData(form).entries());
        const checked = (name) => Boolean(form.querySelector(`[name="${name}"]`)?.checked);
        const text = (name, fallback) => escapeHtml(data[name] || fallback);
        let html = '';

        if (mode === 'growth-consultant') {
            html = `<h3>Growth Plan</h3><ul class="check-list">
                <li>Create dedicated SEO pages for ${text('region', 'your target region')} and your highest-value services.</li>
                <li>Launch PPC to test which keywords create enquiries fastest.</li>
                <li>Add AI lead qualification and CRM follow-up for ${text('business', 'your business')}.</li>
                <li>Improve conversion around the main goal: ${text('goal', 'more qualified leads')}.</li>
            </ul>`;
        } else if (mode === 'quote-calculator') {
            const pages = Number(data.pages || 1);
            let base = pages * 180 + 900;
            if (data.type?.includes('Ecommerce')) base += 1400;
            if (data.type?.includes('App')) base += 3200;
            if (checked('seo')) base += 650;
            if (checked('ai')) base += 1500;
            if (checked('ppc')) base += 700;
            html = `<h3>Estimated Range</h3><strong class="big-result">EUR ${base.toLocaleString()} - EUR ${Math.round(base * 1.65).toLocaleString()}</strong><p>Final quote depends on content, integrations, design depth and launch speed.</p>`;
        } else if (mode === 'security-badge') {
            const score = Math.round((Number(data.headers || 0) + Number(data.tls || 0) + Number(data.dns || 0)) / 3);
            html = `<div class="security-badge-preview"><span>SECURITY CHECKED</span><strong>${score}/100</strong><small>${text('site', 'your website')}</small></div><p>Use the full security tools to validate the score before publishing a badge.</p>`;
        } else if (mode === 'client-portal') {
            const focus = escapeHtml(data.focus || data.service || 'Growth');
            html = `<div class="portal-preview"><h3>${text('client', 'Client')} Portal</h3><div><span>Open Tickets</span><strong>3</strong></div><div><span>Ranking Actions</span><strong>12</strong></div><div><span>Current Focus</span><strong>${focus}</strong></div><div><span>Next Milestone</span><strong>Friday</strong></div></div>`;
        } else if (mode === 'ppc-roi') {
            const budget = Number(data.budget || 0);
            const cpc = Math.max(Number(data.cpc || 1), .01);
            const clicks = Math.floor(budget / cpc);
            const leads = Math.round(clicks * (Number(data.conversion || 0) / 100));
            const revenue = leads * Number(data.value || 0);
            const roas = budget ? (revenue / budget).toFixed(2) : '0.00';
            html = `<h3>PPC Projection</h3><div class="result-metrics"><span><b>${clicks}</b> clicks</span><span><b>${leads}</b> leads</span><span><b>EUR ${revenue.toLocaleString()}</b> potential revenue</span><span><b>${roas}x</b> ROAS</span></div>`;
        } else if (mode === 'speed-simulator') {
            const current = Number(data.current || 0);
            const target = Number(data.target || 0);
            const visitors = Number(data.visitors || 0);
            const conversion = Number(data.conversion || 0) / 100;
            const lift = Math.max(0, target - current) / 100;
            const extra = Math.round(visitors * conversion * lift);
            html = `<h3>Before / After</h3><div class="speed-bars"><span style="--score:${current}%">Before ${current}</span><span style="--score:${target}%">After ${target}</span></div><p>Potential extra monthly leads from speed confidence: <strong>${extra}</strong>.</p>`;
        } else {
            const ideas = [];
            if (checked('lead')) ideas.push('AI lead intake, qualification and routing.');
            if (checked('support')) ideas.push('Support ticket summaries and reply drafts.');
            if (checked('crm')) ideas.push('CRM updates, lead scoring and follow-up drafts.');
            if (checked('reports')) ideas.push('Weekly report summaries and action lists.');
            if (checked('booking')) ideas.push('Booking reminders, onboarding and status updates.');
            html = `<h3>Automation Map</h3><ul class="check-list">${(ideas.length ? ideas : ['Select at least one workflow to map.']).map((item) => `<li>${item}</li>`).join('')}</ul>`;
        }

        output.innerHTML = html;
        resultCard.classList.add('has-result');
    });
});

document.querySelectorAll('[data-chatbot]').forEach((chatbot) => {
    const toggle = chatbot.querySelector('.chatbot-toggle');
    const panel = chatbot.querySelector('.chatbot-panel');
    const close = chatbot.querySelector('.chatbot-head button');
    const messages = chatbot.querySelector('.chatbot-messages');
    const form = chatbot.querySelector('.chatbot-form');
    const input = form?.querySelector('input[name="message"]');
    const state = { name: '', email: '', interest: '', askedLead: false };

    const addMessage = (role, text) => {
        if (!messages) return;
        const item = document.createElement('div');
        item.className = `chat-message ${role}`;
        item.textContent = text;
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
    };

    const serviceReply = (value) => {
        const textValue = value.toLowerCase();
        if (/\b(seo|rank|google|serp|search)\b/.test(textValue)) {
            state.interest = 'SEO and SERP growth';
            return 'We provide technical SEO, local SEO pages for Ireland/UK/USA/Europe, SERP checks, content architecture, schema, Core Web Vitals and ranking strategy. For fast wins, start with the SEO Audit Tool or book an SEO growth plan.';
        }
        if (/\b(ppc|ads|google ads|paid|campaign)\b/.test(textValue)) {
            state.interest = 'PPC advertising';
            return 'We build PPC landing pages, conversion tracking, Google Ads strategy, keyword testing, offer matching and ROI forecasting. The PPC ROI calculator can estimate clicks, leads and ROAS before spend goes live.';
        }
        if (/\b(ai|automation|chatbot|workflow|crm)\b/.test(textValue)) {
            state.interest = 'AI integration and automation';
            return 'We integrate AI into existing websites, CRMs and workflows for lead qualification, support summaries, reply drafts, reports, bookings and internal automation. Everything is designed with human approval where it matters.';
        }
        if (/\b(app|mobile|android|ios|portal|dashboard)\b/.test(textValue)) {
            state.interest = 'App or portal development';
            return 'We build web apps, client portals, dashboards, Android/iOS-ready experiences, APIs and support systems. The best starting point is a scope map: users, roles, data, workflows and launch priorities.';
        }
        if (/\b(ecommerce|shop|store|woocommerce|shopify|checkout)\b/.test(textValue)) {
            state.interest = 'Ecommerce development';
            return 'We build ecommerce stores focused on product discovery, checkout trust, automation, speed, SEO and abandoned-cart recovery. We can work with WooCommerce, Shopify-style flows or custom PHP/MySQL commerce systems.';
        }
        if (/\b(security|pentest|penetration|hack|malware|headers)\b/.test(textValue)) {
            state.interest = 'Website security and penetration testing';
            return 'We provide defensive security reviews, header checks, TLS/DNS checks, form hardening, admin protection, secure PHP practices and penetration-testing support. The free security tools are at the bottom of the homepage.';
        }
        if (/\b(price|cost|quote|budget|how much)\b/.test(textValue)) {
            state.interest = 'Quote request';
            return 'Pricing depends on pages, content, integrations, AI, SEO, PPC and support needs. Use the quote calculator for a quick range, or share your email here and we can follow up with a project estimate.';
        }
        state.interest ||= 'General service enquiry';
        return 'Crest Web Media provides website development, app development, SEO, PPC, AI automation, ecommerce, security checks, support tickets and client portals. Ask me about any service, or send your name and email and I will capture the lead locally.';
    };

    const maybeCaptureLead = async (textValue) => {
        const email = textValue.match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i)?.[0] || '';
        if (!email) return false;
        const nameMatch = textValue.match(/(?:name is|i am|i'm|this is)\s+([a-z ,.'-]{2,40})/i);
        state.name = nameMatch ? nameMatch[1].trim() : state.name || 'Website visitor';
        state.email = email;

        try {
            const response = await fetch(chatbot.dataset.chatEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: state.name,
                    email: state.email,
                    interest: state.interest || 'Chatbot enquiry',
                    message: textValue,
                }),
            });
            const result = await response.json();
            addMessage('bot', result.message || 'Thanks. Your details have been saved.');
        } catch (error) {
            addMessage('bot', 'I can answer offline, but the lead save failed. Please use the contact page as a backup.');
        }
        return true;
    };

    const openChat = () => {
        if (!panel) return;
        panel.hidden = false;
        chatbot.classList.add('is-open');
        if (!messages?.children.length) {
            addMessage('bot', 'Hi, I can answer questions about Crest Web Media services without using an external API. Ask about websites, SEO, PPC, AI, apps, ecommerce or security.');
        }
        input?.focus();
    };

    const closeChat = () => {
        chatbot.classList.remove('is-open');
        if (panel) panel.hidden = true;
    };

    toggle?.addEventListener('click', () => {
        if (chatbot.classList.contains('is-open')) {
            closeChat();
            return;
        }
        openChat();
    });
    close?.addEventListener('click', closeChat);

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const value = input?.value.trim() || '';
        if (!value) return;
        addMessage('user', value);
        if (input) input.value = '';
        if (await maybeCaptureLead(value)) return;
        addMessage('bot', serviceReply(value));
        if (!state.askedLead && state.interest) {
            state.askedLead = true;
            addMessage('bot', 'If you want a follow-up, reply with your email. Example: "I am Alex, alex@example.com".');
        }
    });
});

/* Tool report: white-label branding (persisted) + print-to-PDF. */
document.querySelectorAll('[data-tool-report]').forEach(function (report) {
    var nameInput = report.querySelector('[data-wl-input-name]');
    var logoInput = report.querySelector('[data-wl-input-logo]');
    var wlName = report.querySelector('[data-wl-name]');
    var wlLogo = report.querySelector('[data-wl-logo]');
    var wlFooter = report.querySelector('[data-wl-footer]');
    var dateEl = report.querySelector('[data-report-date]');
    var printBtn = report.querySelector('[data-report-print]');
    var store = {};
    try { store = JSON.parse(localStorage.getItem('cwm_whitelabel') || '{}'); } catch (e) { store = {}; }

    function apply() {
        var name = (nameInput && nameInput.value.trim()) || store.name || '';
        var logo = (logoInput && logoInput.value.trim()) || store.logo || '';
        var brand = name || 'Crest Web Media';
        if (wlName) { wlName.textContent = brand; }
        if (wlFooter) { wlFooter.textContent = 'Generated by ' + brand + ' Growth Lab'; }
        if (wlLogo) {
            if (logo) { wlLogo.src = logo; wlLogo.hidden = false; }
            else { wlLogo.removeAttribute('src'); wlLogo.hidden = true; }
        }
    }

    if (nameInput && store.name) { nameInput.value = store.name; }
    if (logoInput && store.logo) { logoInput.value = store.logo; }
    if (dateEl) { dateEl.textContent = new Date().toISOString().slice(0, 10); }

    [nameInput, logoInput].forEach(function (inp) {
        if (!inp) { return; }
        inp.addEventListener('input', function () {
            store.name = nameInput ? nameInput.value.trim() : '';
            store.logo = logoInput ? logoInput.value.trim() : '';
            try { localStorage.setItem('cwm_whitelabel', JSON.stringify(store)); } catch (e) {}
            apply();
        });
    });

    apply();
    if (printBtn) { printBtn.addEventListener('click', function () { apply(); window.print(); }); }
});

// --- Enterprise hero interactions: cursor spotlight, tile glow, 3D tilt ---
(function () {
    const hero = document.querySelector('[data-hero]');
    if (!hero) return;

    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!finePointer) return;

    // Backdrop spotlight follows the cursor across the hero.
    hero.addEventListener('pointermove', (event) => {
        const rect = hero.getBoundingClientRect();
        hero.style.setProperty('--mx', (((event.clientX - rect.left) / rect.width) * 100).toFixed(2) + '%');
        hero.style.setProperty('--my', (((event.clientY - rect.top) / rect.height) * 100).toFixed(2) + '%');
        hero.classList.add('is-spotlit');
    }, { passive: true });
    hero.addEventListener('pointerleave', () => hero.classList.remove('is-spotlit'));

    // Service tiles: glow tracks the cursor inside each tile.
    hero.querySelectorAll('.hero-service-grid a').forEach((tile) => {
        tile.addEventListener('pointermove', (event) => {
            const rect = tile.getBoundingClientRect();
            tile.style.setProperty('--px', (event.clientX - rect.left) + 'px');
            tile.style.setProperty('--py', (event.clientY - rect.top) + 'px');
        }, { passive: true });
    });

    // 3D tilt + glare on the DIRECT SIGNAL / LOCAL TIME panels.
    if (!reducedMotion) {
        hero.querySelectorAll('[data-tilt]').forEach((card) => {
            let raf = 0;
            card.addEventListener('pointermove', (event) => {
                const rect = card.getBoundingClientRect();
                const px = (event.clientX - rect.left) / rect.width;
                const py = (event.clientY - rect.top) / rect.height;
                cancelAnimationFrame(raf);
                raf = requestAnimationFrame(() => {
                    card.classList.add('is-tilting');
                    card.style.transform =
                        'perspective(720px) rotateX(' + ((0.5 - py) * 9).toFixed(2) + 'deg)' +
                        ' rotateY(' + ((px - 0.5) * 11).toFixed(2) + 'deg) translateY(-2px)';
                    card.style.setProperty('--gx', (px * 100).toFixed(1) + '%');
                    card.style.setProperty('--gy', (py * 100).toFixed(1) + '%');
                });
            }, { passive: true });
            card.addEventListener('pointerleave', () => {
                cancelAnimationFrame(raf);
                card.classList.remove('is-tilting');
                card.style.transform = '';
            });
        });
    }
})();

// --- GDPR cookie consent (Google Consent Mode v2) ---
(function () {
    var banner = document.getElementById('cookieConsent');
    var reopen = document.getElementById('cookieReopen');
    if (!banner) { return; }

    var readCookie = function (name) {
        return document.cookie.split('; ').reduce(function (acc, c) {
            var parts = c.split('=');
            return parts[0] === name ? decodeURIComponent(parts[1] || '') : acc;
        }, '');
    };
    var setCookie = function (name, value) {
        var oneYear = 60 * 60 * 24 * 365;
        var secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = name + '=' + encodeURIComponent(value) + '; Max-Age=' + oneYear + '; Path=/; SameSite=Lax' + secure;
    };

    var gtagSafe = function () {
        if (typeof window.gtag === 'function') { window.gtag.apply(window, arguments); }
    };

    var injectAdsense = function () {
        var client = banner.getAttribute('data-adsense');
        if (!client || document.querySelector('script[data-adsense-loader]')) { return; }
        var s = document.createElement('script');
        s.async = true;
        s.crossOrigin = 'anonymous';
        s.setAttribute('data-adsense-loader', '1');
        s.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' + encodeURIComponent(client);
        document.head.appendChild(s);
    };

    var showBanner = function () { banner.hidden = false; if (reopen) { reopen.hidden = true; } };
    var hideBanner = function () { banner.hidden = true; if (reopen) { reopen.hidden = false; } };

    var accept = function () {
        setCookie('cwm_consent', 'granted');
        gtagSafe('consent', 'update', {
            ad_storage: 'granted', ad_user_data: 'granted',
            ad_personalization: 'granted', analytics_storage: 'granted'
        });
        injectAdsense();
        hideBanner();
    };
    var reject = function () {
        setCookie('cwm_consent', 'denied');
        gtagSafe('consent', 'update', {
            ad_storage: 'denied', ad_user_data: 'denied',
            ad_personalization: 'denied', analytics_storage: 'denied'
        });
        hideBanner();
    };

    banner.querySelectorAll('[data-consent]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.getAttribute('data-consent') === 'accept' ? accept() : reject();
        });
    });
    if (reopen) { reopen.addEventListener('click', showBanner); }
    // Let a link anywhere (e.g. cookie policy page) reopen the chooser.
    document.querySelectorAll('[data-open-consent]').forEach(function (el) {
        el.addEventListener('click', function (e) { e.preventDefault(); showBanner(); });
    });

    var choice = readCookie('cwm_consent');
    if (choice === 'granted' || choice === 'denied') {
        hideBanner();
    } else {
        showBanner();
    }
})();

// --- External openers for the header account popover (login/register gate) ---
(function () {
    var widget = document.querySelector('[data-account-widget]');
    if (!widget) { return; }
    document.querySelectorAll('[data-open-account]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            var tab = el.getAttribute('data-open-account');
            if (tab === 'register' || tab === 'login') {
                var tabBtn = widget.querySelector('[data-account-tab="' + tab + '"]');
                if (tabBtn) { tabBtn.click(); }
            }
            widget.dispatchEvent(new CustomEvent('account:open'));
            widget.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });
})();

// --- Referral capture: store ?ref=CODE in a 30-day cookie for attribution ---
(function () {
    try {
        var ref = new URLSearchParams(window.location.search).get('ref');
        if (!ref) { return; }
        ref = ref.replace(/[^A-Za-z0-9]/g, '').slice(0, 20);
        if (!ref) { return; }
        var secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = 'cwm_ref=' + ref + '; Max-Age=' + (60 * 60 * 24 * 30) + '; Path=/; SameSite=Lax' + secure;
    } catch (e) {}
})();

// --- Generic copy-to-clipboard for [data-copy-target] buttons ---
(function () {
    document.querySelectorAll('[data-copy-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var el = document.querySelector(btn.getAttribute('data-copy-target'));
            if (!el) { return; }
            el.select && el.select();
            try { navigator.clipboard.writeText(el.value || el.textContent); } catch (e) { try { document.execCommand('copy'); } catch (e2) {} }
            var original = btn.innerHTML;
            btn.innerHTML = 'Copied! <i class="fa-solid fa-check"></i>';
            window.setTimeout(function () { btn.innerHTML = original; }, 1800);
        });
    });
})();

// --- Open the header account popover on the Register tab from any CTA ---
(function () {
    document.querySelectorAll('[data-open-register]').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            var widget = document.querySelector('[data-account-widget]');
            if (!widget) { window.location.href = '/tools-pricing'; return; }
            var registerTab = widget.querySelector('[data-account-tab="register"]');
            if (registerTab) { registerTab.click(); }
            widget.dispatchEvent(new CustomEvent('account:open'));
            widget.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
})();

// --- AI content assistant: copy the preceding [data-copy-source] text ---
(function () {
    document.querySelectorAll('[data-copy-prev]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var source = btn.parentElement && btn.parentElement.querySelector('[data-copy-source]');
            if (!source) { return; }
            var text = source.textContent || '';
            try { navigator.clipboard.writeText(text); } catch (e) { try { document.execCommand('copy'); } catch (e2) {} }
            var original = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i>';
            window.setTimeout(function () { btn.innerHTML = original; }, 1500);
        });
    });
})();
