<div id="inactivity-overlay" class="inactivity-overlay" style="display: none;">
    <div class="inactivity-popup">
        <h2>Are you still here?</h2>
        <p>You have been inactive for a while.</p>
        <p>You will be returned to the home screen in <span id="countdown-seconds">30</span> seconds.</p>

        <div class="inactivity-popup-buttons">
            <button id="stay-active-btn" type="button">Stay</button>
        </div>
    </div>
</div>

<script>
    (() => {
        const INACTIVITY_TIME = 45000;
        const POPUP_COUNTDOWN = 30;

        let inactivityTimer = null;
        let countdownTimer = null;
        let countdown = POPUP_COUNTDOWN;
        let popupVisible = false;

        const overlay = document.getElementById('inactivity-overlay');
        const countdownElement = document.getElementById('countdown-seconds');
        const stayButton = document.getElementById('stay-active-btn');

        function showPopup() {
            popupVisible = true;
            countdown = POPUP_COUNTDOWN;
            countdownElement.textContent = countdown;
            overlay.style.display = 'flex';

            countdownTimer = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(countdownTimer);
                    timeoutAndReset();
                }
            }, 1000);
        }

        function hidePopup() {
            popupVisible = false;
            overlay.style.display = 'none';

            if (countdownTimer) {
                clearInterval(countdownTimer);
                countdownTimer = null;
            }
        }

        function resetInactivityTimer() {
            if (inactivityTimer) {
                clearTimeout(inactivityTimer);
            }

            if (!popupVisible) {
                inactivityTimer = setTimeout(showPopup, INACTIVITY_TIME);
            }
        }

        function stayActive() {
            hidePopup();
            resetInactivityTimer();
        }

        function timeoutAndReset() {
            window.location.href = 'clear-cart.php';
        }

        const activityEvents = [
            'click',
            'touchstart',
            'mousemove',
            'keydown',
            'scroll'
        ];

        activityEvents.forEach((eventName) => {
            document.addEventListener(eventName, () => {
                if (!popupVisible) {
                    resetInactivityTimer();
                }
            }, true);
        });

        stayButton.addEventListener('click', stayActive);

        resetInactivityTimer();
    })();
</script>