window.addEventListener('load', function() {
    var passwordField = document.getElementsByName('password')[0];
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
                evaluation.innerHTML = 'Password is very bad';
                break;

            case 1:
                evaluation.innerHTML = 'Password is bad';
                break;

            case 2:
                evaluation.innerHTML = 'Password is mediocre';
                break;

            case 3:
                evaluation.innerHTML = 'Password is good';
                break;

            case 4:
                evaluation.innerHTML = 'Password is very good';
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