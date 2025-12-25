const logoutBtn = document.getElementById('logoutBtn');
const openModal = document.getElementById('openModal');
const startGameRepair = document.getElementById('startGameRepair');

// Функции показа/скрытия кнопки
function showLogoutButton() {
    logoutBtn.classList.remove('hidden');
    logoutBtn.classList.add('visible');
    
    openModal.classList.remove('visible');
    openModal.classList.add('hidden');
    
    startGameRepair.classList.remove('hidden');
    startGameRepair.classList.add('visible');
    
        // Обновляем никнейм
    if (typeof window.updateUserState === 'function') {
        window.updateUserState();
    }
    
    
}

function hideLogoutButton() {
    logoutBtn.classList.remove('visible');
    logoutBtn.classList.add('hidden');
    
    openModal.classList.remove('hidden');
    openModal.classList.add('visible');
    
    startGameRepair.classList.remove('visible');
    startGameRepair.classList.add('hidden');
    
        // Показываем Гость
    if (typeof window.updateUserState === 'function') {
        window.updateUserState();
    }
    
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
        // 1️⃣ Получаем CSRF токен
        const csrfRes = await fetch('/api/csrf.php', {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });
        const { csrf } = await csrfRes.json();

        // 2️⃣ Отправляем POST logout с CSRF
        const res = await fetch('/api/logout.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csrf })
        });

        if (res.ok) {
            hideLogoutButton();
            window.updateUserState();
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