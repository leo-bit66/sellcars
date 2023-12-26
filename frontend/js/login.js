document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const modal = document.getElementById('error-modal');
    const modalMessage = document.getElementById('modal-message');
    const closeButton = document.querySelector('.close');

    loginForm.addEventListener('submit', handleLoginFormSubmit);

    if (closeButton) {
        closeButton.style.display = 'block';
        closeButton.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    async function handleLoginFormSubmit(event) {
        event.preventDefault();
        const formData = new FormData(loginForm);

        try {
            const response = await fetch('/sellcars/login', {
                method: 'POST',
                body: JSON.stringify(Object.fromEntries(formData)),
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            if (response.ok && data.status === 'success') {
                handleSuccessfulLogin(data.token, data.redirect);
            } else {
                handleFailedLogin(data.message);
            }
        } catch (error) {
            handleFetchError(error);
        }
    }

    async function handleSuccessfulLogin(token, redirectURL) {
        // Store the JWT in local storage
        localStorage.setItem('token', token);

        const baseUrl = window.location.origin;
        const customersPageURL = baseUrl + redirectURL;

        // Redirect based on the provided information
        window.location.href = customersPageURL;
    }

    function handleFailedLogin(errorMessage) {
        modalMessage.textContent = `Login Failed: ${errorMessage}`;
        modal.style.display = 'block';
    }

    function handleFetchError(error) {
        console.error('An error occurred during the fetch operation:', error);
    }
});
