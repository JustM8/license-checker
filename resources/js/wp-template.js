(function () {
    const domain = location.hostname;
    const secret = '{{secret}}';

    const href = `https://smarto.agency/?utm_source=referral&utm_medium=${encodeURIComponent(domain)}`;

    function getLogoHtml() {
        return `<a class="smarto" target="_blank" href="${href}">
                    <img src="https://ubani.ge/wp-content/themes/lisigreen/assets/images/smarto-logo.svg" style="height:20px;">
                </a>`;
    }

    function insertLogo() {
        if (!document.querySelector('.smarto')) {
            const footer = document.getElementById('site-footer') || document.querySelector('footer');
            if (footer) {
                const div = document.createElement('div');
                div.innerHTML = getLogoHtml();
                footer.appendChild(div.firstChild);
            }
        }
    }

    function isVisible(el) {
        if (!el) return false;
        const style = window.getComputedStyle(el);
        return (
            style.display !== 'none' &&
            style.visibility !== 'hidden' &&
            style.opacity !== '0' &&
            el.offsetParent !== null
        );
    }

    function checkLogo() {
        const logo = document.querySelector('.smarto');
        if (!logo || !isVisible(logo)) {
            insertLogo();

            const payload = domain + secret;
            crypto.subtle.digest('SHA-256', new TextEncoder().encode(payload)).then(buffer => {
                const hashArray = Array.from(new Uint8Array(buffer));
                const key = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                fetch('https://license-check.local/api/branding-removed', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({domain, key})
                });
            });
        }
    }

    insertLogo();
    setInterval(checkLogo, 15000);
})();
