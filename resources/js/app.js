const BUTTON_SELECTOR = 'button, input[type="submit"]';

const isActionButton = (element) => {
    if (!element) {
        return false;
    }

    return element.tagName === 'BUTTON' || (element.tagName === 'INPUT' && element.type === 'submit');
};

const isLogoutAction = (button) => {
    if (!button) {
        return false;
    }

    const form = button.closest('form');
    const action = form?.action || '';
    const testId = button.getAttribute('data-test') || button.closest('[data-test]')?.getAttribute('data-test') || '';

    return action.includes('/logout') || testId.includes('logout');
};

const setButtonLoadingState = (button, isLoading) => {
    if (!button || !isActionButton(button) || isLogoutAction(button)) {
        return;
    }

    button.disabled = isLoading;
    button.setAttribute('aria-busy', isLoading ? 'true' : 'false');

    if (isLoading) {
        if (!button.dataset.originalText) {
            if (button.tagName === 'INPUT') {
                button.dataset.originalText = button.value;
            } else {
                button.dataset.originalText = button.innerHTML;
            }
        }

        button.classList.add('is-loading');

        if (button.tagName === 'INPUT') {
            button.value = 'Please wait…';
            return;
        }

        button.innerHTML = `
            <span class="tf-button-content">${button.dataset.originalText}</span>
            <span class="tf-button-spinner" aria-hidden="true">
                <span class="tf-inline-loader"></span>
            </span>
        `;

        return;
    }

    button.classList.remove('is-loading');

    if (button.tagName === 'INPUT') {
        if (button.dataset.originalText) {
            button.value = button.dataset.originalText;
        }

        return;
    }

    if (button.dataset.originalText) {
        button.innerHTML = button.dataset.originalText;
    }
};

const resetLoadingButtons = () => {
    document.querySelectorAll('.is-loading').forEach((button) => {
        setButtonLoadingState(button, false);
    });
};

document.addEventListener('click', (event) => {
    const button = event.target.closest(BUTTON_SELECTOR);

    if (!button || !isActionButton(button) || button.disabled || button.classList.contains('is-loading')) {
        return;
    }

    setButtonLoadingState(button, true);
}, true);

document.addEventListener('submit', (event) => {
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

    if (submitButton && !submitButton.disabled && !isLogoutAction(submitButton)) {
        setButtonLoadingState(submitButton, true);
    }
}, true);

window.addEventListener('beforeunload', resetLoadingButtons);
window.addEventListener('pagehide', resetLoadingButtons);

const bindLivewireLoadingHooks = () => {
    if (!window.Livewire) {
        return;
    }

    window.Livewire.hook('message.processed', () => {
        resetLoadingButtons();
    });

    window.Livewire.hook('message.failed', () => {
        resetLoadingButtons();
    });
};

if (window.Livewire) {
    bindLivewireLoadingHooks();
} else {
    document.addEventListener('livewire:initialized', bindLivewireLoadingHooks);
}
