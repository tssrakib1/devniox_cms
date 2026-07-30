import 'bootstrap';

const root = document.documentElement;
const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
let theme = systemTheme;

try {
    theme = localStorage.getItem('theme') || systemTheme;
} catch {
    // Storage can be unavailable in privacy-restricted browsing contexts.
}

const applyTheme = (value) => {
    root.setAttribute('data-bs-theme', value);
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(value === 'dark'));
        const icon = button.querySelector('i');
        if (icon) icon.className = value === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
    });
};

applyTheme(theme);

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        theme = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(theme);

        try {
            localStorage.setItem('theme', theme);
        } catch {
            // Theme still applies for the current page when persistence is unavailable.
        }
    });
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (! window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented || ! form.checkValidity()) return;

        const submitter = event.submitter;
        if (! submitter || submitter.dataset.loading === 'false') return;

        submitter.disabled = true;
        submitter.setAttribute('aria-disabled', 'true');
        submitter.dataset.submitLabel = submitter.innerHTML;
        submitter.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Working…';
    });
});

document.querySelectorAll('[data-select-all]').forEach((control) => {
    control.addEventListener('change', () => {
        control.closest('table')?.querySelectorAll('tbody input[type="checkbox"]').forEach((checkbox) => { checkbox.checked = control.checked; });
    });
});

document.querySelectorAll('[data-repeater]').forEach((repeater) => {
    const items = repeater.querySelector('[data-repeater-items]');
    const template = repeater.querySelector('template');
    let nextIndex = items?.children.length || 0;
    repeater.querySelector('[data-repeater-add]')?.addEventListener('click', () => {
        items?.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextIndex++)));
    });
    repeater.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-repeater-remove]');
        if (remove) remove.closest('.repeater-item')?.remove();
    });
});

const slugSource = document.querySelector('[data-slug-source]');
const slugTarget = document.querySelector('[data-slug-target]');
if (slugSource && slugTarget) {
    let slugEdited = Boolean(slugTarget.value);
    slugTarget.addEventListener('input', () => { slugEdited = true; });
    slugSource.addEventListener('input', () => {
        if (! slugEdited) slugTarget.value = slugSource.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    });
}

document.querySelectorAll('[data-image-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (! file) return;
        let preview = input.parentElement.querySelector('.image-preview');
        if (! preview) {
            preview = document.createElement('img');
            preview.className = 'image-preview mb-2';
            preview.alt = 'Selected image preview';
            input.before(preview);
        }
        preview.src = URL.createObjectURL(file);
    });
});

const publicNavigation = document.querySelector('[data-public-nav]');
if (publicNavigation) {
    const updateNavigation = () => publicNavigation.classList.toggle('is-scrolled', window.scrollY > 12);
    updateNavigation();
    window.addEventListener('scroll', updateNavigation, { passive: true });
}

document.documentElement.classList.add('js-ready');
const revealItems = document.querySelectorAll('[data-reveal]');
if ('IntersectionObserver' in window && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (! entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    revealItems.forEach((item) => revealObserver.observe(item));
} else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
}

document.querySelectorAll('[data-counter]').forEach((counter) => {
    counter.style.fontVariantNumeric = 'tabular-nums';
});
