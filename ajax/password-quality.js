// Temporary stand-in for gettext
function gettext(msg) {
    return msg;
}

require(["lib/zxcvbn/zxcvbn-async"]);

/** When the document finishes loading, add a password meter next to the form field with the given name. */
function addPasswordMeter(fieldName) {
    window.addEventListener('load', function() {
        var passwordField = document.getElementsByName(fieldName)[0];
        var evaluation = document.createElement('span');
        passwordField.parentNode.appendChild(evaluation);

        function updateMeter() {
            // TODO: include user_inputs (other form fields)
            var result = zxcvbn(passwordField.value);

            var classNames = [
                'very_bad',
                'bad',
                'mediocre',
                'good',
                'very_good'
            ];

            // TODO: i18n
            switch (result.score) {
                case 0:
                    evaluation.innerHTML = gettext('Password is very bad');
                    break;

                case 1:
                    evaluation.innerHTML = gettext('Password is bad');
                    break;

                case 2:
                    evaluation.innerHTML = gettext('Password is mediocre');
                    break;

                case 3:
                    evaluation.innerHTML = gettext('Password is good');
                    break;

                case 4:
                    evaluation.innerHTML = gettext('Password is very good');
                    break;

            }

            passwordField.classList.add('password_evaluated');
            for (var i = 0; i <= 4; i++)
                if (result.score == i)
                    passwordField.classList.add('password_'+ classNames[i]);
                else
                    passwordField.classList.remove('password_'+ classNames[i]);
        }

        passwordField.addEventListener('input', updateMeter, false);
    }, false);
}