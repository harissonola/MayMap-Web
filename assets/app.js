import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import { Turbo } from '@hotwired/turbo-rails';
Turbo.session.drive = false;

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');


// === Configuration des messages flash ===
const flashConfig = {
    'success': {
        icon: 'success',
        title: 'Succès',
        background: '#d1e7dd',
        iconColor: '#0f5132',
        textColor: '#0c4128',
        confirmButtonColor: '#0f5132'
    },
    'error': {
        icon: 'error',
        title: 'Erreur',
        background: '#f8d7da',
        iconColor: '#842029',
        textColor: '#6a1a21',
        confirmButtonColor: '#842029'
    },
    'danger': 'error',
    'warning': {
        icon: 'warning',
        title: 'Attention',
        background: '#fff3cd',
        iconColor: '#664d03',
        textColor: '#523e02',
        confirmButtonColor: '#664d03'
    },
    'info': {
        icon: 'info',
        title: 'Information',
        background: '#cfe2ff',
        iconColor: '#084298',
        textColor: '#06357a',
        confirmButtonColor: '#084298'
    }
};

// === Fonction pour afficher les messages flash ===
function showFlashMessages() {
    const flashMessages = document.querySelectorAll('.flash-message');

    flashMessages.forEach(message => {
        let type = message.dataset.type || 'info';
        type = type === 'danger' ? 'error' : type;

        const config = typeof flashConfig[type] === 'string'
            ? flashConfig[flashConfig[type]]
            : flashConfig[type];

        Swal.fire({
            title: config.title,
            html: message.textContent.trim(),
            icon: config.icon,
            background: config.background,
            color: config.textColor,
            iconColor: config.iconColor,
            confirmButtonColor: config.confirmButtonColor,
            showConfirmButton: true,
            confirmButtonText: 'Fermer',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'flash-popup',
                title: 'flash-title',
                htmlContainer: 'flash-text',
                confirmButton: 'flash-confirm-btn'
            }
        }).then(() => {
            message.remove();
        });
    });
}

// === Initialisation ===
document.addEventListener('turbo:load', function() {
    showFlashMessages();
});