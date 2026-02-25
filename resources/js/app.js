import './bootstrap';

// Ensure Livewire fetch requests explicitly include credentials (cookies)
// and send CSRF token as header — workaround for Chrome cookie issues
document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ options }) => {
        options.credentials = 'include';

        const token = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('[data-csrf]')?.getAttribute('data-csrf');

        if (token) {
            options.headers['X-CSRF-TOKEN'] = token;
        }
    });
});
