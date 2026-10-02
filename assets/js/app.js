document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.querySelector('[data-nav-toggle]');
    const mainNav = document.querySelector('[data-main-nav]');

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', () => {
            const opened = mainNav.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', String(opened));
            document.body.classList.toggle('no-scroll', opened);
        });
    }

    const userMenu = document.querySelector('[data-user-menu]');
    const userTrigger = document.querySelector('[data-user-trigger]');

    if (userMenu && userTrigger) {
        userTrigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const opened = userMenu.classList.toggle('open');
            userTrigger.setAttribute('aria-expanded', String(opened));
        });

        document.addEventListener('click', () => {
            userMenu.classList.remove('open');
            userTrigger.setAttribute('aria-expanded', 'false');
        });
    }

    document.querySelectorAll('[data-toast-close]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('[data-toast]')?.remove();
        });
    });

    window.setTimeout(() => {
        document.querySelectorAll('[data-toast]').forEach((toast) => toast.remove());
    }, 5000);

    document.querySelectorAll('[data-qty-minus], [data-qty-plus]').forEach((button) => {
        button.addEventListener('click', () => {
            const wrapper = button.closest('[data-quantity]');
            const input = wrapper?.querySelector('input[type="number"]');

            if (!input) {
                return;
            }

            const min = Number(input.min || 1);
            const max = Number(input.max || 99);
            const delta = button.hasAttribute('data-qty-plus') ? 1 : -1;
            input.value = String(Math.max(min, Math.min(max, Number(input.value || min) + delta)));
        });
    });

    const chatMessages = document.querySelector('[data-chat-messages]');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const chat = document.querySelector('[data-chat-messages][data-chat-conversation]');

    if (!chat) {
        return;
    }

    const conversationId = Number(chat.dataset.chatConversation || 0);
    const currentUserId = Number(chat.dataset.currentUser || 0);
    let lastId = Math.max(
        0,
        ...Array.from(chat.querySelectorAll('[data-message-id]')).map((item) => Number(item.dataset.messageId || 0))
    );

    const escapeHtml = (value) => {
        const element = document.createElement('div');
        element.textContent = value;
        return element.innerHTML;
    };

    const appendMessage = (message) => {
        if (chat.querySelector(`[data-message-id="${message.id}"]`)) {
            return;
        }

        const article = document.createElement('article');
        article.className = `message${Number(message.sender_id) === currentUserId ? ' mine' : ''}`;
        article.dataset.messageId = String(message.id);

        const date = new Date(String(message.created_at).replace(' ', 'T'));
        const dateLabel = Number.isNaN(date.getTime())
            ? ''
            : new Intl.DateTimeFormat('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            }).format(date).replace(',', '');

        article.innerHTML = `<p>${escapeHtml(message.body).replace(/\n/g, '<br>')}</p><time>${dateLabel}</time>`;
        chat.appendChild(article);
        chat.scrollTop = chat.scrollHeight;
    };

    const pollMessages = async () => {
        if (!conversationId || document.hidden) {
            return;
        }

        try {
            const response = await fetch(`actions/messages-json.php?conversation_id=${conversationId}&after=${lastId}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            if (!data.ok || !Array.isArray(data.messages)) {
                return;
            }

            data.messages.forEach((message) => {
                appendMessage(message);
                lastId = Math.max(lastId, Number(message.id || 0));
            });
        } catch (_) {
            // O envio tradicional continua funcionando como fallback.
        }
    };

    window.setInterval(pollMessages, 4000);
});


document.addEventListener('DOMContentLoaded', () => {
    const cepInput = document.querySelector('[data-cep-input]');
    const cityInput = document.querySelector('[data-cep-city]');
    const stateInput = document.querySelector('[data-cep-state]');
    const addressInput = document.querySelector('[data-cep-address]');
    const status = document.querySelector('[data-cep-status]');

    if (!cepInput || !cityInput || !stateInput) {
        return;
    }

    let lastCep = '';
    let requestController = null;

    const setStatus = (message, type = '') => {
        if (!status) {
            return;
        }
        status.textContent = message;
        status.classList.remove('is-loading', 'is-success', 'is-error');
        if (type) {
            status.classList.add(`is-${type}`);
        }
    };

    const formatCep = (value) => {
        const digits = value.replace(/\D/g, '').slice(0, 8);
        return digits.length > 5 ? `${digits.slice(0, 5)}-${digits.slice(5)}` : digits;
    };

    const lookupCep = async () => {
        const cep = cepInput.value.replace(/\D/g, '');

        if (cep.length !== 8 || cep === lastCep) {
            if (cep.length > 0 && cep.length < 8) {
                setStatus('O CEP deve ter 8 dígitos.', 'error');
            }
            return;
        }

        lastCep = cep;
        requestController?.abort();
        requestController = new AbortController();
        const timeout = window.setTimeout(() => requestController.abort(), 8000);
        setStatus('Consultando CEP…', 'loading');

        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
                headers: { Accept: 'application/json' },
                signal: requestController.signal,
            });

            if (!response.ok) {
                throw new Error('Falha na consulta');
            }

            const data = await response.json();
            if (data.erro) {
                cityInput.value = '';
                stateInput.value = '';
                setStatus('CEP não encontrado. Confira os números ou preencha o endereço manualmente.', 'error');
                return;
            }

            cityInput.value = data.localidade || '';
            stateInput.value = data.uf || '';

            if (addressInput && data.logradouro && addressInput.value.trim() === '') {
                addressInput.value = data.logradouro;
            }

            const location = [data.localidade, data.uf].filter(Boolean).join(' / ');
            setStatus(location ? `Localidade identificada: ${location}.` : 'CEP identificado.', 'success');
            addressInput?.focus();
        } catch (error) {
            if (error.name !== 'AbortError') {
                setStatus('Não foi possível consultar o CEP agora. Você pode preencher cidade e estado manualmente.', 'error');
            } else {
                setStatus('A consulta do CEP demorou demais. Você pode preencher os dados manualmente.', 'error');
            }
        } finally {
            window.clearTimeout(timeout);
        }
    };

    cepInput.addEventListener('input', () => {
        const formatted = formatCep(cepInput.value);
        if (cepInput.value !== formatted) {
            cepInput.value = formatted;
        }
        if (formatted.replace(/\D/g, '').length < 8) {
            lastCep = '';
        }
    });

    cepInput.addEventListener('blur', lookupCep);
    cepInput.addEventListener('keyup', () => {
        if (cepInput.value.replace(/\D/g, '').length === 8) {
            lookupCep();
        }
    });
});

/* Baby Food — visual interactions 2.0 */
document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('[data-header]');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const syncHeader = () => {
        if (!header) {
            return;
        }
        header.classList.toggle('is-scrolled', window.scrollY > 14);
    };

    syncHeader();
    window.addEventListener('scroll', syncHeader, { passive: true });

    if (reduceMotion || !('IntersectionObserver' in window)) {
        return;
    }

    document.documentElement.classList.add('js-reveal-ready');

    const selectors = [
        '.section-head',
        '.product-card',
        '.recipe-card',
        '.video-card',
        '.article-card',
        '.professional-card',
        '.feature-panel',
        '.step-item',
        '.page-hero-grid',
        '.filter-bar',
        '.panel',
        '.stat-card',
        '.child-card',
        '.cart-item',
        '.summary-card',
        '.form-card'
    ];

    const items = Array.from(document.querySelectorAll(selectors.join(',')));

    items.forEach((item, index) => {
        item.classList.add('reveal-on-scroll');
        item.style.setProperty('--reveal-delay', `${Math.min(index % 4, 3) * 70}ms`);
    });

    const observer = new IntersectionObserver((entries, currentObserver) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            currentObserver.unobserve(entry.target);
        });
    }, {
        threshold: 0.08,
        rootMargin: '0px 0px -36px 0px'
    });

    items.forEach((item) => observer.observe(item));
});
