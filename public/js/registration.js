document.addEventListener('DOMContentLoaded', function() {
    const userTypeCards = document.querySelectorAll('.user-type-card');
    const userTypeInput = document.querySelector('#registration_form_userType');
    const journalistFields = document.querySelector('#journalist-fields');

    userTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            userTypeCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            userTypeInput.value = this.getAttribute('data-type');
            journalistFields.style.display = this.getAttribute('data-type') === 'journaliste' ? 'block' : 'none';
        });
    });

    // Validate password confirmation
    const passwordInput = document.querySelector('#registration_form_plainPassword');
    const confirmPasswordInput = document.querySelector('#confirmPassword');
    const submitButton = document.querySelector('.btn-primary');

    submitButton.addEventListener('click', function(e) {
        if (passwordInput.value !== confirmPasswordInput.value) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
        }
    });
});