/**
 * Use Jed for JavaScript i18n.
 */

// TODO: Use a get_i18n script to get the correct language file
// TODO: Try jed-gettext-parser, so we can skip the JSON intermediate step
define(["jed", "includes/lang/nb_NO/LC_MESSAGES/nb"], function(Jed, nb) {
        var i18n = new Jed(nb);

        return function(key) { return i18n.gettext(key); };
    }
);
