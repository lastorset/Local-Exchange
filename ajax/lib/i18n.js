/**
 * Use Jed for JavaScript i18n.
 */

// TODO: Try jed-gettext-parser, so we can skip the JSON intermediate step
define(["jed", translations], function(Jed, messages) {
        if (!messages.hasOwnProperty('error')) {
            // No error reported, pass messages to Jed
            var i18n = new Jed(messages);

            return function(key) { return i18n.gettext(key); };
        }

        // There was an error, return strings as-is
        console.log("Local Exchange: Error loading translations for language %s (file %s)", messages.language, messages.file);

        return function(key) { return key };
    }
);
