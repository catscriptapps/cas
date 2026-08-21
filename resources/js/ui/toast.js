// /resources/js/ui/toast.js

/**
 * Simple toast utility (auto-removes after 3s)
 */
export function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `
    fixed bottom-4 left-1/2 transform -translate-x-1/2
    px-4 py-2 rounded shadow-lg
    text-white text-sm font-medium
    ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}
  `;
  // Modals in this app run at z-index up to 2147483648 (see modal-factory.js),
  // so a toast fired while one is open (e.g. copying a token code from inside
  // a modal) needs to sit above that or it renders invisibly behind it.
  toast.style.zIndex = '2147483649';
  toast.textContent = message;

  document.body.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('opacity-0');
    setTimeout(() => toast.remove(), 500);
  }, 3000);
}
