const logoutBtn = document.getElementById('logoutBtn');
const openModal = document.getElementById('openModal');

// Функции показа/скрытия кнопки
function showLogoutButton() {
    logoutBtn.classList.remove('hidden');
    logoutBtn.classList.add('visible');
    
    openModal.classList.remove('visible');
    openModal.classList.add('hidden');
    
}

function hideLogoutButton() {
    logoutBtn.classList.remove('visible');
    logoutBtn.classList.add('hidden');
    
    openModal.classList.remove('hidden');
    openModal.classList.add('visible');
}

// Проверка авторизации на сервере
async function checkAuth() {
    try {
        const res = await fetch('/api/check_auth.php', {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) {
            hideLogoutButton();
            return false;
        }

        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            hideLogoutButton();
            return false;
        }

        const data = await res.json();

        if (data.auth === true) {
            showLogoutButton();
            return true;
        } else {
            hideLogoutButton();
            return false;
        }

    } catch (err) {
        console.error(err);
        hideLogoutButton();
        return false;
    }
}

// Глобальная функция для использования после логина/регистрации
function updateAuthState() {
    checkAuth(); // покажет кнопку выхода, если авторизован
}

// Делаем функцию доступной глобально
window.updateAuthState = updateAuthState;

// Клик по кнопке выхода
logoutBtn.addEventListener('click', async () => {
    try {
        const res = await fetch('/api/logout.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' }
        });

        if (res.ok) {
            hideLogoutButton();
            alert('Вы вышли из аккаунта');
        } else {
            alert('Ошибка выхода');
        }
    } catch (err) {
        console.error(err);
        alert('Ошибка сети');
    }
});

// Проверка авторизации при загрузке страницы
document.addEventListener('DOMContentLoaded', checkAuth);