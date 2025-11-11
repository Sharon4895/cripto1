// js/login.js
// Intercepta el envío del formulario de login y hace una petición AJAX a procesar_login.php

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('login-form');
    if (!form) return; // no hay formulario en esta página

    const errorDiv = document.getElementById('login-error');

    form.addEventListener('submit', function (e) {
        // Interceptar envío normal
        e.preventDefault();

        if (errorDiv) {
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';
        }

        const formData = new FormData(form);
        // Marcar que es petición AJAX
        formData.append('ajax', '1');

        // Opcional: deshabilitar el botón para evitar envíos dobles
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) submitButton.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            // Si el servidor responde JSON, parsearlo
            const ct = response.headers.get('content-type') || '';
            if (ct.indexOf('application/json') !== -1) {
                return response.json();
            }
            // Si no es JSON, tratar como texto y mostrarlo como error
            return response.text().then(text => ({ success: false, message: text }));
        })
        .then(data => {
            if (data && data.success) {
                // Login correcto -> redirigir al principal
                window.location.href = 'principal.php';
            } else {
                // Mostrar mensaje de error en la página y añadir enlace de recuperar contraseña
                const msg = (data && data.message) ? data.message : 'Error en la autenticación';
                if (errorDiv) {
                    // Limpiar contenido previo
                    errorDiv.innerHTML = '';
                    const span = document.createElement('span');
                    span.textContent = msg + ' ';
                    errorDiv.appendChild(span);

                    const recoverLink = document.createElement('a');
                    recoverLink.href = 'recuperar_contrasena.html';
                    recoverLink.textContent = 'Recuperar contraseña';
                    recoverLink.style.color = '#0056b3';
                    recoverLink.style.fontWeight = 'bold';
                    recoverLink.style.marginLeft = '6px';
                    errorDiv.appendChild(recoverLink);

                    errorDiv.style.display = 'block';
                } else {
                    alert(msg + ' — Recuperar contraseña: recuperar_contrasena.html');
                }
                if (submitButton) submitButton.disabled = false;
            }
        })
        .catch(err => {
            console.error('Error en la petición de login:', err);
            const msg = 'No se ha podido conectar con el servidor. Inténtalo de nuevo.';
            if (errorDiv) {
                errorDiv.textContent = msg;
                errorDiv.style.display = 'block';
            } else {
                alert(msg);
            }
            if (submitButton) submitButton.disabled = false;
        });
    });
});
