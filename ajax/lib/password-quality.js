define(["lib/zxcvbn/zxcvbn", "ajax/lib/i18n"], function(zxcvbn, i18n) {
    /** When the document finishes loading, add a password meter next to the form field with the given name. */
    return {
        addPasswordMeter: function (fieldName) {
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

                switch (result.score) {
                    case 0:
                        evaluation.innerHTML = i18n.gettext('Password is very bad');
                        break;

                    case 1:
                        evaluation.innerHTML = i18n.gettext('Password is bad');
                        break;

                    case 2:
                        evaluation.innerHTML = i18n.gettext('Password is mediocre');
                        break;

                    case 3:
                        evaluation.innerHTML = i18n.gettext('Password is good');
                        break;

                    case 4:
                        evaluation.innerHTML = i18n.gettext('Password is very good');
                        break;

                    default:
                        // Something went wrong
                        evaluation.innerHTML = '';
                }

                passwordField.classList.add('password_evaluated');
                for (var i = 0; i <= 4; i++)
                    if (result.score == i)
                        passwordField.classList.add('password_' + classNames[i]);
                    else
                        passwordField.classList.remove('password_' + classNames[i]);
            }

            passwordField.addEventListener('input', updateMeter, false);
        }
    };
});