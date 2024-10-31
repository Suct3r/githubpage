        // Redirección después de 5 segundos (5000 milisegundos)
        let countdown = 10;
        let countdownElement = document.getElementById('countdown');
        
        function updateCountdown() {
            countdown -= 1;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                window.location.href = "./"; // Cambia la URL de redirección
            } else {
                setTimeout(updateCountdown, 1000); // Actualiza cada 1 segundo
            }
        }

        setTimeout(updateCountdown, 1000);