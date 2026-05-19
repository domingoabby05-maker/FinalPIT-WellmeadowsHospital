import './bootstrap';

const showScreen = () => {
    const section = (location.hash || '#dashboard').replace('#', '');
    document.querySelectorAll('.screen').forEach((screen) => {
        screen.classList.toggle('active', screen.id === `${section}-screen`);
    });
    document.querySelectorAll('[data-nav]').forEach((link) => {
        link.classList.toggle('active', link.dataset.nav === section);
    });
};

if (location.hash && location.hash !== '#signin') {
    document.body.classList.add('app-ready');
}

showScreen();
window.addEventListener('hashchange', showScreen);

document.querySelectorAll('[data-modal]').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById(button.dataset.modal)?.showModal();
    });
});

document.querySelectorAll('[data-close]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
});

document.querySelectorAll('[data-search]').forEach((input) => {
    input.addEventListener('input', () => {
        const needle = input.value.trim().toLowerCase();
        const table = document.getElementById(input.dataset.search);

        table?.querySelectorAll('tbody tr').forEach((row) => {
            row.hidden = !row.textContent.toLowerCase().includes(needle);
        });
    });
});
